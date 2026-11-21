<?php

namespace App\Controllers;

use App\Models\Reservas;
use App\Services\MicroserviceClient;
use DateTime;
use Exception;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ReservationsController
{
    // URL de los otros microservicios (cambia los puertos si usas otros)
    private const CLIENTES_URL  = 'http://127.0.0.1:8081';
    private const VEHICULOS_URL = 'http://127.0.0.1:8061';

    private MicroserviceClient $clientes;
    private MicroserviceClient $vehiculos;

    function __construct()
    {
        $this->clientes  = new MicroserviceClient(self::CLIENTES_URL, 'clientes');
        $this->vehiculos = new MicroserviceClient(self::VEHICULOS_URL, 'vehículos');
    }

    // ==================================================================
    // CRUD
    // ==================================================================

    function all(): Collection
    {
        return Reservas::all();
    }

    function find_reservation_by_id(int $id): Reservas|null
    {
        return Reservas::find($id);
    }
 
    function create(array $data): Reservas
    {
        $this->validate($data);
        $vehiculo = $this->check_references($data['cliente_id'], $data['vehiculo_id']);
        if (($vehiculo['estado'] ?? '') === 'mantenimiento') {
            throw new Exception('El vehículo está en mantenimiento', 409);
        }

        // Transacción: si algo falla (incluida la llamada a vehiculos_ms) se deshace la reserva.
        // lockForUpdate evita que dos peticiones simultáneas reserven el mismo carro.
        return Capsule::connection()->transaction(function () use ($data) {
            $cruce = $this->overlap_query($data['vehiculo_id'], $data['fecha_inicio'], $data['fecha_fin'])
                ->lockForUpdate()->first();
            if ($cruce !== null) {
                throw new Exception('El vehículo ya está reservado en ese periodo', 409);
            }

            $reserva = new Reservas();
            $reserva->cliente_id = $data['cliente_id'];
            $reserva->vehiculo_id = $data['vehiculo_id'];
            $reserva->fecha_inicio = $data['fecha_inicio'];
            $reserva->fecha_fin = $data['fecha_fin'];
            $reserva->estado = 'activa';
            $reserva->save();

            $this->set_vehicle_state($reserva->vehiculo_id, 'alquilado');
            return $reserva->fresh(); // fresh() trae también created_at / updated_at
        });
    }

    function update(int $id, array $data): Reservas
    {
        $reserva = Reservas::find($id);
        if (empty($reserva)) {
            throw new Exception("La reserva $id no existe", 404);
        }
        $this->validate($data);
        $this->check_references($data['cliente_id'], $data['vehiculo_id']);

        $estado = $data['estado'] ?? $reserva->estado;
        $estabaActiva = $reserva->estado === 'activa';
        $vehiculoAnterior = $reserva->vehiculo_id;

        return Capsule::connection()->transaction(function () use ($reserva, $data, $estado, $estabaActiva, $vehiculoAnterior) {
            if ($estado === 'activa') {
                $cruce = $this->overlap_query($data['vehiculo_id'], $data['fecha_inicio'], $data['fecha_fin'], $reserva->id)
                    ->lockForUpdate()->first();
                if ($cruce !== null) {
                    throw new Exception('El vehículo ya está reservado en ese periodo', 409);
                }
            }

            $reserva->cliente_id = $data['cliente_id'];
            $reserva->vehiculo_id = $data['vehiculo_id'];
            $reserva->fecha_inicio = $data['fecha_inicio'];
            $reserva->fecha_fin = $data['fecha_fin'];
            $reserva->estado = $estado;
            $reserva->save();

            // Mantener el estado de los vehículos coherente con la reserva
            if ($estado === 'activa') {
                $this->set_vehicle_state($reserva->vehiculo_id, 'alquilado');
            }
            if ($estabaActiva && ($estado !== 'activa' || $vehiculoAnterior != $reserva->vehiculo_id)) {
                $this->release_vehicle($vehiculoAnterior);
            }
            return $reserva->fresh();
        });
    }

    function delete(int $id): Reservas
    {
        $reserva = Reservas::find($id);
        if (empty($reserva)) {
            throw new Exception("La reserva $id no existe", 404);
        }
        return Capsule::connection()->transaction(function () use ($reserva) {
            $reserva->delete();
            if ($reserva->estado === 'activa') {
                $this->release_vehicle($reserva->vehiculo_id);
            }
            return $reserva;
        });
    }

 
    // devolución de un vehículo
   
    function return_vehicle(int $id): Reservas
    {
        $reserva = Reservas::find($id);
        if (empty($reserva)) {
            throw new Exception("La reserva $id no existe", 404);
        }
        if ($reserva->estado !== 'activa') {
            throw new Exception("La reserva ya está {$reserva->estado}", 409);
        }

        return Capsule::connection()->transaction(function () use ($reserva) {
            $reserva->estado = 'completada';
            $reserva->save();
            // Si vehiculos_ms falla, se lanza excepción y la reserva NO queda completada
            $this->release_vehicle($reserva->vehiculo_id);
            return $reserva->fresh();
        });
    }

   
    // historial por cliente o por vehículo
    

    function history_by_client(int $clienteId): array
    {
        $rows = Reservas::where('cliente_id', $clienteId)->orderBy('fecha_inicio', 'desc')->get();
        return $this->enrich($rows);
    }

    function history_by_vehicle(int $vehiculoId): array
    {
        $rows = Reservas::where('vehiculo_id', $vehiculoId)->orderBy('fecha_inicio', 'desc')->get();
        return $this->enrich($rows);
    }

  
    // vehículos disponibles en una fecha o periodo


    function available_vehicles(?string $desde, ?string $hasta): array
    {
        if (empty($desde)) {
            throw new Exception('desde es obligatorio (formato YYYY-MM-DD)', 422);
        }
        $hasta = $hasta ?: $desde; // si solo mandan "desde", se consulta ese día
        $this->check_date($desde, 'desde');
        $this->check_date($hasta, 'hasta');
        if ($hasta < $desde) {
            throw new Exception('hasta no puede ser anterior a desde', 422);
        }

        // Vehículos con una reserva activa que se cruza con el periodo (mi base de datos)
        $ocupados = array_map('intval', Reservas::where('estado', 'activa')
            ->where('fecha_inicio', '<=', $hasta)
            ->where('fecha_fin', '>=', $desde)
            ->pluck('vehiculo_id')->all());

        // Catálogo completo (vehiculos_ms)
        $todos = $this->vehiculos->get('vehiculos') ?? [];

        $disponibles = array_values(array_filter($todos, function ($v) use ($ocupados) {
            return !in_array((int) $v['id'], $ocupados, true)
                && ($v['estado'] ?? '') !== 'mantenimiento';
        }));

        return ['desde' => $desde, 'hasta' => $hasta, 'vehiculos' => $disponibles];
    }

   
    // Auxiliares 

    /* Reservas activas del vehículo que se cruzan con [inicio, fin]. */
    private function overlap_query(int $vehiculoId, string $inicio, string $fin, ?int $excluirId = null): Builder
    {
        $q = Reservas::where('vehiculo_id', $vehiculoId)
            ->where('estado', 'activa')
            ->where('fecha_inicio', '<=', $fin)
            ->where('fecha_fin', '>=', $inicio);
        if ($excluirId !== null) {
            $q->where('id', '!=', $excluirId);
        }
        return $q;
    }

    /** Reemplaza a la llave foránea: confirma en los otros microservicios que existan. Devuelve el vehículo. */
    private function check_references(int $clienteId, int $vehiculoId): array
    {
        if ($this->clientes->get("clientes/$clienteId") === null) {
            throw new Exception("El cliente $clienteId no existe", 422);
        }
        $vehiculo = $this->vehiculos->get("vehiculos/$vehiculoId");
        if ($vehiculo === null) {
            throw new Exception("El vehículo $vehiculoId no existe", 422);
        }
        return $vehiculo;
    }

    /*Cambia el estado de un vehículo en vehiculos_ms (usa su GET y su PUT, que ya existen).
     */
    private function set_vehicle_state(int $id, string $estado, ?string $soloSi = null): bool
    {
        $v = $this->vehiculos->get("vehiculos/$id");
        if ($v === null) {
            return false;
        }
        $actual = $v['estado'] ?? '';
        if ($actual === $estado || ($soloSi !== null && $actual !== $soloSi)) {
            return true;
        }
        // El PUT de vehiculos_ms exige todos los campos
        $this->vehiculos->put("vehiculos/$id", [
            'marca'     => $v['marca'] ?? null,
            'modelo'    => $v['modelo'] ?? null,
            'anio'      => $v['anio'] ?? null,
            'categoria' => $v['categoria'] ?? null,
            'estado'    => $estado,
        ]);
        return true;
    }

    /** Deja el vehículo 'disponible' solo si ya no tiene otra reserva activa (y no está en mantenimiento). */
    private function release_vehicle(int $vehiculoId): void
    {
        $otraActiva = Reservas::where('vehiculo_id', $vehiculoId)->where('estado', 'activa')->exists();
        if (!$otraActiva) {
            $this->set_vehicle_state($vehiculoId, 'disponible', 'alquilado');
        }
    }

    private function check_date(string $value, string $campo): void
    {
        $d = DateTime::createFromFormat('Y-m-d', $value);
        if (!$d || $d->format('Y-m-d') !== $value) {
            throw new Exception("$campo debe tener formato YYYY-MM-DD", 422);
        }
    }

    private function validate(array $data): void
    {
        foreach (['cliente_id', 'vehiculo_id', 'fecha_inicio', 'fecha_fin'] as $campo) {
            if (empty($data[$campo])) {
                throw new Exception("$campo es obligatorio", 422);
            }
        }
        $this->check_date($data['fecha_inicio'], 'fecha_inicio');
        $this->check_date($data['fecha_fin'], 'fecha_fin');

        if ($data['fecha_fin'] < $data['fecha_inicio']) {
            throw new Exception('fecha_fin no puede ser anterior a fecha_inicio', 422);
        }

        $estados = ['activa', 'completada', 'cancelada'];
        if (isset($data['estado']) && !in_array($data['estado'], $estados, true)) {
            throw new Exception('estado debe ser: ' . implode(', ', $estados), 422);
        }
    }

    /** Agrega los datos del cliente y del vehículo a cada reserva. Si un servicio falla, deja null. */
    private function enrich(Collection $reservas): array
    {
        $cache = [];
        $buscar = function (MicroserviceClient $ms, string $path) use (&$cache) {
            if (!array_key_exists($path, $cache)) {
                try {
                    $cache[$path] = $ms->get($path);
                } catch (Exception $e) {
                    $cache[$path] = null;
                }
            }
            return $cache[$path];
        };

        $resultado = [];
        foreach ($reservas as $r) {
            $item = $r->toArray();
            $item['cliente']  = $buscar($this->clientes, "clientes/{$r->cliente_id}");
            $item['vehiculo'] = $buscar($this->vehiculos, "vehiculos/{$r->vehiculo_id}");
            $resultado[] = $item;
        }
        return $resultado;
    }
}

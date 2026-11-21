<?php

namespace App\Controllers;

use App\Models\Reservas;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class ReservationsController
{
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

        $reserva = new Reservas();
        $reserva->cliente_id = $data['cliente_id'];
        $reserva->vehiculo_id = $data['vehiculo_id'];
        $reserva->fecha_inicio = $data['fecha_inicio'];
        $reserva->fecha_fin = $data['fecha_fin'];
        $reserva->estado = $data['estado'] ?? 'activa';
        $reserva->save();
        return $reserva->fresh(); // fresh() trae también created_at / updated_at
    }

    function update(int $id, array $data): Reservas|null
    {
        $reserva = Reservas::find($id);
        if (empty($reserva)) {
            throw new Exception("La reserva $id no existe", 404);
        }
        $this->validate($data);

        $reserva->cliente_id = $data['cliente_id'];
        $reserva->vehiculo_id = $data['vehiculo_id'];
        $reserva->fecha_inicio = $data['fecha_inicio'];
        $reserva->fecha_fin = $data['fecha_fin'];
        $reserva->estado = $data['estado'] ?? $reserva->estado;
        $reserva->save();
        return $reserva->fresh();
    }

    function delete(int $id): Reservas|null
    {
        $reserva = Reservas::find($id);
        if (empty($reserva)) {
            throw new Exception("La reserva $id no existe", 404);
        }
        $reserva->delete();
        return $reserva;
    }

    private function validate(array $data): void
    {
        foreach (['cliente_id', 'vehiculo_id', 'fecha_inicio', 'fecha_fin'] as $campo) {
            if (empty($data[$campo])) {
                throw new Exception("$campo es obligatorio", 422);
            }
        }

        foreach (['fecha_inicio', 'fecha_fin'] as $campo) {
            $d = DateTime::createFromFormat('Y-m-d', $data[$campo]);
            if (!$d || $d->format('Y-m-d') !== $data[$campo]) {
                throw new Exception("$campo debe tener formato YYYY-MM-DD", 422);
            }
        }

        if ($data['fecha_fin'] < $data['fecha_inicio']) {
            throw new Exception('fecha_fin no puede ser anterior a fecha_inicio', 422);
        }

        $estados = ['activa', 'completada', 'cancelada'];
        if (isset($data['estado']) && !in_array($data['estado'], $estados, true)) {
            throw new Exception('estado debe ser: ' . implode(', ', $estados), 422);
        }
    }
}

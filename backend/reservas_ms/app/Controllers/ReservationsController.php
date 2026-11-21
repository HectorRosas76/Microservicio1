<?php

namespace App\Controllers;

use App\Models\Reservas;
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
        $vehicle = new Reservas();
        $vehicle->marca = $data['marca'];
        $vehicle->modelo = $data['modelo'];
        $vehicle->anio = $data['anio'];
        $vehicle->categoria = $data['categoria'];
        $vehicle->estado = $data['estado'];
        $vehicle->save();
        return $vehicle;
    }

    function update(int $id, array $data): Reservas|null
    {
        $vehicle = Reservas::find($id);
        if (empty($vehicle)) {
            throw new Exception("$id no existe", 1);
        }
        $vehicle->marca = $data['marca'];
        $vehicle->modelo = $data['modelo'];
        $vehicle->anio = $data['anio'];
        $vehicle->categoria = $data['categoria'];
        $vehicle->estado = $data['estado'];
        $vehicle->save();
        return $vehicle;
    }

    function delete(int $id): Reservas|null
    {
        $vehicle = Reservas::find($id);
        if (empty($vehicle)) {
            throw new Exception("$id no existe", 1);
        }
        $vehicle->delete();
        return $vehicle;
    }
}

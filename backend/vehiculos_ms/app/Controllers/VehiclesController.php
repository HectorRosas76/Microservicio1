<?php

namespace App\Controllers;

use App\Models\Vehiculos;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class VehiclesController
{
    function all(): Collection
    {
        return Vehiculos::all();
    }

    function find_vehicle_by_id(int $id): Vehiculos|null
    {
        return Vehiculos::find($id);
    }

    function create(array $data): Vehiculos
    {
        $vehicle = new Vehiculos();
        $vehicle->marca = $data['marca'];
        $vehicle->modelo = $data['modelo'];
        $vehicle->anio = $data['anio'];
        $vehicle->categoria = $data['categoria'];
        $vehicle->estado = $data['estado'];
        $vehicle->save();
        return $vehicle;
    }

    function update(int $id, array $data): Vehiculos|null
    {
        $vehicle = Vehiculos::find($id);
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

    function delete(int $id): Vehiculos|null
    {
        $vehicle = Vehiculos::find($id);
        if (empty($vehicle)) {
            throw new Exception("$id no existe", 1);
        }
        $vehicle->delete();
        return $vehicle;
    }
}

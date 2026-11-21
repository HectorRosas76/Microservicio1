<?php

namespace App\Controllers;

use App\Models\Clientes;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class ClientsController
{
    function all(): Collection
    {
        return Clientes::all();
    }

    function find_client_by_id(int $id): Clientes|null
    {
        return Clientes::find($id);
    }

    function create(array $data): Clientes
    {
        $client = new Clientes();
        $client->nombre = $data['nombre'];
        $client->telefono = $data['telefono'];
        $client->correo = $data['correo'];
        $client->numero_licencia = $data['numero_licencia'];
        $client->save();
        return $client;
    }

    function update(int $id, array $data): Clientes|null
    {
        $client = Clientes::find($id);
        if (empty($client)) {
            throw new Exception("$id no existe", 1);
        }
        $client->nombre = $data['nombre'];
        $client->correo = $data['correo'];
        $client->numero_licencia = $data['numero_licencia'];
        $client->save();
        return $client;
    }

    function delete(int $id): Clientes|null
    {
        $client = Clientes::find($id);
        if (empty($client)) {
            throw new Exception("$id no existe", 1);
        }
        $client->delete();
        return $client;
    }
}

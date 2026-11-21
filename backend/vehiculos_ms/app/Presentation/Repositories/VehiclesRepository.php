<?php

namespace App\Presentation\Repositories;

use App\Controllers\VehiclesController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class VehiclesRepository
{

    function get_all_vehicles(Request $req, Response $resp, $args): Response
    {
        $controller = new VehiclesController();
        $rows = $controller->all();
        if (count($rows) == 0) {
            return $resp->withStatus(204);
        } else {
            $resp->getBody()->write($rows->toJson());
            return $resp
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    function get_vehicle(Request $req, Response $resp, $args): Response
    {
        $id = $args["id"];
        $controller = new VehiclesController();
        $row = $controller->find_vehicle_by_id($id);
        if (empty($row)) {
            return $resp->withStatus(404);
        } else {
            $resp->getBody()->write($row->toJson());
            return $resp
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    function create_vehicle(Request $req, Response $resp, $args): Response
    {
        $controller = new VehiclesController();
        $body = $req->getBody()->getContents();
        $data = json_decode($body, true);
        $row = $controller->create($data);
        if (empty($row)) {
            return $resp->withStatus(400);
        } else {
            $resp->getBody()->write($row->toJson());
            return $resp
                ->withStatus(201)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    function update_vehicle(Request $req, Response $resp, $args): Response
    {
        $id = $args["id"];
        $body = $req->getBody()->getContents();
        $data = json_decode($body, true);
        $controller = new VehiclesController();
        $row = $controller->update($id, $data);
        if (empty($row)) {
            return $resp->withStatus(400);
        } else {
            $resp->getBody()->write($row->toJson());
            return $resp
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    function delete_vehicle(Request $req, Response $resp, $args): Response
    {
        $id = $args["id"];
        $controller = new VehiclesController();
        $row = $controller->delete($id);
        if (empty($row)) {
            return $resp->withStatus(400);
        } else {
            return $resp->withStatus(200);
        }
    }

        function update_vehicle_state(Request $req, Response $resp, $args): Response
    {
        $data = json_decode($req->getBody()->getContents(), true) ?? [];
        $controller = new VehiclesController();
        try {
            $row = $controller->set_state((int) $args["id"], $data['estado'] ?? '');
        } catch (\Exception $e) {
            $resp->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $resp->withStatus(422)->withHeader('Content-Type', 'application/json');
        }
        if (empty($row)) {
            return $resp->withStatus(404);
        }
        $resp->getBody()->write($row->toJson());
        return $resp->withStatus(200)->withHeader('Content-Type', 'application/json');
    }
}

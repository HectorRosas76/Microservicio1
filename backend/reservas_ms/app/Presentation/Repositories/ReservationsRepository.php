<?php

namespace App\Presentation\Repositories;

use App\Controllers\ReservationsController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReservationsRepository
{

    function get_all_reservations(Request $req, Response $resp, $args): Response
    {
        $controller = new ReservationsController();
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

    function get_reservation(Request $req, Response $resp, $args): Response
    {
        $id = $args["id"];
        $controller = new ReservationsController();
        $row = $controller->find_reservation_by_id($id);
        if (empty($row)) {
            return $resp->withStatus(404);
        } else {
            $resp->getBody()->write($row->toJson());
            return $resp
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    function create_reservation(Request $req, Response $resp, $args): Response
    {
        $controller = new ReservationsController();
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

    function update_reservation(Request $req, Response $resp, $args): Response
    {
        $id = $args["id"];
        $body = $req->getBody()->getContents();
        $data = json_decode($body, true);
        $controller = new ReservationsController();
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

    function delete_reservation(Request $req, Response $resp, $args): Response
    {
        $id = $args["id"];
        $controller = new ReservationsController();
        $row = $controller->delete($id);
        if (empty($row)) {
            return $resp->withStatus(400);
        } else {
            return $resp->withStatus(200);
        }
    }
}

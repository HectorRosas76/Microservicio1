<?php

namespace App\Presentation\Repositories;

use App\Controllers\ReservationsController;
use Exception;
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
        if (!is_array($data)) {
            return $this->error($resp, 'El cuerpo debe ser un JSON válido', 400);
        }
        try {
            $row = $controller->create($data);
        } catch (Exception $e) {
            return $this->error($resp, $e->getMessage(), $e->getCode());
        }
        $resp->getBody()->write($row->toJson());
        return $resp
            ->withStatus(201)
            ->withHeader('Content-Type', 'application/json');
    }

    function update_reservation(Request $req, Response $resp, $args): Response
    {
        $id = $args["id"];
        $body = $req->getBody()->getContents();
        $data = json_decode($body, true);
        if (!is_array($data)) {
            return $this->error($resp, 'El cuerpo debe ser un JSON válido', 400);
        }
        $controller = new ReservationsController();
        try {
            $row = $controller->update($id, $data);
        } catch (Exception $e) {
            return $this->error($resp, $e->getMessage(), $e->getCode());
        }
        $resp->getBody()->write($row->toJson());
        return $resp
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json');
    }

    function delete_reservation(Request $req, Response $resp, $args): Response
    {
        $id = $args["id"];
        $controller = new ReservationsController();
        try {
            $controller->delete($id);
        } catch (Exception $e) {
            return $this->error($resp, $e->getMessage(), $e->getCode());
        }
        return $resp->withStatus(200);
    }

    // Respuesta de error en JSON. Si el código no es un 4xx válido, usa 400.
    private function error(Response $resp, string $message, int $code): Response
    {
        $status = ($code >= 400 && $code < 500) ? $code : 400;
        $resp->getBody()->write(json_encode(['error' => $message], JSON_UNESCAPED_UNICODE));
        return $resp
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }
}

<?php

namespace App\Presentation\Repositories;

use App\Controllers\ReservationsController;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReservationsRepository
{

    // ---------------------------------------------------------------
    // CRUD
    // ---------------------------------------------------------------

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
        $data = json_decode($req->getBody()->getContents(), true);
        if (!is_array($data)) {
            return $this->error($resp, 'El cuerpo debe ser un JSON válido', 400);
        }
        try {
            $row = (new ReservationsController())->create($data);
        } catch (Exception $e) {
            return $this->error($resp, $e->getMessage(), $e->getCode());
        }
        return $this->json($resp, $row->toJson(), 201);
    }

    function update_reservation(Request $req, Response $resp, $args): Response
    {
        $data = json_decode($req->getBody()->getContents(), true);
        if (!is_array($data)) {
            return $this->error($resp, 'El cuerpo debe ser un JSON válido', 400);
        }
        try {
            $row = (new ReservationsController())->update($args["id"], $data);
        } catch (Exception $e) {
            return $this->error($resp, $e->getMessage(), $e->getCode());
        }
        return $this->json($resp, $row->toJson(), 200);
    }

    function delete_reservation(Request $req, Response $resp, $args): Response
    {
        try {
            (new ReservationsController())->delete($args["id"]);
        } catch (Exception $e) {
            return $this->error($resp, $e->getMessage(), $e->getCode());
        }
        return $resp->withStatus(200);
    }

    // ---------------------------------------------------------------
    // Los 4 métodos
    // ---------------------------------------------------------------

    // PUT /reservas/{id}/devolucion
    function return_vehicle(Request $req, Response $resp, $args): Response
    {
        try {
            $row = (new ReservationsController())->return_vehicle($args["id"]);
        } catch (Exception $e) {
            return $this->error($resp, $e->getMessage(), $e->getCode());
        }
        return $this->json($resp, $row->toJson(), 200);
    }

    // GET /reservas/cliente/{id}
    function get_client_history(Request $req, Response $resp, $args): Response
    {
        $rows = (new ReservationsController())->history_by_client($args["id"]);
        return $this->json($resp, json_encode($rows, JSON_UNESCAPED_UNICODE), 200);
    }

    // GET /reservas/vehiculo/{id}
    function get_vehicle_history(Request $req, Response $resp, $args): Response
    {
        $rows = (new ReservationsController())->history_by_vehicle($args["id"]);
        return $this->json($resp, json_encode($rows, JSON_UNESCAPED_UNICODE), 200);
    }

    // GET /reservas/disponibles?desde=2026-12-01&hasta=2026-12-05
    function get_available_vehicles(Request $req, Response $resp, $args): Response
    {
        $q = $req->getQueryParams();
        try {
            $result = (new ReservationsController())->available_vehicles($q['desde'] ?? null, $q['hasta'] ?? null);
        } catch (Exception $e) {
            return $this->error($resp, $e->getMessage(), $e->getCode());
        }
        return $this->json($resp, json_encode($result, JSON_UNESCAPED_UNICODE), 200);
    }

    // ---------------------------------------------------------------
    // Auxiliares
    // ---------------------------------------------------------------

    private function json(Response $resp, string $json, int $status): Response
    {
        $resp->getBody()->write($json);
        return $resp
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }

    // Respuesta de error en JSON. Si el código no es un error HTTP válido (400-599), usa 400.
    private function error(Response $resp, string $message, $code): Response
    {
        // (los errores de base de datos traen un código tipo texto, por eso $code no lleva tipo)
        $status = (is_int($code) && $code >= 400 && $code < 600) ? $code : 400;
        return $this->json($resp, json_encode(['error' => $message], JSON_UNESCAPED_UNICODE), $status);
    }
}

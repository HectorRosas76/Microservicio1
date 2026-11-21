<?php

use App\Presentation\Repositories\ReservationsRepository;
// use Psr\Http\Message\ResponseInterface as Response;
// use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {

    $app->group('/reservas', function (RouteCollectorProxy $group) {
        $group->get('', [ReservationsRepository::class, 'get_all_reservations']);
        $group->get('/{id}', [ReservationsRepository::class, 'get_reservation']);
        $group->post('', [ReservationsRepository::class, 'create_reservation']);
        $group->put('/{id}', [ReservationsRepository::class, 'update_reservation']);
        $group->delete('/{id}', [ReservationsRepository::class, 'delete_reservation']);
    });
};

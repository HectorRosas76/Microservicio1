<?php

use App\Presentation\Repositories\ReservationsRepository;

use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {

    $app->group('/reservas', function (RouteCollectorProxy $group) {

        $group->get('/disponibles', [ReservationsRepository::class, 'get_available_vehicles']);
        $group->get('/cliente/{id}', [ReservationsRepository::class, 'get_client_history']);
        $group->get('/vehiculo/{id}', [ReservationsRepository::class, 'get_vehicle_history']);

        $group->get('', [ReservationsRepository::class, 'get_all_reservations']);
        $group->get('/{id}', [ReservationsRepository::class, 'get_reservation']);
        $group->post('', [ReservationsRepository::class, 'create_reservation']);
        $group->put('/{id}', [ReservationsRepository::class, 'update_reservation']);
        $group->put('/{id}/devolucion', [ReservationsRepository::class, 'return_vehicle']);
        $group->delete('/{id}', [ReservationsRepository::class, 'delete_reservation']);
    });
};

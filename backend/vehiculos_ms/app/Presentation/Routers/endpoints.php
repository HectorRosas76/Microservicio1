<?php

use App\Presentation\Repositories\VehiclesRepository;

use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {

    $app->group('/vehiculos', function (RouteCollectorProxy $group) {
        $group->get('', [VehiclesRepository::class, 'get_all_vehicles']);
        $group->get('/{id}', [VehiclesRepository::class, 'get_vehicle']);
        $group->post('', [VehiclesRepository::class, 'create_vehicle']);
        $group->put('/{id}', [VehiclesRepository::class, 'update_vehicle']);
        $group->delete('/{id}', [VehiclesRepository::class, 'delete_vehicle']);
    });
};

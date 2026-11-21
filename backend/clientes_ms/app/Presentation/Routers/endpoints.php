<?php

use App\Presentation\Repositories\ClientsRepository;
// use Psr\Http\Message\ResponseInterface as Response;
// use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    // $app->get('/clientes', function (Request $request, Response $response, $args) {
    //     $repository = new ClientsRepository();
    //     return $repository->get_all_clients($request, $response, $args);
    // });

    // $app->get('/clientes', [ClientsRepository::class, 'get_all_clients']);
    // $app->get('/estudiantes/{id}', [StudentsRepository::class, 'get_student']);
    // $app->post('/estudiantes', [StudentsRepository::class, 'create_student']);
    // $app->put('/estudiantes/{id}', [StudentsRepository::class, 'update_student']);
    // $app->delete('/estudiantes/{id}', [StudentsRepository::class, 'delete_student']);

    $app->group('/clientes', function (RouteCollectorProxy $group) {
        $group->get('', [ClientsRepository::class, 'get_all_clients']);
        $group->get('/{id}', [ClientsRepository::class, 'get_client']);
        $group->post('', [ClientsRepository::class, 'create_client']);
        $group->put('/{id}', [ClientsRepository::class, 'update_client']);
        $group->delete('/{id}', [ClientsRepository::class, 'delete_client']);
    });
};

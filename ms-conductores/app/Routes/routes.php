<?php

use Slim\App;
use App\Controllers\ConductorController;

return function (App $app) {
    
    $app->get('/', function ($request, $response) {
        $response->getBody()->write(json_encode([
            'estado' => 'ok',
            'servicio' => 'conductores'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $controlador = new ConductorController();
    
    $app->get('/conductores', [$controlador, 'listar']);
    $app->post('/conductores', [$controlador, 'crear']);
    $app->put('/conductores/{id}', [$controlador, 'actualizar']);
    $app->patch('/conductores/{id}/estado', [$controlador, 'estado']);
    
};
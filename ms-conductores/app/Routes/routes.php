<?php

use Slim\App;
use App\Controllers\ConductorController;

return function (App $app) {
    
    
    $app->get('/', function ($request, $response) {
        $data = ['message' => 'Microservicio ms-conductores funcionando correctamente'];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    });

    
    $conductorController = new ConductorController();
    
    $app->get('/conductores', [$conductorController, 'index']);
    $app->post('/conductores', [$conductorController, 'store']);
    $app->put('/conductores/{id}', [$conductorController, 'update']);
    $app->patch('/conductores/{id}/estado', [$conductorController, 'cambiarEstado']);
    
};
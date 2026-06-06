<?php

use Slim\App;
use App\Controllers\VehiculoController;

return function (App $app) {
 
    $app->get('/', function ($request, $response) {
        $data = ['message' => 'Microservicio ms-vehiculos funcionando correctamente'];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $vehiculoController = new VehiculoController();
    
    $app->get('/vehiculos', [$vehiculoController, 'index']);
    $app->post('/vehiculos', [$vehiculoController, 'store']);
    $app->put('/vehiculos/{id}', [$vehiculoController, 'update']);
    $app->patch('/vehiculos/{id}/estado', [$vehiculoController, 'cambiarEstado']);
    
};
<?php

use Slim\App;
use App\Controllers\ViajeController;

return function (App $app) {
    
    
    $app->get('/', function ($request, $response) {
        $data = ['message' => 'Microservicio ms-viajes funcionando correctamente'];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $viajeController = new ViajeController();
    
  
    $app->get('/seguimientos', [$viajeController, 'index']);
    $app->post('/seguimientos', [$viajeController, 'store']);
    $app->get('/seguimientos/historial/{programacion_id}', [$viajeController, 'historial']);
    
};
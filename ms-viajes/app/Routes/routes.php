<?php

use Slim\App;
use App\Controllers\ViajeController;

return function (App $app) {
    
    $app->get('/', function ($request, $response) {
        $response->getBody()->write(json_encode([
            'estado' => 'ok',
            'servicio' => 'viajes'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $controlador = new ViajeController();
    
    $app->get('/seguimientos', [$controlador, 'listar']);
    $app->post('/seguimientos', [$controlador, 'crear']);
    $app->get('/seguimientos/historial/{programacion_id}', [$controlador, 'historial']);
    
};
<?php

use Slim\App;
use App\Controllers\RutaController;

return function (App $app) {
    
    $app->get('/', function ($request, $response) {
        $response->getBody()->write(json_encode([
            'estado' => 'ok',
            'servicio' => 'rutas'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $controlador = new RutaController();

    $app->get('/rutas', [$controlador, 'listarRutas']);
    $app->post('/rutas', [$controlador, 'crearRuta']);
    $app->put('/rutas/{id}', [$controlador, 'actualizarRuta']);

    $app->get('/programaciones', [$controlador, 'listarProgramaciones']);
    $app->post('/programaciones', [$controlador, 'programarViaje']);
    $app->put('/programaciones/{id}', [$controlador, 'reprogramarViaje']);
    $app->patch('/programaciones/{id}/estado', [$controlador, 'cambiarEstadoProgramacion']);
    $app->patch('/programaciones/{id}/finalizar', [$controlador, 'finalizarViaje']);
    
};
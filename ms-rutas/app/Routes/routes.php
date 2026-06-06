<?php

use Slim\App;
use App\Controllers\RutaController;

return function (App $app) {
    
  
    $app->get('/', function ($request, $response) {
        $data = ['message' => 'Microservicio ms-rutas funcionando correctamente'];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $rutaController = new RutaController();

    $app->get('/rutas', [$rutaController, 'listarRutas']);
    $app->post('/rutas', [$rutaController, 'crearRuta']);
    $app->put('/rutas/{id}', [$rutaController, 'actualizarRuta']);
    
    // Programaciones de viajes
    $app->get('/programaciones', [$rutaController, 'listarProgramaciones']);
    $app->post('/programaciones', [$rutaController, 'programarViaje']);
    $app->put('/programaciones/{id}', [$rutaController, 'reprogramarViaje']);
    $app->patch('/programaciones/{id}/estado', [$rutaController, 'cambiarEstadoProgramacion']);
    $app->patch('/programaciones/{id}/finalizar', [$rutaController, 'finalizarViaje']);
    
};
<?php

use Slim\App;
use App\Controllers\AuthController;

return function (App $app) {
    
    $app->get('/', function ($request, $response) {
        $response->getBody()->write(json_encode([
            'estado' => 'ok',
            'servicio' => 'autenticacion'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $controlador = new AuthController();
    
    $app->post('/login', [$controlador, 'login']);
    $app->post('/logout', [$controlador, 'logout']);
    $app->get('/validar', [$controlador, 'validar']);
    
};
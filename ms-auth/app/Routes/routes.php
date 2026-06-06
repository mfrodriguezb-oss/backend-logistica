<?php

use Slim\App;
use App\Controllers\AuthController;

return function (App $app) {

    $app->get('/', function ($request, $response) {
        $data = ['message' => 'Microservicio ms-auth funcionando correctamente'];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    });

    
    $authController = new AuthController();
    
    $app->post('/login', [$authController, 'login']);
    $app->post('/logout', [$authController, 'logout']);
    $app->get('/validar', [$authController, 'validar']);
    
};
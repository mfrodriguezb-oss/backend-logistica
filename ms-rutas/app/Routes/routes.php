<?php

use Slim\App;

return function (App $app) {
    
    $app->get('/', function ($request, $response) {
        $data = ['message' => 'Microservicio ms-rutas funcionando correctamente'];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    });
    
};
<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Configuracion/basedatos.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();

$app->get('/', function ($request, $response) {
    $data = [
        'message' => 'Microservicio ms-conductores funcionando correctamente'
    ];

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
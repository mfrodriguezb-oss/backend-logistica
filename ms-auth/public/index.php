<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Config/database.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$app->get('/', function ($request, $response) {
    $data = [
        'message' => 'Microservicio ms-auth funcionando correctamente'
    ];

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
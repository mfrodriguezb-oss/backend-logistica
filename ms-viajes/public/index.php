<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Config/database.php';

use Slim\Factory\AppFactory;
use Illuminate\Database\Capsule\Manager as DB;

$app = AppFactory::create();

$app->get('/', function ($request, $response) {
    $seguimientos = DB::table('seguimientos_viajes')->get();

    $data = [
        'message' => 'Conexión exitosa a db_ms_viajes',
        'seguimientos_viajes' => $seguimientos
    ];

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
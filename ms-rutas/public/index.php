<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Config/database.php';

use Slim\Factory\AppFactory;
use Illuminate\Database\Capsule\Manager as DB;

$app = AppFactory::create();

$app->get('/', function ($request, $response) {
    $rutas = DB::table('rutas')->get();
    $programaciones = DB::table('programaciones_viajes')->get();

    $data = [
        'message' => 'Conexión exitosa a db_ms_rutas',
        'rutas' => $rutas,
        'programaciones_viajes' => $programaciones
    ];

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
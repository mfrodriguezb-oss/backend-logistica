<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Config/database.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as SlimResponse;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$app->setBasePath('/backend-logistica/ms-conductores/public/index.php');


$app->add(function (Request $request, $handler) {
    $cabeceras = $request->getHeaders();
    $token = $cabeceras['HTTP_TOKEN'][0] ?? '';

    if (empty($token)) {
        $respuesta = new SlimResponse();
        $respuesta->getBody()->write(json_encode([
            'exito' => false,
            'mensaje' => 'Token no proporcionado. Acceso denegado.',
            'codigo' => 401
        ]));
        return $respuesta->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    $usuario = \Illuminate\Database\Capsule\Manager::table('usuarios')
        ->where('token', $token)
        ->where('sesion_activa', 1)
        ->where('estado', 'activo')
        ->first();

    if (!$usuario) {
        $respuesta = new SlimResponse();
        $respuesta->getBody()->write(json_encode([
            'exito' => false,
            'mensaje' => 'Token invalido o sesion inactiva. Acceso denegado.',
            'codigo' => 401
        ]));
        return $respuesta->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    return $handler->handle($request);
});

(require __DIR__ . '/../app/Routes/routes.php')($app);

$app->run();
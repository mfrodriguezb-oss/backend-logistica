<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Config/database.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as SlimResponse;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$app->setBasePath('/backend-logistica/ms-vehiculos/public/index.php');


$app->add(function (Request $request, $handler) {
    $headers = $request->getHeaders();
    $token = $headers['HTTP_TOKEN'][0] ?? '';

    if (empty($token)) {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'Token no proporcionado. Acceso denegado.'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    $usuario = \Illuminate\Database\Capsule\Manager::table('usuarios')
        ->where('token', $token)
        ->where('sesion_activa', 1)
        ->where('estado', 'activo')
        ->first();

    if (!$usuario) {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'Token invalido o sesion inactiva. Acceso denegado.'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    return $handler->handle($request);
});

(require __DIR__ . '/../app/Routes/routes.php')($app);

$app->run();
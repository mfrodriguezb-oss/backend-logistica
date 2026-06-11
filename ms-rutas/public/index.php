<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Config/database.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$app->setBasePath('');

// CORS preflight
$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, TOKEN, Authorization')
        ->withStatus(200);
});

$app->add(function (Request $request, $handler) {
    if ($request->getMethod() === 'OPTIONS') {
        return $handler->handle($request);
    }

    $uri = $request->getUri()->getPath();
    if ($uri === '/' || $uri === '') {
        return $handler->handle($request);
    }

    $cabeceras = $request->getHeaders();
    $token = '';

    if (isset($cabeceras['HTTP_TOKEN'][0])) {
        $token = $cabeceras['HTTP_TOKEN'][0];
    } elseif (isset($cabeceras['TOKEN'][0])) {
        $token = $cabeceras['TOKEN'][0];
    }

    if (empty($token)) {
        $tokenHeader = $request->getHeader('TOKEN');
        if (!empty($tokenHeader)) {
            $token = $tokenHeader[0];
        }
    }

    if (empty($token)) {
        $respuesta = new SlimResponse();
        $respuesta->getBody()->write(json_encode([
            'exito' => false,
            'mensaje' => 'Token no proporcionado. Acceso denegado.',
            'codigo' => 401
        ]));
        return $respuesta
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withStatus(401);
    }

    return $handler->handle($request);
});

$app->add(function (Request $request, $handler) {
    $response = $handler->handle($request);

    $contentType = $response->getHeaderLine('Content-Type');
    if (empty($contentType)) {
        $contentType = 'application/json; charset=utf-8';
    } elseif (strpos($contentType, 'charset') === false && strpos($contentType, 'application/json') !== false) {
        $contentType .= '; charset=utf-8';
    }

    return $response
        ->withHeader('Content-Type', $contentType)
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, TOKEN, Authorization');
});

(require __DIR__ . '/../app/Routes/routes.php')($app);

$app->run();
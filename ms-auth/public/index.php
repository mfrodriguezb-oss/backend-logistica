<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Config/database.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

$app = AppFactory::create();

$app->addBodyParsingMiddleware();

// CORS preflight - maneja peticiones OPTIONS
$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, TOKEN, Authorization')
        ->withStatus(200);
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

$app->setBasePath('');

(require __DIR__ . '/../app/Routes/routes.php')($app);

$app->run();
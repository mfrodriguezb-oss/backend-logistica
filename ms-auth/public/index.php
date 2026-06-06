<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Config/database.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

// ESTO VA ANTES de las rutas
$app->setBasePath('/backend-logistica/ms-auth/public');

// Cargar rutas desde archivo
(require __DIR__ . '/../app/Routes/routes.php')($app);

$app->run();
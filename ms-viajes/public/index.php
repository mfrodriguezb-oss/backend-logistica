<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Config/database.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$app->setBasePath('/backend-logistica/ms-viajes/public');

(require __DIR__ . '/../app/Routes/routes.php')($app);

$app->run();
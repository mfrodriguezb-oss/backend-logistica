    <?php

    use Slim\App;
    use App\Controllers\VehiculoController;

    return function (App $app) {
        
        $app->get('/', function ($request, $response) {
            $response->getBody()->write(json_encode([
                'estado' => 'ok',
                'servicio' => 'vehiculos'
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        });

        $controlador = new VehiculoController();
        
        $app->get('/vehiculos', [$controlador, 'listar']);
        $app->post('/vehiculos', [$controlador, 'crear']);
        $app->put('/vehiculos/{id}', [$controlador, 'actualizar']);
        $app->patch('/vehiculos/{id}/estado', [$controlador, 'estado']);
        
    };
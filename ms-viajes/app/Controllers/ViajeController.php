<?php

namespace App\Controllers;

use App\Models\SeguimientoViaje;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ViajeController
{
 
    public function listar(Request $request, Response $response)
    {
        $parametros = $request->getQueryParams();
        $consulta = SeguimientoViaje::query();

        if (!empty($parametros['programacion_id'])) {
            $consulta->where('programacion_viaje_id', $parametros['programacion_id']);
        }

        if (!empty($parametros['estado'])) {
            $consulta->where('estado', $parametros['estado']);
        }

        if (!empty($parametros['fecha'])) {
            $consulta->where('fecha', $parametros['fecha']);
        }

        $resultados = $consulta->get();

        $response->getBody()->write(json_encode([
            'exito' => true,
            'total' => count($resultados),
            'lista' => $resultados
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }


    public function crear(Request $request, Response $response)
    {
        $entrada = $request->getParsedBody();

        if (empty($entrada['programacion_viaje_id']) || empty($entrada['fecha']) || 
            empty($entrada['hora']) || empty($entrada['estado'])) {
            return $this->respuestaError($response, 'Todos los campos obligatorios deben completarse', 400);
        }

        $estadosValidos = ['programado', 'en_transito', 'retrasado', 'finalizado', 'cancelado'];
        if (!in_array($entrada['estado'], $estadosValidos)) {
            return $this->respuestaError($response, 'Estado no reconocido. Valores: programado, en_transito, retrasado, finalizado, cancelado', 400);
        }

        $nuevo = SeguimientoViaje::create($entrada);

        $response->getBody()->write(json_encode([
            'exito' => true,
            'mensaje' => 'Seguimiento registrado correctamente',
            'codigo' => 201,
            'registro' => $nuevo
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function historial(Request $request, Response $response, $args)
    {
        $programacionId = $args['programacion_id'];

        $resultados = SeguimientoViaje::where('programacion_viaje_id', $programacionId)
            ->orderBy('fecha', 'asc')
            ->orderBy('hora', 'asc')
            ->get();

        $response->getBody()->write(json_encode([
            'exito' => true,
            'total' => count($resultados),
            'lista' => $resultados
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

  
    private function respuestaError(Response $response, $texto, $codigo)
    {
        $response->getBody()->write(json_encode([
            'exito' => false,
            'mensaje' => $texto,
            'codigo' => $codigo
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($codigo);
    }
}
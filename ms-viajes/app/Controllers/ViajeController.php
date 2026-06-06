<?php

namespace App\Controllers;

use App\Models\SeguimientoViaje;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ViajeController
{
   
    public function index(Request $request, Response $response)
    {
        $params = $request->getQueryParams();
        $query = SeguimientoViaje::query();

        if (!empty($params['programacion_id'])) {
            $query->where('programacion_viaje_id', $params['programacion_id']);
        }

        if (!empty($params['estado'])) {
            $query->where('estado', $params['estado']);
        }

        if (!empty($params['fecha'])) {
            $query->where('fecha', $params['fecha']);
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $query->get()
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

   
    public function store(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        if (empty($data['programacion_viaje_id']) || empty($data['fecha']) || empty($data['hora']) || empty($data['estado'])) {
            return $this->error($response, 'Faltan campos obligatorios', 400);
        }

        $estadosPermitidos = ['programado', 'en_transito', 'retrasado', 'finalizado', 'cancelado'];
        if (!in_array($data['estado'], $estadosPermitidos)) {
            return $this->error($response, 'Estado no valido', 400);
        }

        $seguimiento = SeguimientoViaje::create($data);
        return $this->exito($response, 'Seguimiento registrado exitosamente', $seguimiento, 201);
    }

   
    public function historial(Request $request, Response $response, $args)
    {
        $programacionId = $args['programacion_id'];

        $seguimientos = SeguimientoViaje::where('programacion_viaje_id', $programacionId)
            ->orderBy('fecha', 'asc')
            ->orderBy('hora', 'asc')
            ->get();

        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $seguimientos
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

   
    private function error(Response $response, $mensaje, $codigo)
    {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => $mensaje
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($codigo);
    }

    private function exito(Response $response, $mensaje, $data = null, $codigo = 200)
    {
        $respuesta = [
            'success' => true,
            'message' => $mensaje
        ];
        if ($data) $respuesta['data'] = $data;

        $response->getBody()->write(json_encode($respuesta));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($codigo);
    }
}
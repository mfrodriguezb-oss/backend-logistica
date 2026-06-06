<?php

namespace App\Controllers;

use App\Models\Conductor;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ConductorController
{
    // Listar todos (con filtros opcionales)
    public function index(Request $request, Response $response)
    {
        $params = $request->getQueryParams();
        $query = Conductor::query();

        if (!empty($params['documento'])) {
            $query->where('documento', 'like', '%' . $params['documento'] . '%');
        }

        if (!empty($params['licencia'])) {
            $query->where('numero_licencia', 'like', '%' . $params['licencia'] . '%');
        }

        if (!empty($params['estado'])) {
            $query->where('estado', $params['estado']);
        }

        $conductores = $query->get();

        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $conductores
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Crear conductor
    public function store(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        // Validaciones
        if (empty($data['nombres']) || empty($data['apellidos']) || empty($data['documento']) || empty($data['numero_licencia'])) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Faltan campos obligatorios'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Validar documento duplicado
        $existeDocumento = Conductor::where('documento', $data['documento'])->first();
        if ($existeDocumento) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'El documento ya esta registrado'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Validar licencia duplicada
        $existeLicencia = Conductor::where('numero_licencia', $data['numero_licencia'])->first();
        if ($existeLicencia) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'El numero de licencia ya esta registrado'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Validar fecha de vencimiento
        if (empty($data['fecha_vencimiento_licencia']) || $data['fecha_vencimiento_licencia'] < date('Y-m-d')) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'La fecha de vencimiento de licencia no es valida'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $conductor = Conductor::create($data);

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Conductor creado exitosamente',
            'data' => $conductor
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    // Actualizar conductor
    public function update(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $data = $request->getParsedBody();

        $conductor = Conductor::find($id);

        if (!$conductor) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Conductor no encontrado'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // Validar documento duplicado (si cambia)
        if (!empty($data['documento']) && $data['documento'] !== $conductor->documento) {
            $existe = Conductor::where('documento', $data['documento'])->first();
            if ($existe) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'El documento ya esta registrado en otro conductor'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        // Validar licencia duplicada (si cambia)
        if (!empty($data['numero_licencia']) && $data['numero_licencia'] !== $conductor->numero_licencia) {
            $existe = Conductor::where('numero_licencia', $data['numero_licencia'])->first();
            if ($existe) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'El numero de licencia ya esta registrado en otro conductor'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        $conductor->update($data);

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Conductor actualizado exitosamente',
            'data' => $conductor
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Cambiar estado
    public function cambiarEstado(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $data = $request->getParsedBody();

        $conductor = Conductor::find($id);

        if (!$conductor) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Conductor no encontrado'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $estadosPermitidos = ['disponible', 'en_ruta', 'inactivo'];

        if (empty($data['estado']) || !in_array($data['estado'], $estadosPermitidos)) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Estado no valido. Use: disponible, en_ruta, inactivo'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $conductor->estado = $data['estado'];
        $conductor->save();

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Estado actualizado',
            'data' => $conductor
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
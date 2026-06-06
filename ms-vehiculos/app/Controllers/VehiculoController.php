<?php

namespace App\Controllers;

use App\Models\Vehiculo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class VehiculoController
{
   
    public function index(Request $request, Response $response)
    {
        $params = $request->getQueryParams();
        $query = Vehiculo::query();

        if (!empty($params['placa'])) {
            $query->where('placa', 'like', '%' . $params['placa'] . '%');
        }

        if (!empty($params['estado'])) {
            $query->where('estado', $params['estado']);
        }

        if (!empty($params['tipo'])) {
            $query->where('tipo_vehiculo', 'like', '%' . $params['tipo'] . '%');
        }

        $vehiculos = $query->get();

        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $vehiculos
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

  
    public function store(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        if (empty($data['placa']) || empty($data['tipo_vehiculo']) || empty($data['capacidad_carga']) || empty($data['modelo']) || empty($data['marca'])) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Faltan campos obligatorios'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        
        if ($data['capacidad_carga'] <= 0) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'La capacidad de carga debe ser mayor a 0'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

      
        $existePlaca = Vehiculo::where('placa', $data['placa'])->first();
        if ($existePlaca) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'La placa ya esta registrada'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $vehiculo = Vehiculo::create($data);

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Vehiculo creado exitosamente',
            'data' => $vehiculo
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

 
    public function update(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $data = $request->getParsedBody();

        $vehiculo = Vehiculo::find($id);

        if (!$vehiculo) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Vehiculo no encontrado'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

   
        if (!empty($data['capacidad_carga']) && $data['capacidad_carga'] <= 0) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'La capacidad de carga debe ser mayor a 0'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (!empty($data['placa']) && $data['placa'] !== $vehiculo->placa) {
            $existe = Vehiculo::where('placa', $data['placa'])->first();
            if ($existe) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'La placa ya esta registrada en otro vehiculo'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        $vehiculo->update($data);

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Vehiculo actualizado exitosamente',
            'data' => $vehiculo
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }


    public function cambiarEstado(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $data = $request->getParsedBody();

        $vehiculo = Vehiculo::find($id);

        if (!$vehiculo) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Vehiculo no encontrado'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $estadosPermitidos = ['disponible', 'en_ruta', 'mantenimiento', 'inactivo'];

        if (empty($data['estado']) || !in_array($data['estado'], $estadosPermitidos)) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Estado no valido. Use: disponible, en_ruta, mantenimiento, inactivo'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $vehiculo->estado = $data['estado'];
        $vehiculo->save();

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Estado actualizado',
            'data' => $vehiculo
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
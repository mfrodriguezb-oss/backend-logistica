<?php

namespace App\Controllers;

use App\Models\Vehiculo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class VehiculoController
{
   
    public function listar(Request $request, Response $response)
    {
        $parametros = $request->getQueryParams();
        $consulta = Vehiculo::query();

        if (!empty($parametros['placa'])) {
            $consulta->where('placa', 'like', '%' . $parametros['placa'] . '%');
        }

        if (!empty($parametros['estado'])) {
            $consulta->where('estado', $parametros['estado']);
        }

        if (!empty($parametros['tipo'])) {
            $consulta->where('tipo_vehiculo', 'like', '%' . $parametros['tipo'] . '%');
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

        if (empty($entrada['placa']) || empty($entrada['tipo_vehiculo']) || empty($entrada['capacidad_carga']) || 
            empty($entrada['modelo']) || empty($entrada['marca'])) {
            return $this->respuestaError($response, 'Todos los campos obligatorios deben completarse', 400);
        }

        if ($entrada['capacidad_carga'] <= 0) {
            return $this->respuestaError($response, 'La capacidad debe ser mayor a cero', 400);
        }

        $existePlaca = Vehiculo::where('placa', $entrada['placa'])->first();
        if ($existePlaca) {
            return $this->respuestaError($response, 'Esta placa ya esta registrada', 400);
        }

        $nuevo = Vehiculo::create($entrada);

        $response->getBody()->write(json_encode([
            'exito' => true,
            'mensaje' => 'Vehiculo registrado correctamente',
            'codigo' => 201,
            'registro' => $nuevo
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

 
    public function actualizar(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $entrada = $request->getParsedBody();

        $vehiculo = Vehiculo::find($id);

        if (!$vehiculo) {
            return $this->respuestaError($response, 'Vehiculo no encontrado en el sistema', 404);
        }

        if (!empty($entrada['capacidad_carga']) && $entrada['capacidad_carga'] <= 0) {
            return $this->respuestaError($response, 'La capacidad debe ser mayor a cero', 400);
        }

        if (!empty($entrada['placa']) && $entrada['placa'] !== $vehiculo->placa) {
            $existe = Vehiculo::where('placa', $entrada['placa'])->first();
            if ($existe) {
                return $this->respuestaError($response, 'Esta placa pertenece a otro vehiculo', 400);
            }
        }

        $vehiculo->update($entrada);

        $response->getBody()->write(json_encode([
            'exito' => true,
            'mensaje' => 'Informacion actualizada',
            'codigo' => 200,
            'registro' => $vehiculo
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

  
    public function estado(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $entrada = $request->getParsedBody();

        $vehiculo = Vehiculo::find($id);

        if (!$vehiculo) {
            return $this->respuestaError($response, 'Vehiculo no encontrado en el sistema', 404);
        }

        $estadosValidos = ['disponible', 'en_ruta', 'mantenimiento', 'inactivo'];

        if (empty($entrada['estado']) || !in_array($entrada['estado'], $estadosValidos)) {
            return $this->respuestaError($response, 'Estado no reconocido. Valores: disponible, en_ruta, mantenimiento, inactivo', 400);
        }

        $vehiculo->estado = $entrada['estado'];
        $vehiculo->save();

        $response->getBody()->write(json_encode([
            'exito' => true,
            'mensaje' => 'Estado modificado',
            'codigo' => 200,
            'registro' => $vehiculo
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
<?php

namespace App\Controllers;

use App\Models\Conductor;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ConductorController
{

    public function listar(Request $request, Response $response)
    {
        $parametros = $request->getQueryParams();
        $consulta = Conductor::query();

        if (!empty($parametros['documento'])) {
            $consulta->where('documento', 'like', '%' . $parametros['documento'] . '%');
        }

        if (!empty($parametros['licencia'])) {
            $consulta->where('numero_licencia', 'like', '%' . $parametros['licencia'] . '%');
        }

        if (!empty($parametros['estado'])) {
            $consulta->where('estado', $parametros['estado']);
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


        if (empty($entrada['nombres']) || empty($entrada['apellidos']) || empty($entrada['documento']) || empty($entrada['numero_licencia'])) {
            return $this->respuestaError($response, 'Todos los campos obligatorios deben completarse', 400);
        }


        $existeDoc = Conductor::where('documento', $entrada['documento'])->first();
        if ($existeDoc) {
            return $this->respuestaError($response, 'Ya existe un conductor con este documento', 400);
        }
        $existeLic = Conductor::where('numero_licencia', $entrada['numero_licencia'])->first();
        if ($existeLic) {
            return $this->respuestaError($response, 'Ya existe un conductor con esta licencia', 400);
        }


        if (empty($entrada['fecha_vencimiento_licencia']) || $entrada['fecha_vencimiento_licencia'] < date('Y-m-d')) {
            return $this->respuestaError($response, 'La fecha de vencimiento no es valida', 400);
        }

        $nuevo = Conductor::create($entrada);

        $response->getBody()->write(json_encode([
            'exito' => true,
            'mensaje' => 'Conductor registrado correctamente',
            'codigo' => 201,
            'registro' => $nuevo
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function actualizar(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $entrada = $request->getParsedBody();

        $conductor = Conductor::find($id);

        if (!$conductor) {
            return $this->respuestaError($response, 'Conductor no encontrado en el sistema', 404);
        }

        if (!empty($entrada['documento']) && $entrada['documento'] !== $conductor->documento) {
            $existe = Conductor::where('documento', $entrada['documento'])->first();
            if ($existe) {
                return $this->respuestaError($response, 'Este documento ya pertenece a otro conductor', 400);
            }
        }

        if (!empty($entrada['numero_licencia']) && $entrada['numero_licencia'] !== $conductor->numero_licencia) {
            $existe = Conductor::where('numero_licencia', $entrada['numero_licencia'])->first();
            if ($existe) {
                return $this->respuestaError($response, 'Esta licencia ya pertenece a otro conductor', 400);
            }
        }

        $conductor->update($entrada);

        $response->getBody()->write(json_encode([
            'exito' => true,
            'mensaje' => 'Informacion actualizada',
            'codigo' => 200,
            'registro' => $conductor
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function estado(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $entrada = $request->getParsedBody();

        $conductor = Conductor::find($id);

        if (!$conductor) {
            return $this->respuestaError($response, 'Conductor no encontrado en el sistema', 404);
        }

        $estadosValidos = ['disponible', 'en_ruta', 'inactivo'];

        if (empty($entrada['estado']) || !in_array($entrada['estado'], $estadosValidos)) {
            return $this->respuestaError($response, 'Estado no reconocido. Valores: disponible, en_ruta, inactivo', 400);
        }

        $conductor->estado = $entrada['estado'];
        $conductor->save();

        $response->getBody()->write(json_encode([
            'exito' => true,
            'mensaje' => 'Estado modificado',
            'codigo' => 200,
            'registro' => $conductor
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
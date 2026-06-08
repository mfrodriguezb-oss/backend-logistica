<?php

namespace App\Controllers;

use App\Models\Ruta;
use App\Models\ProgramacionViaje;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RutaController
{

    
    public function listarRutas(Request $request, Response $response)
    {
        $parametros = $request->getQueryParams();
        $consulta = Ruta::query();

        if (!empty($parametros['ciudad'])) {
            $consulta->where('ciudad_origen', 'like', '%' . $parametros['ciudad'] . '%')
                  ->orWhere('ciudad_destino', 'like', '%' . $parametros['ciudad'] . '%');
        }

        $resultados = $consulta->get();

        $response->getBody()->write(json_encode([
            'exito' => true,
            'total' => count($resultados),
            'lista' => $resultados
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function crearRuta(Request $request, Response $response)
    {
        $entrada = $request->getParsedBody();

        if (empty($entrada['ciudad_origen']) || empty($entrada['ciudad_destino']) || empty($entrada['distancia']) || empty($entrada['tiempo_estimado'])) {
            return $this->respuestaError($response, 'Todos los campos obligatorios deben completarse', 400);
        }

        if ($entrada['distancia'] <= 0) {
            return $this->respuestaError($response, 'La distancia debe ser mayor a cero', 400);
        }

        $existe = Ruta::where('ciudad_origen', $entrada['ciudad_origen'])
            ->where('ciudad_destino', $entrada['ciudad_destino'])
            ->first();

        if ($existe) {
            return $this->respuestaError($response, 'Ya existe una ruta entre estas ciudades', 400);
        }

        $nuevo = Ruta::create($entrada);

        $response->getBody()->write(json_encode([
            'exito' => true,
            'mensaje' => 'Ruta registrada correctamente',
            'codigo' => 201,
            'registro' => $nuevo
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function actualizarRuta(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $entrada = $request->getParsedBody();

        $ruta = Ruta::find($id);

        if (!$ruta) {
            return $this->respuestaError($response, 'Ruta no encontrada en el sistema', 404);
        }

        if (!empty($entrada['distancia']) && $entrada['distancia'] <= 0) {
            return $this->respuestaError($response, 'La distancia debe ser mayor a cero', 400);
        }

        $ruta->update($entrada);

        $response->getBody()->write(json_encode([
            'exito' => true,
            'mensaje' => 'Informacion de ruta actualizada',
            'codigo' => 200,
            'registro' => $ruta
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    
    public function listarProgramaciones(Request $request, Response $response)
    {
        $parametros = $request->getQueryParams();
        $consulta = ProgramacionViaje::query();

        if (!empty($parametros['conductor_id'])) {
            $consulta->where('conductor_id', $parametros['conductor_id']);
        }
        if (!empty($parametros['vehiculo_id'])) {
            $consulta->where('vehiculo_id', $parametros['vehiculo_id']);
        }
        if (!empty($parametros['estado'])) {
            $consulta->where('estado', $parametros['estado']);
        }
        if (!empty($parametros['fecha'])) {
            $consulta->where('fecha_salida', $parametros['fecha']);
        }

        $resultados = $consulta->get();

        $response->getBody()->write(json_encode([
            'exito' => true,
            'total' => count($resultados),
            'lista' => $resultados
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function programarViaje(Request $request, Response $response)
    {
        $entrada = $request->getParsedBody();

      
        if (empty($entrada['conductor_id']) || empty($entrada['vehiculo_id']) || empty($entrada['ruta_id']) || 
            empty($entrada['fecha_salida']) || empty($entrada['hora_salida']) || empty($entrada['fecha_estimada_llegada'])) {
            return $this->respuestaError($response, 'Todos los campos obligatorios deben completarse', 400);
        }

      
        if (!is_numeric($entrada['conductor_id']) || $entrada['conductor_id'] <= 0) {
            return $this->respuestaError($response, 'El conductor no es valido', 400);
        }


        if (!is_numeric($entrada['vehiculo_id']) || $entrada['vehiculo_id'] <= 0) {
            return $this->respuestaError($response, 'El vehiculo no es valido', 400);
        }

        $ruta = Ruta::find($entrada['ruta_id']);
        if (!$ruta) {
            return $this->respuestaError($response, 'La ruta seleccionada no existe', 400);
        }

        $nuevo = ProgramacionViaje::create($entrada);

        $response->getBody()->write(json_encode([
            'exito' => true,
            'mensaje' => 'Viaje programado correctamente',
            'codigo' => 201,
            'registro' => $nuevo
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function reprogramarViaje(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $entrada = $request->getParsedBody();

        $programacion = ProgramacionViaje::find($id);

        if (!$programacion) {
            return $this->respuestaError($response, 'Programacion no encontrada en el sistema', 404);
        }

        if ($programacion->estado === 'cancelado') {
            return $this->respuestaError($response, 'No se puede modificar un viaje que ha sido cancelado', 400);
        }

        $programacion->update($entrada);

        $response->getBody()->write(json_encode([
            'exito' => true,
            'mensaje' => 'Viaje reprogramado correctamente',
            'codigo' => 200,
            'registro' => $programacion
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function cambiarEstadoProgramacion(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $entrada = $request->getParsedBody();

        $programacion = ProgramacionViaje::find($id);

        if (!$programacion) {
            return $this->respuestaError($response, 'Programacion no encontrada en el sistema', 404);
        }

        $estado = $entrada['estado'] ?? '';
        $estadosValidos = ['programado', 'en_transito', 'retrasado', 'finalizado', 'cancelado'];

        if (!in_array($estado, $estadosValidos)) {
            return $this->respuestaError($response, 'Estado no reconocido. Valores permitidos: programado, en_transito, retrasado, finalizado, cancelado', 400);
        }

        if ($programacion->estado === 'cancelado' && $estado === 'en_transito') {
            return $this->respuestaError($response, 'No se puede iniciar un viaje que ha sido cancelado', 400);
        }

        if ($estado === 'finalizado' && !in_array($programacion->estado, ['en_transito', 'retrasado'])) {
            return $this->respuestaError($response, 'No se puede finalizar un viaje que no ha sido iniciado', 400);
        }

        $programacion->estado = $estado;
        $programacion->save();

        $response->getBody()->write(json_encode([
            'exito' => true,
            'mensaje' => 'Estado del viaje modificado',
            'codigo' => 200,
            'registro' => $programacion
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function finalizarViaje(Request $request, Response $response, $args)
    {
        $id = $args['id'];

        $programacion = ProgramacionViaje::find($id);

        if (!$programacion) {
            return $this->respuestaError($response, 'Programacion no encontrada en el sistema', 404);
        }

        if (!in_array($programacion->estado, ['en_transito', 'retrasado'])) {
            return $this->respuestaError($response, 'No se puede finalizar un viaje que no ha sido iniciado', 400);
        }

        $programacion->estado = 'finalizado';
        $programacion->save();

        $response->getBody()->write(json_encode([
            'exito' => true,
            'mensaje' => 'Viaje finalizado correctamente',
            'codigo' => 200,
            'registro' => $programacion
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
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
        $params = $request->getQueryParams();
        $query = Ruta::query();

        if (!empty($params['ciudad'])) {
            $query->where('ciudad_origen', 'like', '%' . $params['ciudad'] . '%')
                  ->orWhere('ciudad_destino', 'like', '%' . $params['ciudad'] . '%');
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $query->get()
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function crearRuta(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        if (empty($data['ciudad_origen']) || empty($data['ciudad_destino']) || empty($data['distancia']) || empty($data['tiempo_estimado'])) {
            return $this->error($response, 'Faltan campos obligatorios', 400);
        }

        if ($data['distancia'] <= 0) {
            return $this->error($response, 'La distancia debe ser mayor a 0', 400);
        }

        if (Ruta::where('ciudad_origen', $data['ciudad_origen'])->where('ciudad_destino', $data['ciudad_destino'])->first()) {
            return $this->error($response, 'Ya existe una ruta con ese origen y destino', 400);
        }

        $ruta = Ruta::create($data);
        return $this->exito($response, 'Ruta creada exitosamente', $ruta, 201);
    }

    public function actualizarRuta(Request $request, Response $response, $args)
    {
        $ruta = Ruta::find($args['id']);
        if (!$ruta) return $this->error($response, 'Ruta no encontrada', 404);

        $data = $request->getParsedBody();
        if (!empty($data['distancia']) && $data['distancia'] <= 0) {
            return $this->error($response, 'La distancia debe ser mayor a 0', 400);
        }

        $ruta->update($data);
        return $this->exito($response, 'Ruta actualizada exitosamente', $ruta);
    }

    public function listarProgramaciones(Request $request, Response $response)
    {
        $params = $request->getQueryParams();
        $query = ProgramacionViaje::query();

        if (!empty($params['conductor_id'])) $query->where('conductor_id', $params['conductor_id']);
        if (!empty($params['vehiculo_id'])) $query->where('vehiculo_id', $params['vehiculo_id']);
        if (!empty($params['estado'])) $query->where('estado', $params['estado']);
        if (!empty($params['fecha'])) $query->where('fecha_salida', $params['fecha']);

        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $query->get()
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function programarViaje(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        if (empty($data['conductor_id']) || empty($data['vehiculo_id']) || empty($data['ruta_id']) || empty($data['fecha_salida']) || empty($data['hora_salida']) || empty($data['fecha_estimada_llegada'])) {
            return $this->error($response, 'Faltan campos obligatorios', 400);
        }

        $programacion = ProgramacionViaje::create($data);
        return $this->exito($response, 'Viaje programado exitosamente', $programacion, 201);
    }

    public function reprogramarViaje(Request $request, Response $response, $args)
    {
        $programacion = ProgramacionViaje::find($args['id']);
        if (!$programacion) return $this->error($response, 'Programacion no encontrada', 404);
        if ($programacion->estado === 'cancelado') {
            return $this->error($response, 'No se puede modificar un viaje cancelado', 400);
        }

        $programacion->update($request->getParsedBody());
        return $this->exito($response, 'Viaje reprogramado exitosamente', $programacion);
    }

    public function cambiarEstadoProgramacion(Request $request, Response $response, $args)
    {
        $programacion = ProgramacionViaje::find($args['id']);
        if (!$programacion) return $this->error($response, 'Programacion no encontrada', 404);

        $estado = $request->getParsedBody()['estado'] ?? '';
        $estadosPermitidos = ['programado', 'en_transito', 'retrasado', 'finalizado', 'cancelado'];

        if (!in_array($estado, $estadosPermitidos)) {
            return $this->error($response, 'Estado no valido', 400);
        }
        if ($programacion->estado === 'cancelado' && $estado === 'en_transito') {
            return $this->error($response, 'No se puede iniciar un viaje cancelado', 400);
        }

        if ($estado === 'finalizado' && !in_array($programacion->estado, ['en_transito', 'retrasado'])) {
            return $this->error($response, 'No se puede finalizar un viaje no iniciado', 400);
        }

        $programacion->estado = $estado;
        $programacion->save();

        return $this->exito($response, 'Estado actualizado', $programacion);
    }

    public function finalizarViaje(Request $request, Response $response, $args)
    {
        $programacion = ProgramacionViaje::find($args['id']);
        if (!$programacion) return $this->error($response, 'Programacion no encontrada', 404);

        if (!in_array($programacion->estado, ['en_transito', 'retrasado'])) {
            return $this->error($response, 'No se puede finalizar un viaje no iniciado', 400);
        }

        $programacion->estado = 'finalizado';
        $programacion->save();

        return $this->exito($response, 'Viaje finalizado exitosamente', $programacion);
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
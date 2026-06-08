<?php

namespace App\Controllers;

use App\Models\Usuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    public function login(Request $request, Response $response)
    {
        $datos = $request->getParsedBody();
        $identificador = $datos['usuario'] ?? '';
        $clave = $datos['contrasena'] ?? '';

        $usuario = Usuario::where('usuario', $identificador)->first();

        if (!$usuario) {
            $usuario = Usuario::where('correo', $identificador)->first();
        }

        if (!$usuario || $usuario->contrasena !== $clave) {
            $response->getBody()->write(json_encode([
                'exito' => false,
                'mensaje' => 'Credenciales invalidas',
                'codigo' => 401
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $token = md5(uniqid() . time());

        $usuario->token = $token;
        $usuario->sesion_activa = 1;
        $usuario->save();

        $response->getBody()->write(json_encode([
            'exito' => true,
            'mensaje' => 'Autenticacion exitosa',
            'codigo' => 200,
            'datos' => [
                'token' => $token,
                'nombre' => $usuario->nombre,
                'rol' => $usuario->rol
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function logout(Request $request, Response $response)
    {
        $cabeceras = $request->getHeaders();
        $token = $cabeceras['HTTP_TOKEN'][0] ?? '';

        $usuario = Usuario::where('token', $token)->first();

        if ($usuario) {
            $usuario->token = null;
            $usuario->sesion_activa = 0;
            $usuario->save();
        }

        $response->getBody()->write(json_encode([
            'exito' => true,
            'mensaje' => 'Sesion finalizada',
            'codigo' => 200
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function validar(Request $request, Response $response)
    {
        $cabeceras = $request->getHeaders();
        $token = $cabeceras['HTTP_TOKEN'][0] ?? '';

        $usuario = Usuario::where('token', $token)
            ->where('sesion_activa', 1)
            ->where('estado', 'activo')
            ->first();

        if (!$usuario) {
            $response->getBody()->write(json_encode([
                'exito' => false,
                'mensaje' => 'Token no valido',
                'codigo' => 401
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $response->getBody()->write(json_encode([
            'exito' => true,
            'mensaje' => 'Token activo',
            'codigo' => 200,
            'datos' => [
                'nombre' => $usuario->nombre,
                'rol' => $usuario->rol
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
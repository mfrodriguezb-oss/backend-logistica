<?php

namespace App\Controllers;

use App\Models\Usuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    // Login
    public function login(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $usuario = $data['usuario'] ?? '';
        $contrasena = $data['contrasena'] ?? '';

        $user = Usuario::where('usuario', $usuario)
            ->orWhere('correo', $usuario)
            ->first();

        if (!$user || $user->contrasena !== $contrasena) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Credenciales incorrectas'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        // Generar token simple
        $token = bin2hex(random_bytes(32));
        $user->token = $token;
        $user->sesion_activa = 1;  // ← Usamos 1 en vez de true
        $user->save();

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Login exitoso',
            'token' => $token,
            'usuario' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'usuario' => $user->usuario,
                'rol' => $user->rol
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Logout
    public function logout(Request $request, Response $response)
    {
        $headers = $request->getHeaders();
        $token = $headers['HTTP_TOKEN'][0] ?? '';

        $user = Usuario::where('token', $token)->first();

        if ($user) {
            $user->token = null;
            $user->sesion_activa = 0;  // ← Usamos 0 en vez de false
            $user->save();
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Sesion cerrada correctamente'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }


    public function validar(Request $request, Response $response)
    {
        $headers = $request->getHeaders();
        $token = $headers['HTTP_TOKEN'][0] ?? '';

        $user = Usuario::where('token', $token)
            ->where('sesion_activa', 1)  // ← Comparamos con 1
            ->where('estado', 'activo')
            ->first();

        if (!$user) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Token invalido o sesion inactiva'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Sesion valida',
            'usuario' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'rol' => $user->rol
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
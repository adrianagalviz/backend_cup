<?php

namespace App\Http\Middleware;

use App\Models\UsuarioModel;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->attributes->get('usuario_autenticado');

        if (!$usuario instanceof UsuarioModel) {
            return ApiResponse::error('Usuario autenticado requerido.', [], 401);
        }

        $rol = $usuario->rol?->nombre;

        if (!$rol || !in_array($rol, $roles, true)) {
            return ApiResponse::error('No tienes permisos para acceder a este recurso.', [], 403);
        }

        return $next($request);
    }
}

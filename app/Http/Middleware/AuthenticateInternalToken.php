<?php

namespace App\Http\Middleware;

use App\Models\UsuarioModel;
use App\Services\Auth\InternalTokenService;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateInternalToken
{
    public function __construct(
        private readonly InternalTokenService $tokens,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return ApiResponse::error('Token de autenticacion requerido.', [], 401);
        }

        $userId = $this->tokens->userIdFromToken($token);

        if (!$userId) {
            return ApiResponse::error('Token de autenticacion invalido o expirado.', [], 401);
        }

        $usuario = UsuarioModel::query()
            ->with(['rol', 'persona', 'administrador', 'docente', 'alumno'])
            ->find($userId);

        if (!$usuario || !$usuario->activo) {
            return ApiResponse::error('Usuario inactivo o no autorizado.', [], 401);
        }

        $request->attributes->set('usuario_autenticado', $usuario);

        return $next($request);
    }
}

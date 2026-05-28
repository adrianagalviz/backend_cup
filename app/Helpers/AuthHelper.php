<?php

namespace App\Helpers;

use App\Models\UsuarioModel;
use App\Services\Auth\InternalTokenService;
use Illuminate\Http\Request;

class AuthHelper
{
    public static function currentUser(Request $request): ?UsuarioModel
    {
        $token = $request->bearerToken();

        if (!$token) {
            return null;
        }

        $userId = app(InternalTokenService::class)->userIdFromToken($token);

        if (!$userId) {
            return null;
        }

        return UsuarioModel::query()
            ->with(['rol', 'persona', 'administrador', 'docente', 'alumno'])
            ->find($userId);
    }
}

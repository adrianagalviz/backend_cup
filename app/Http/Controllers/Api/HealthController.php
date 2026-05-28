<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Database\PostgreSqlConnectionService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Throwable;

class HealthController extends Controller
{
    public function salud(): JsonResponse
    {
        return ApiResponse::success(
            'API REST del sistema CUP FICCT disponible.',
            [
                'aplicacion' => config('app.name'),
                'entorno' => config('app.env'),
                'php' => PHP_VERSION,
                'laravel' => app()->version(),
            ]
        );
    }

    public function conexionPostgresql(PostgreSqlConnectionService $postgresql): JsonResponse
    {
        try {
            $resultado = $postgresql->testConnection();

            return ApiResponse::success(
                'Conexion con PostgreSQL verificada correctamente.',
                [
                    'conexion' => config('database.default'),
                    'base_datos' => config('database.connections.pgsql.database'),
                    'version' => $resultado?->version,
                ]
            );
        } catch (Throwable) {
            return ApiResponse::error(
                'No se pudo conectar con PostgreSQL. Verifica DB_DATABASE, DB_USERNAME y DB_PASSWORD en .env.',
                $postgresql->safeConnectionMetadata(),
                503
            );
        }
    }
}

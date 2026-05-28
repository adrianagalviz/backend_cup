<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(string $mensaje, array|object|null $datos = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'mensaje' => $mensaje,
            'datos' => $datos ?? [],
        ], $status);
    }

    public static function error(string $mensaje, array|object|null $errores = [], int $status = 400): JsonResponse
    {
        return response()->json([
            'ok' => false,
            'mensaje' => $mensaje,
            'errores' => $errores ?? [],
        ], $status);
    }
}

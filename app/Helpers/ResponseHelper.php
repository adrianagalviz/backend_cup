<?php

namespace App\Helpers;

use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ResponseHelper
{
    public static function success(string $mensaje, array|object|null $datos = [], int $status = 200): JsonResponse
    {
        return ApiResponse::success($mensaje, $datos, $status);
    }

    public static function error(string $mensaje, array|object|null $errores = [], int $status = 400): JsonResponse
    {
        return ApiResponse::error($mensaje, $errores, $status);
    }
}

<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/salud', [HealthController::class, 'salud']);
    Route::get('/conexion-postgresql', [HealthController::class, 'conexionPostgresql']);

    Route::prefix('auth')->group(function (): void {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/alumno/login', [AuthController::class, 'loginAlumno']);
        Route::post('/firebase', [AuthController::class, 'firebase']);

        Route::middleware('auth.internal')->group(function (): void {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/perfil', [AuthController::class, 'perfil']);
        });
    });
});

Route::any('{fallbackPlaceholder}', function () {
    return response()->json([
        'ok' => false,
        'mensaje' => 'Ruta API no encontrada.',
        'errores' => [],
    ], 404);
})->where('fallbackPlaceholder', '.*');

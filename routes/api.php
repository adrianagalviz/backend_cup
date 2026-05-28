<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\ApplicantController;
use App\Http\Controllers\Api\ApplicantDocumentController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\UserController;
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

    Route::middleware(['auth.internal', 'role:administrador'])->prefix('usuarios')->group(function (): void {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/administradores', [UserController::class, 'createAdministrator']);
        Route::get('/{id}', [UserController::class, 'show'])->whereNumber('id');
        Route::put('/{id}', [UserController::class, 'update'])->whereNumber('id');
        Route::patch('/{id}/estado', [UserController::class, 'status'])->whereNumber('id');
    });

    Route::post('/postulantes', [ApplicantController::class, 'store']);
    Route::post('/postulantes/{id}/documentos', [ApplicantDocumentController::class, 'store'])->whereNumber('id');

    Route::prefix('pagos')->group(function (): void {
        Route::post('/stripe/crear-sesion', [PaymentController::class, 'createStripeSession']);
        Route::post('/stripe/webhook', [PaymentController::class, 'stripeWebhook']);
    });

    Route::middleware(['auth.internal', 'role:administrador'])->prefix('postulantes')->group(function (): void {
        Route::get('/', [ApplicantController::class, 'index']);
        Route::get('/{id}/documentos', [ApplicantDocumentController::class, 'index'])->whereNumber('id');
        Route::patch('/{id}/requisitos/validar', [ApplicantDocumentController::class, 'validateRequirements'])->whereNumber('id');
        Route::get('/{id}', [ApplicantController::class, 'show'])->whereNumber('id');
        Route::put('/{id}', [ApplicantController::class, 'update'])->whereNumber('id');
        Route::delete('/{id}', [ApplicantController::class, 'destroy'])->whereNumber('id');
    });

    Route::middleware(['auth.internal', 'role:administrador'])->prefix('pagos')->group(function (): void {
        Route::get('/postulante/{id}', [PaymentController::class, 'byApplicant'])->whereNumber('id');
        Route::patch('/{id}/validar-admin', [PaymentController::class, 'validateAdmin'])->whereNumber('id');
    });
});

Route::any('{fallbackPlaceholder}', function () {
    return response()->json([
        'ok' => false,
        'mensaje' => 'Ruta API no encontrada.',
        'errores' => [],
    ], 404);
})->where('fallbackPlaceholder', '.*');

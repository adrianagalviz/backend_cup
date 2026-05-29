<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\ApplicantController;
use App\Http\Controllers\Api\ApplicantConversionController;
use App\Http\Controllers\Api\ApplicantDocumentController;
use App\Http\Controllers\Api\AcademicManagementController;
use App\Http\Controllers\Api\ClassroomGroupController;
use App\Http\Controllers\Api\ClassScheduleController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ScheduleCatalogController;
use App\Http\Controllers\Api\TeacherController;
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
        Route::post('/{id}/convertir-alumno', [ApplicantConversionController::class, 'convertToStudent'])->whereNumber('id');
        Route::get('/{id}', [ApplicantController::class, 'show'])->whereNumber('id');
        Route::put('/{id}', [ApplicantController::class, 'update'])->whereNumber('id');
        Route::delete('/{id}', [ApplicantController::class, 'destroy'])->whereNumber('id');
    });

    Route::middleware(['auth.internal', 'role:administrador'])->prefix('pagos')->group(function (): void {
        Route::get('/postulante/{id}', [PaymentController::class, 'byApplicant'])->whereNumber('id');
        Route::patch('/{id}/validar-admin', [PaymentController::class, 'validateAdmin'])->whereNumber('id');
    });

    Route::middleware(['auth.internal', 'role:administrador'])->group(function (): void {
        Route::get('/gestiones', [AcademicManagementController::class, 'listGestiones']);
        Route::post('/gestiones', [AcademicManagementController::class, 'createGestion']);
        Route::get('/gestiones/actual', [AcademicManagementController::class, 'currentGestion']);

        Route::get('/carreras', [AcademicManagementController::class, 'listCareers']);
        Route::post('/carreras', [AcademicManagementController::class, 'createCareer']);
        Route::put('/carreras/{id}', [AcademicManagementController::class, 'updateCareer'])->whereNumber('id');

        Route::get('/cupos', [AcademicManagementController::class, 'listQuotas']);
        Route::post('/cupos', [AcademicManagementController::class, 'createQuota']);
        Route::put('/cupos/{id}', [AcademicManagementController::class, 'updateQuota'])->whereNumber('id');

        Route::get('/docentes', [TeacherController::class, 'index']);
        Route::post('/docentes', [TeacherController::class, 'store']);
        Route::get('/docentes/buscar', [TeacherController::class, 'search']);
        Route::get('/docentes/{id}', [TeacherController::class, 'show'])->whereNumber('id');
        Route::put('/docentes/{id}', [TeacherController::class, 'update'])->whereNumber('id');
        Route::delete('/docentes/{id}', [TeacherController::class, 'destroy'])->whereNumber('id');

        Route::get('/materias', [ClassroomGroupController::class, 'subjects']);

        Route::get('/grupos', [ClassroomGroupController::class, 'listGroups']);
        Route::post('/grupos', [ClassroomGroupController::class, 'createGroup']);
        Route::get('/grupos/calcular-necesarios', [ClassroomGroupController::class, 'calculateGroups']);
        Route::post('/grupos/asignar-alumnos', [ClassroomGroupController::class, 'assignStudents']);
        Route::get('/grupos/{id}/alumnos', [ClassroomGroupController::class, 'groupStudents'])->whereNumber('id');

        Route::get('/aulas', [ClassroomGroupController::class, 'listClassrooms']);
        Route::post('/aulas', [ClassroomGroupController::class, 'createClassroom']);
        Route::put('/aulas/{id}', [ClassroomGroupController::class, 'updateClassroom'])->whereNumber('id');

        Route::get('/dias', [ScheduleCatalogController::class, 'days']);

        Route::get('/turnos', [ScheduleCatalogController::class, 'shifts']);
        Route::post('/turnos', [ScheduleCatalogController::class, 'createShift']);

        Route::get('/periodos', [ScheduleCatalogController::class, 'periods']);
        Route::post('/periodos', [ScheduleCatalogController::class, 'createPeriod']);

        Route::get('/horarios', [ClassScheduleController::class, 'index']);
        Route::post('/horarios', [ClassScheduleController::class, 'store']);
    });

    Route::middleware(['auth.internal', 'role:administrador,docente'])
        ->get('/horarios/docente/{id}', [ClassScheduleController::class, 'teacherSchedules'])
        ->whereNumber('id');

    Route::middleware(['auth.internal', 'role:administrador,alumno'])
        ->get('/horarios/alumno/{id}', [ClassScheduleController::class, 'studentSchedules'])
        ->whereNumber('id');
});

Route::any('{fallbackPlaceholder}', function () {
    return response()->json([
        'ok' => false,
        'mensaje' => 'Ruta API no encontrada.',
        'errores' => [],
    ], 404);
})->where('fallbackPlaceholder', '.*');

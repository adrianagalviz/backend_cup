<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdmissionController;
use App\Http\Controllers\Api\BulkLoadController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\ApplicantController;
use App\Http\Controllers\Api\ApplicantConversionController;
use App\Http\Controllers\Api\ApplicantDocumentController;
use App\Http\Controllers\Api\AcademicManagementController;
use App\Http\Controllers\Api\ClassroomGroupController;
use App\Http\Controllers\Api\ClassScheduleController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\GradeAverageController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\RequirementController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ScheduleCatalogController;
use App\Http\Controllers\Api\StudentAttendanceController;
use App\Http\Controllers\Api\StudentExamController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\TeacherAssignmentController;
use App\Http\Controllers\Api\TeacherAttendanceController;
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
    Route::get('/gestiones/actual', [AcademicManagementController::class, 'currentGestion']);
    Route::get('/carreras/activas', [AcademicManagementController::class, 'listActiveCareers']);

    Route::prefix('pagos')->group(function (): void {
        Route::post('/stripe/crear-sesion', [PaymentController::class, 'createStripeSession']);
        Route::post('/stripe/webhook', [PaymentController::class, 'stripeWebhook']);
        Route::get('/postulante/{id}/estado-publico', [PaymentController::class, 'publicStatusByApplicant'])->whereNumber('id');
        Route::post('/postulante/{id}/pago-temporal', [PaymentController::class, 'temporaryAutomaticPayment'])->whereNumber('id');
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
        Route::get('/', [PaymentController::class, 'index']);
        Route::get('/postulante/{id}', [PaymentController::class, 'byApplicant'])->whereNumber('id');
        Route::patch('/{id}/validar-admin', [PaymentController::class, 'validateAdmin'])->whereNumber('id');
    });

    Route::middleware(['auth.internal', 'role:administrador'])->group(function (): void {
        Route::get('/gestiones', [AcademicManagementController::class, 'listGestiones']);
        Route::post('/gestiones', [AcademicManagementController::class, 'createGestion']);
        Route::patch('/gestiones/{id}/activar', [AcademicManagementController::class, 'setCurrentGestion'])->whereNumber('id');

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

        Route::get('/requisitos', [RequirementController::class, 'index']);
        Route::patch('/requisitos/{id}/validar', [RequirementController::class, 'validateRequirement'])->whereNumber('id');

        Route::get('/alumnos', [StudentController::class, 'index']);
        Route::get('/alumnos/{id}', [StudentController::class, 'show'])->whereNumber('id');

        Route::get('/materias', [ClassroomGroupController::class, 'subjects']);

        Route::get('/grupos', [ClassroomGroupController::class, 'listGroups']);
        Route::post('/grupos', [ClassroomGroupController::class, 'createGroup']);
        Route::put('/grupos/{id}', [ClassroomGroupController::class, 'updateGroup'])->whereNumber('id');
        Route::delete('/grupos/{id}', [ClassroomGroupController::class, 'deactivateGroup'])->whereNumber('id');
        Route::get('/grupos/calcular-necesarios', [ClassroomGroupController::class, 'calculateGroups']);
        Route::post('/grupos/asignar-alumnos', [ClassroomGroupController::class, 'assignStudents']);
        Route::get('/grupos/{id}/alumnos', [ClassroomGroupController::class, 'groupStudents'])->whereNumber('id');

        Route::get('/aulas', [ClassroomGroupController::class, 'listClassrooms']);
        Route::post('/aulas', [ClassroomGroupController::class, 'createClassroom']);
        Route::put('/aulas/{id}', [ClassroomGroupController::class, 'updateClassroom'])->whereNumber('id');

        Route::get('/dias', [ScheduleCatalogController::class, 'days']);

        Route::get('/turnos', [ScheduleCatalogController::class, 'shifts']);
        Route::post('/turnos', [ScheduleCatalogController::class, 'createShift']);
        Route::put('/turnos/{id}', [ScheduleCatalogController::class, 'updateShift'])->whereNumber('id');
        Route::delete('/turnos/{id}', [ScheduleCatalogController::class, 'deleteShift'])->whereNumber('id');

        Route::get('/periodos', [ScheduleCatalogController::class, 'periods']);
        Route::post('/periodos', [ScheduleCatalogController::class, 'createPeriod']);
        Route::put('/periodos/{id}', [ScheduleCatalogController::class, 'updatePeriod'])->whereNumber('id');
        Route::delete('/periodos/{id}', [ScheduleCatalogController::class, 'deletePeriod'])->whereNumber('id');

        Route::get('/horarios', [ClassScheduleController::class, 'index']);
        Route::post('/horarios', [ClassScheduleController::class, 'store']);
        Route::put('/horarios/{id}', [ClassScheduleController::class, 'update'])->whereNumber('id');
        Route::delete('/horarios/{id}', [ClassScheduleController::class, 'destroy'])->whereNumber('id');

        Route::get('/asignaciones', [TeacherAssignmentController::class, 'index']);
        Route::post('/asignaciones/docente-materia-grupo', [TeacherAssignmentController::class, 'store']);
        Route::get('/asignaciones/docente/{id}', [TeacherAssignmentController::class, 'byTeacher'])->whereNumber('id');
        Route::get('/asignaciones/grupo/{id}', [TeacherAssignmentController::class, 'byGroup'])->whereNumber('id');
        Route::get('/asignaciones/materia/{id}', [TeacherAssignmentController::class, 'bySubject'])->whereNumber('id');
        Route::delete('/asignaciones/{id}', [TeacherAssignmentController::class, 'destroy'])->whereNumber('id');

        Route::get('/asistencia-docente', [TeacherAttendanceController::class, 'index']);
        Route::get('/asistencia-docente/docente/{id}', [TeacherAttendanceController::class, 'byTeacher'])->whereNumber('id');
        Route::post('/asistencia-docente/generar-faltas', [TeacherAttendanceController::class, 'generateAbsences']);

        Route::get('/examenes', [ExamController::class, 'index']);
        Route::post('/examenes', [ExamController::class, 'store']);
        Route::get('/examenes/{id}', [ExamController::class, 'show'])->whereNumber('id');
        Route::post('/examenes/{id}/materias', [ExamController::class, 'subjects'])->whereNumber('id');
        Route::post('/examenes/{id}/preguntas', [ExamController::class, 'question'])->whereNumber('id');
        Route::patch('/examenes/{id}/habilitar', [ExamController::class, 'enable'])->whereNumber('id');
        Route::patch('/examenes/{id}/deshabilitar', [ExamController::class, 'disable'])->whereNumber('id');
        Route::post('/preguntas/{id}/opciones', [ExamController::class, 'options'])->whereNumber('id');

        Route::post('/promedios/calcular', [GradeAverageController::class, 'calculate']);

        Route::post('/admisiones/asignar-carreras', [AdmissionController::class, 'assignCareers']);

        Route::get('/reportes/postulantes', [ReportController::class, 'applicants']);
        Route::get('/reportes/aprobados', [ReportController::class, 'approved']);
        Route::get('/reportes/reprobados', [ReportController::class, 'failed']);
        Route::get('/reportes/promedios', [ReportController::class, 'averages']);
        Route::get('/reportes/grupos', [ReportController::class, 'groups']);
        Route::get('/reportes/estadisticas-materia', [ReportController::class, 'subjectStatistics']);
        Route::get('/reportes/docentes-grupos', [ReportController::class, 'teachersGroups']);
        Route::get('/reportes/grupos-mayor-aprobados', [ReportController::class, 'groupsMostApproved']);
        Route::get('/reportes/asistencia-docentes', [ReportController::class, 'teacherAttendance']);
        Route::get('/reportes/asistencia-alumnos', [ReportController::class, 'studentAttendance']);
        Route::post('/reportes/comando-voz', [ReportController::class, 'voiceCommand']);
        Route::get('/reportes/{tipo}/exportar', [ReportController::class, 'export']);

        Route::get('/cargas', [BulkLoadController::class, 'index']);
        Route::post('/cargas/csv', [BulkLoadController::class, 'csv']);
        Route::post('/cargas/excel', [BulkLoadController::class, 'excel']);
        Route::get('/cargas/{id}/detalle', [BulkLoadController::class, 'detail'])->whereNumber('id');

        Route::get('/dashboard/resumen', [DashboardController::class, 'summary']);
        Route::get('/dashboard/asistencia', [DashboardController::class, 'attendance']);
        Route::get('/dashboard/cupos', [DashboardController::class, 'quotas']);
        Route::get('/dashboard/examenes', [DashboardController::class, 'exams']);
    });

    Route::middleware(['auth.internal', 'role:administrador,docente'])
        ->get('/horarios/docente/{id}', [ClassScheduleController::class, 'teacherSchedules'])
        ->whereNumber('id');

    Route::middleware(['auth.internal', 'role:administrador,alumno'])
        ->get('/horarios/alumno/{id}', [ClassScheduleController::class, 'studentSchedules'])
        ->whereNumber('id');

    Route::middleware(['auth.internal', 'role:administrador,alumno'])->group(function (): void {
        Route::get('/notas/alumno/{id}', [GradeAverageController::class, 'notesByStudent'])->whereNumber('id');
        Route::get('/promedios', [GradeAverageController::class, 'averages']);
        Route::get('/promedios/aprobados', [GradeAverageController::class, 'approved']);
        Route::get('/promedios/reprobados', [GradeAverageController::class, 'failed']);
    });

    Route::middleware(['auth.internal', 'role:docente'])->prefix('asistencia-docente')->group(function (): void {
        Route::get('/horario-activo', [TeacherAttendanceController::class, 'activeSchedule']);
        Route::post('/marcar-entrada', [TeacherAttendanceController::class, 'markEntry']);
        Route::post('/marcar-salida', [TeacherAttendanceController::class, 'markExit']);
    });

    Route::middleware(['auth.internal', 'role:alumno'])->prefix('asistencia-alumno')->group(function (): void {
        Route::get('/horario-activo', [StudentAttendanceController::class, 'activeSchedule']);
        Route::post('/marcar', [StudentAttendanceController::class, 'mark']);
        Route::get('/mis-asistencias', [StudentAttendanceController::class, 'myAttendance']);
    });

    Route::middleware(['auth.internal', 'role:alumno'])->prefix('alumno')->group(function (): void {
        Route::get('/grupos/opciones', [ClassroomGroupController::class, 'studentGroupOptions']);
        Route::post('/grupo/asignacion', [ClassroomGroupController::class, 'assignStudentGroup']);
    });

    Route::middleware(['auth.internal', 'role:alumno'])->prefix('alumno/examenes')->group(function (): void {
        Route::get('/habilitados', [StudentExamController::class, 'enabled']);
        Route::get('/{id}', [StudentExamController::class, 'show'])->whereNumber('id');
        Route::post('/{id}/responder', [StudentExamController::class, 'answer'])->whereNumber('id');
        Route::get('/{id}/resultado', [StudentExamController::class, 'result'])->whereNumber('id');
    });

    Route::middleware(['auth.internal', 'role:docente'])->prefix('asistencia-alumno/docente')->group(function (): void {
        Route::post('/registrar', [StudentAttendanceController::class, 'registerByTeacher']);
        Route::get('/mis-alumnos', [StudentAttendanceController::class, 'teacherStudents']);
    });

    Route::middleware(['auth.internal', 'role:administrador'])->prefix('asistencia-alumno')->group(function (): void {
        Route::get('/', [StudentAttendanceController::class, 'index']);
        Route::post('/generar-faltas', [StudentAttendanceController::class, 'generateAbsences']);
    });
});

Route::any('{fallbackPlaceholder}', function () {
    return response()->json([
        'ok' => false,
        'mensaje' => 'Ruta API no encontrada.',
        'errores' => [],
    ], 404);
})->where('fallbackPlaceholder', '.*');

<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Models\AsistenciaAlumnoModel;
use App\Services\Attendance\StudentAttendanceService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

class StudentAttendanceController extends Controller
{
    public function __construct(
        private readonly StudentAttendanceService $attendance,
    ) {
    }

    public function activeSchedule(Request $request): JsonResponse
    {
        try {
            $result = $this->attendance->detectActiveSchedule($request->attributes->get('usuario_autenticado'));
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Deteccion de horario del alumno realizada correctamente.', $result);
    }

    public function mark(Request $request): JsonResponse
    {
        try {
            $attendance = $this->attendance->markByStudent($request->attributes->get('usuario_autenticado'));
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Asistencia del alumno marcada correctamente.', [
            'asistencia' => $this->attendance->formatAttendance($attendance),
        ], 201);
    }

    public function registerByTeacher(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'horario_clase_id' => ['required', 'integer', 'exists:horario_clase,id'],
            'asistencias' => ['required', 'array', 'min:1'],
            'asistencias.*.alumno_id' => ['required', 'integer', 'exists:alumno,id'],
            'asistencias.*.estado_asistencia' => ['nullable', Rule::in(['presente', 'retraso', 'falta'])],
            'asistencias.*.observacion' => ['nullable', 'string'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $result = $this->attendance->registerByTeacher(
                $request->attributes->get('usuario_autenticado'),
                $validator->validated()
            );
        } catch (ModelNotFoundException) {
            return ApiResponse::error('No se encontro el horario o alumno indicado.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Asistencia de alumnos registrada correctamente.', $result, 201);
    }

    public function generateAbsences(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fecha' => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $result = $this->attendance->generateAutomaticAbsences($validator->validated()['fecha'] ?? null);

        return ApiResponse::success('Faltas automaticas de alumnos generadas correctamente.', $result);
    }

    public function myAttendance(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), $this->filterRules());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $records = $this->attendance->listForStudent(
            $request->attributes->get('usuario_autenticado'),
            $validator->validated()
        );

        return $this->paginatedResponse('Mis asistencias obtenidas correctamente.', $records);
    }

    public function teacherStudents(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), $this->filterRules());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $records = $this->attendance->listForTeacherStudents(
            $request->attributes->get('usuario_autenticado'),
            $validator->validated()
        );

        return $this->paginatedResponse('Asistencias de alumnos del docente obtenidas correctamente.', $records);
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), $this->filterRules());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $records = $this->attendance->listForAdmin($validator->validated());

        return $this->paginatedResponse('Asistencias de alumnos obtenidas correctamente.', $records);
    }

    private function paginatedResponse(string $message, $records): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'mensaje' => $message,
            'datos' => collect($records->items())
                ->map(fn (AsistenciaAlumnoModel $attendance) => $this->attendance->formatAttendance($attendance))
                ->values(),
            'meta' => [
                'pagina_actual' => $records->currentPage(),
                'por_pagina' => $records->perPage(),
                'total' => $records->total(),
                'ultima_pagina' => $records->lastPage(),
            ],
        ]);
    }

    private function filterRules(): array
    {
        return [
            'alumno_id' => ['nullable', 'integer', 'exists:alumno,id'],
            'docente_id' => ['nullable', 'integer', 'exists:docente,id'],
            'fecha' => ['nullable', 'date'],
            'grupo_id' => ['nullable', 'integer', 'exists:grupo,id'],
            'materia_id' => ['nullable', 'integer', 'exists:materia,id'],
            'estado' => ['nullable', Rule::in(['pendiente', 'presente', 'retraso', 'falta'])],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    private function messages(): array
    {
        return [
            'horario_clase_id.required' => 'El horario de clase es obligatorio.',
            'horario_clase_id.exists' => 'El horario de clase indicado no existe.',
            'asistencias.required' => 'La lista de asistencias es obligatoria.',
            'asistencias.array' => 'La lista de asistencias debe ser un arreglo.',
            'asistencias.*.alumno_id.required' => 'El alumno es obligatorio.',
            'asistencias.*.alumno_id.exists' => 'Uno de los alumnos indicados no existe.',
            'asistencias.*.estado_asistencia.in' => 'El estado de asistencia debe ser presente, retraso o falta.',
        ];
    }
}

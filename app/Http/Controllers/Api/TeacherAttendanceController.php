<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsistenciaDocenteModel;
use App\Services\Attendance\TeacherAttendanceService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

class TeacherAttendanceController extends Controller
{
    public function __construct(
        private readonly TeacherAttendanceService $attendance,
    ) {
    }

    public function activeSchedule(Request $request): JsonResponse
    {
        try {
            $result = $this->attendance->detectActiveSchedule($request->attributes->get('usuario_autenticado'));
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Deteccion de horario docente realizada correctamente.', $result);
    }

    public function markEntry(Request $request): JsonResponse
    {
        try {
            $attendance = $this->attendance->markEntry($request->attributes->get('usuario_autenticado'));
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Entrada docente marcada correctamente.', [
            'asistencia' => $this->attendance->formatAttendance($attendance),
        ], 201);
    }

    public function markExit(Request $request): JsonResponse
    {
        try {
            $attendance = $this->attendance->markExit($request->attributes->get('usuario_autenticado'));
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Salida docente marcada correctamente.', [
            'asistencia' => $this->attendance->formatAttendance($attendance),
        ]);
    }

    public function generateAbsences(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fecha' => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return \App\Helpers\ValidationHelper::failed($validator);
        }

        $result = $this->attendance->generateAutomaticAbsences($validator->validated()['fecha'] ?? null);

        return ApiResponse::success('Faltas automaticas docentes generadas correctamente.', $result);
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), $this->filterRules());

        if ($validator->fails()) {
            return \App\Helpers\ValidationHelper::failed($validator);
        }

        $records = $this->attendance->listAttendance($validator->validated());

        return response()->json([
            'ok' => true,
            'mensaje' => 'Asistencias docentes obtenidas correctamente.',
            'datos' => collect($records->items())
                ->map(fn (AsistenciaDocenteModel $attendance) => $this->attendance->formatAttendance($attendance))
                ->values(),
            'meta' => [
                'pagina_actual' => $records->currentPage(),
                'por_pagina' => $records->perPage(),
                'total' => $records->total(),
                'ultima_pagina' => $records->lastPage(),
            ],
        ]);
    }

    public function byTeacher(Request $request, int $id): JsonResponse
    {
        $query = array_merge($request->query(), ['docente_id' => $id]);
        $validator = Validator::make($query, $this->filterRules());

        if ($validator->fails()) {
            return \App\Helpers\ValidationHelper::failed($validator);
        }

        $records = $this->attendance->listAttendance($validator->validated());

        return response()->json([
            'ok' => true,
            'mensaje' => 'Asistencias del docente obtenidas correctamente.',
            'datos' => collect($records->items())
                ->map(fn (AsistenciaDocenteModel $attendance) => $this->attendance->formatAttendance($attendance))
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
            'docente_id' => ['nullable', 'integer', 'exists:docente,id'],
            'fecha' => ['nullable', 'date'],
            'grupo_id' => ['nullable', 'integer', 'exists:grupo,id'],
            'materia_id' => ['nullable', 'integer', 'exists:materia,id'],
            'estado' => ['nullable', Rule::in(['pendiente', 'presente', 'retraso', 'falta'])],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}

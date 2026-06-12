<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Models\HorarioClaseModel;
use App\Services\Academic\ClassScheduleService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

class ClassScheduleController extends Controller
{
    public function __construct(
        private readonly ClassScheduleService $schedules,
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->scheduleRules(), $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $schedule = $this->schedules->createSchedule($validator->validated());
        } catch (ModelNotFoundException) {
            return ApiResponse::error('No se encontro uno de los recursos requeridos para crear el horario.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Horario de clase creado correctamente.', [
            'horario' => $this->schedules->formatSchedule($schedule),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), $this->listRules(), $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $schedules = $this->schedules->listSchedules($validator->validated());

        return response()->json([
            'ok' => true,
            'mensaje' => 'Horarios obtenidos correctamente.',
            'datos' => collect($schedules->items())
                ->map(fn (HorarioClaseModel $schedule) => $this->schedules->formatSchedule($schedule))
                ->values(),
            'meta' => [
                'pagina_actual' => $schedules->currentPage(),
                'por_pagina' => $schedules->perPage(),
                'total' => $schedules->total(),
                'ultima_pagina' => $schedules->lastPage(),
            ],
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->updateRules(), $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $schedule = $this->schedules->updateSchedule($id, $validator->validated());
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Horario no encontrado o recurso relacionado inexistente.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Horario de clase actualizado correctamente.', [
            'horario' => $this->schedules->formatSchedule($schedule),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->schedules->deleteSchedule($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Horario no encontrado.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Horario de clase eliminado correctamente.');
    }

    public function teacherSchedules(Request $request, int $id): JsonResponse
    {
        $user = $request->attributes->get('usuario_autenticado');

        if ($user?->rol?->nombre === 'docente' && (int) $user?->docente?->id !== $id) {
            return ApiResponse::error('No tienes permisos para consultar horarios de otro docente.', [], 403);
        }

        try {
            $schedules = $this->schedules->schedulesByTeacher($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Docente no encontrado.', [], 404);
        }

        return ApiResponse::success('Horarios del docente obtenidos correctamente.', [
            'horarios' => $schedules->map(fn (HorarioClaseModel $schedule) => $this->schedules->formatSchedule($schedule))->values(),
        ]);
    }

    public function studentSchedules(Request $request, int $id): JsonResponse
    {
        $user = $request->attributes->get('usuario_autenticado');

        if ($user?->rol?->nombre === 'alumno' && (int) $user?->alumno?->id !== $id) {
            return ApiResponse::error('No tienes permisos para consultar horarios de otro alumno.', [], 403);
        }

        try {
            $schedules = $this->schedules->schedulesByStudent($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Alumno no encontrado.', [], 404);
        }

        return ApiResponse::success('Horarios del alumno obtenidos correctamente.', [
            'horarios' => $schedules->map(fn (HorarioClaseModel $schedule) => $this->schedules->formatSchedule($schedule))->values(),
        ]);
    }

    private function scheduleRules(): array
    {
        return [
            'gestion_academica_id' => ['required', 'integer', 'exists:gestion_academica,id'],
            'grupo_id' => ['required', 'integer', 'exists:grupo,id'],
            'materia_id' => ['required', 'integer', 'exists:materia,id'],
            'aula_id' => ['required', 'integer', 'exists:aula,id'],
            'dia_id' => ['required', 'integer', 'exists:dia,id'],
            'turno_id' => ['required', 'integer', 'exists:turno,id'],
            'periodo_id' => ['required', 'integer', 'exists:periodo,id'],
            'docente_id' => ['required', 'integer', 'exists:docente,id'],
            'activo' => ['nullable', 'boolean'],
        ];
    }

    private function listRules(): array
    {
        return [
            'gestion_academica_id' => ['nullable', 'integer', 'exists:gestion_academica,id'],
            'grupo_id' => ['nullable', 'integer', 'exists:grupo,id'],
            'materia_id' => ['nullable', 'integer', 'exists:materia,id'],
            'aula_id' => ['nullable', 'integer', 'exists:aula,id'],
            'dia_id' => ['nullable', 'integer', 'exists:dia,id'],
            'turno_id' => ['nullable', 'integer', 'exists:turno,id'],
            'periodo_id' => ['nullable', 'integer', 'exists:periodo,id'],
            'docente_id' => ['nullable', 'integer', 'exists:docente,id'],
            'activo' => ['nullable', Rule::in(['true', 'false', '1', '0'])],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    private function updateRules(): array
    {
        return [
            'gestion_academica_id' => ['sometimes', 'integer', 'exists:gestion_academica,id'],
            'grupo_id' => ['sometimes', 'integer', 'exists:grupo,id'],
            'materia_id' => ['sometimes', 'integer', 'exists:materia,id'],
            'aula_id' => ['sometimes', 'integer', 'exists:aula,id'],
            'dia_id' => ['sometimes', 'integer', 'exists:dia,id'],
            'turno_id' => ['sometimes', 'integer', 'exists:turno,id'],
            'periodo_id' => ['sometimes', 'integer', 'exists:periodo,id'],
            'docente_id' => ['sometimes', 'integer', 'exists:docente,id'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    private function messages(): array
    {
        return [
            'gestion_academica_id.required' => 'La gestion academica es obligatoria.',
            'grupo_id.required' => 'El grupo es obligatorio.',
            'materia_id.required' => 'La materia es obligatoria.',
            'aula_id.required' => 'El aula es obligatoria.',
            'dia_id.required' => 'El dia es obligatorio.',
            'turno_id.required' => 'El turno es obligatorio.',
            'periodo_id.required' => 'El periodo es obligatorio.',
            'docente_id.required' => 'El docente es obligatorio.',
            '*.exists' => 'Uno de los recursos indicados no existe.',
        ];
    }
}

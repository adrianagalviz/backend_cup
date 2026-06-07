<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Services\Academic\TeacherAssignmentService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

class TeacherAssignmentController extends Controller
{
    public function __construct(
        private readonly TeacherAssignmentService $assignments,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'docente_id' => ['nullable', 'integer', 'exists:docente,id'],
            'materia_id' => ['nullable', 'integer', 'exists:materia,id'],
            'grupo_id' => ['nullable', 'integer', 'exists:grupo,id'],
            'gestion_academica_id' => ['nullable', 'integer', 'exists:gestion_academica,id'],
            'activo' => ['nullable', Rule::in(['true', 'false', '1', '0'])],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $assignments = $this->assignments->listAssignments($validator->validated());

        return response()->json([
            'ok' => true,
            'mensaje' => 'Asignaciones docentes obtenidas correctamente.',
            'datos' => collect($assignments->items())
                ->map(fn ($assignment) => $this->assignments->formatAssignment($assignment))
                ->values(),
            'meta' => [
                'pagina_actual' => $assignments->currentPage(),
                'por_pagina' => $assignments->perPage(),
                'total' => $assignments->total(),
                'ultima_pagina' => $assignments->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'docente_id' => ['required', 'integer', 'exists:docente,id'],
            'materia_id' => ['required', 'integer', 'exists:materia,id'],
            'grupo_id' => ['required', 'integer', 'exists:grupo,id'],
            'gestion_academica_id' => ['required', 'integer', 'exists:gestion_academica,id'],
            'horario_clase_id' => ['required', 'integer', 'exists:horario_clase,id'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $assignment = $this->assignments->createAssignment($validator->validated());
        } catch (ModelNotFoundException) {
            return ApiResponse::error('No se encontro uno de los recursos requeridos para la asignacion.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Asignacion docente creada correctamente.', [
            'asignacion' => $this->assignments->formatAssignment($assignment),
        ], 201);
    }

    public function byTeacher(int $id): JsonResponse
    {
        try {
            $assignments = $this->assignments->byTeacher($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Docente no encontrado.', [], 404);
        }

        return ApiResponse::success('Asignaciones del docente obtenidas correctamente.', [
            'asignaciones' => $assignments->map(fn ($assignment) => $this->assignments->formatAssignment($assignment))->values(),
        ]);
    }

    public function byGroup(int $id): JsonResponse
    {
        try {
            $assignments = $this->assignments->byGroup($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Grupo no encontrado.', [], 404);
        }

        return ApiResponse::success('Asignaciones del grupo obtenidas correctamente.', [
            'asignaciones' => $assignments->map(fn ($assignment) => $this->assignments->formatAssignment($assignment))->values(),
        ]);
    }

    public function bySubject(int $id): JsonResponse
    {
        try {
            $assignments = $this->assignments->bySubject($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Materia no encontrada.', [], 404);
        }

        return ApiResponse::success('Asignaciones de la materia obtenidas correctamente.', [
            'asignaciones' => $assignments->map(fn ($assignment) => $this->assignments->formatAssignment($assignment))->values(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $assignment = $this->assignments->deactivateAssignment($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Asignacion no encontrada.', [], 404);
        }

        return ApiResponse::success('Asignacion docente desactivada correctamente.', [
            'asignacion' => $this->assignments->formatAssignment($assignment),
        ]);
    }

    private function messages(): array
    {
        return [
            'docente_id.required' => 'El docente es obligatorio.',
            'docente_id.exists' => 'El docente indicado no existe.',
            'materia_id.required' => 'La materia es obligatoria.',
            'materia_id.exists' => 'La materia indicada no existe.',
            'grupo_id.required' => 'El grupo es obligatorio.',
            'grupo_id.exists' => 'El grupo indicado no existe.',
            'gestion_academica_id.required' => 'La gestion academica es obligatoria.',
            'gestion_academica_id.exists' => 'La gestion academica indicada no existe.',
            'horario_clase_id.required' => 'El horario de clase es obligatorio.',
            'horario_clase_id.exists' => 'El horario de clase indicado no existe.',
        ];
    }
}

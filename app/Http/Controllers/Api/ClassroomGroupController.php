<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Models\AulaModel;
use App\Models\GrupoModel;
use App\Services\Academic\ClassroomGroupService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

class ClassroomGroupController extends Controller
{
    public function __construct(
        private readonly ClassroomGroupService $classrooms,
    ) {
    }

    public function subjects(): JsonResponse
    {
        $subjects = $this->classrooms->listSubjects();

        return ApiResponse::success('Materias obtenidas correctamente.', [
            'materias' => $subjects->map(fn ($subject) => $this->classrooms->formatSubject($subject))->values(),
        ]);
    }

    public function createGroup(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'gestion_academica_id' => ['required', 'integer', 'exists:gestion_academica,id'],
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('grupo', 'nombre')->where(fn ($query) => $query
                    ->where('gestion_academica_id', $request->input('gestion_academica_id'))),
            ],
            'cupo_maximo' => ['nullable', 'integer', 'min:1', 'max:70'],
            'activo' => ['nullable', 'boolean'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $group = $this->classrooms->createGroup($validator->validated());

        return ApiResponse::success('Grupo creado correctamente.', [
            'grupo' => $this->classrooms->formatGroup($group),
        ], 201);
    }

    public function listGroups(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'gestion_academica_id' => ['nullable', 'integer', 'exists:gestion_academica,id'],
            'activo' => ['nullable', Rule::in(['true', 'false', '1', '0'])],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $groups = $this->classrooms->listGroups($validator->validated());

        return $this->paginatedResponse(
            'Grupos obtenidos correctamente.',
            $groups,
            fn (GrupoModel $group) => $this->classrooms->formatGroup($group)
        );
    }

    public function groupStudents(int $id): JsonResponse
    {
        try {
            $students = $this->classrooms->groupStudents($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Grupo no encontrado.', [], 404);
        }

        return ApiResponse::success('Alumnos del grupo obtenidos correctamente.', [
            'alumnos' => $students->map(fn ($student) => $this->classrooms->formatStudent($student))->values(),
        ]);
    }

    public function calculateGroups(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'gestion_academica_id' => ['required', 'integer', 'exists:gestion_academica,id'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        return ApiResponse::success('Cantidad de grupos necesarios calculada correctamente.', [
            'calculo' => $this->classrooms->calculateRequiredGroups((int) $validator->validated()['gestion_academica_id']),
        ]);
    }

    public function assignStudents(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'gestion_academica_id' => ['required', 'integer', 'exists:gestion_academica,id'],
            'grupo_id' => ['required', 'integer', 'exists:grupo,id'],
            'alumno_ids' => ['nullable', 'array'],
            'alumno_ids.*' => ['integer', 'exists:alumno,id'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $result = $this->classrooms->assignStudentsToGroup($validator->validated());
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Grupo no encontrado.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Alumnos asignados al grupo correctamente.', $result);
    }

    public function listClassrooms(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'activa' => ['nullable', Rule::in(['true', 'false', '1', '0'])],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $classrooms = $this->classrooms->listClassrooms($validator->validated());

        return $this->paginatedResponse(
            'Aulas obtenidas correctamente.',
            $classrooms,
            fn (AulaModel $classroom) => $this->classrooms->formatClassroom($classroom)
        );
    }

    public function createClassroom(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'aula' => ['required', 'string', 'max:100'],
            'activa' => ['nullable', 'boolean'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $classroom = $this->classrooms->createClassroom($validator->validated());
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Aula creada correctamente.', [
            'aula' => $this->classrooms->formatClassroom($classroom),
        ], 201);
    }

    public function updateClassroom(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'aula' => ['sometimes', 'string', 'max:100'],
            'activa' => ['sometimes', 'boolean'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $classroom = $this->classrooms->updateClassroom($id, $validator->validated());
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Aula no encontrada.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Aula actualizada correctamente.', [
            'aula' => $this->classrooms->formatClassroom($classroom),
        ]);
    }

    private function paginatedResponse(string $message, $paginator, callable $formatter): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'mensaje' => $message,
            'datos' => collect($paginator->items())->map($formatter)->values(),
            'meta' => [
                'pagina_actual' => $paginator->currentPage(),
                'por_pagina' => $paginator->perPage(),
                'total' => $paginator->total(),
                'ultima_pagina' => $paginator->lastPage(),
            ],
        ]);
    }

    private function messages(): array
    {
        return [
            'gestion_academica_id.required' => 'La gestion academica es obligatoria.',
            'gestion_academica_id.exists' => 'La gestion academica indicada no existe.',
            'nombre.required' => 'El nombre del grupo es obligatorio.',
            'nombre.unique' => 'Ya existe un grupo con ese nombre en la gestion indicada.',
            'cupo_maximo.max' => 'El cupo maximo del grupo no puede superar 70 alumnos.',
            'grupo_id.required' => 'El grupo es obligatorio.',
            'grupo_id.exists' => 'El grupo indicado no existe.',
            'alumno_ids.array' => 'Los alumnos deben enviarse como una lista.',
            'alumno_ids.*.exists' => 'Uno de los alumnos indicados no existe.',
            'aula.required' => 'El aula es obligatoria.',
        ];
    }
}

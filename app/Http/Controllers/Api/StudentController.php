<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Models\AlumnoModel;
use App\Services\Students\StudentManagementService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function __construct(
        private readonly StudentManagementService $students,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'gestion_academica_id' => ['nullable', 'integer', 'exists:gestion_academica,id'],
            'estado_academico' => ['nullable', Rule::in(['activo', 'aprobado', 'reprobado'])],
            'grupo_id' => ['nullable', 'integer', 'exists:grupo,id'],
            'buscar' => ['nullable', 'string', 'max:150'],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $students = $this->students->listStudents($validator->validated());

        return response()->json([
            'ok' => true,
            'mensaje' => 'Alumnos obtenidos correctamente.',
            'datos' => collect($students->items())
                ->map(fn (AlumnoModel $student) => $this->students->formatStudent($student))
                ->values(),
            'meta' => [
                'pagina_actual' => $students->currentPage(),
                'por_pagina' => $students->perPage(),
                'total' => $students->total(),
                'ultima_pagina' => $students->lastPage(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $student = $this->students->findStudent($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Alumno no encontrado.', [], 404);
        }

        return ApiResponse::success('Alumno obtenido correctamente.', [
            'alumno' => $this->students->formatStudent($student, true),
        ]);
    }

    private function messages(): array
    {
        return [
            'gestion_academica_id.exists' => 'La gestion academica indicada no existe.',
            'estado_academico.in' => 'El estado academico indicado no es valido.',
            'grupo_id.exists' => 'El grupo indicado no existe.',
        ];
    }
}

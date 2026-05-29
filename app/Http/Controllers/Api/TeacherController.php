<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Models\DocenteModel;
use App\Services\Teachers\TeacherManagementService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

class TeacherController extends Controller
{
    public function __construct(
        private readonly TeacherManagementService $teachers,
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->storeRules(), $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $teacher = $this->teachers->createTeacher(
                $validator->validated(),
                $request->attributes->get('usuario_autenticado')
            );
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Docente creado correctamente.', [
            'docente' => $this->teachers->formatTeacher($teacher, true),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'activo' => ['nullable', Rule::in(['true', 'false', '1', '0'])],
            'ci' => ['nullable', 'string', 'max:20'],
            'nombre' => ['nullable', 'string', 'max:100'],
            'buscar' => ['nullable', 'string', 'max:150'],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $teachers = $this->teachers->listTeachers($validator->validated());

        return response()->json([
            'ok' => true,
            'mensaje' => 'Docentes obtenidos correctamente.',
            'datos' => collect($teachers->items())
                ->map(fn (DocenteModel $teacher) => $this->teachers->formatTeacher($teacher))
                ->values(),
            'meta' => [
                'pagina_actual' => $teachers->currentPage(),
                'por_pagina' => $teachers->perPage(),
                'total' => $teachers->total(),
                'ultima_pagina' => $teachers->lastPage(),
            ],
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        return $this->index($request);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $teacher = $this->teachers->findTeacher($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Docente no encontrado.', [], 404);
        }

        return ApiResponse::success('Docente obtenido correctamente.', [
            'docente' => $this->teachers->formatTeacher($teacher, true),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->updateRules($id), $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $teacher = $this->teachers->updateTeacher($id, $validator->validated());
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Docente no encontrado.', [], 404);
        }

        return ApiResponse::success('Docente actualizado correctamente.', [
            'docente' => $this->teachers->formatTeacher($teacher, true),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $teacher = $this->teachers->deactivateTeacher($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Docente no encontrado.', [], 404);
        }

        return ApiResponse::success('Docente desactivado correctamente.', [
            'docente' => $this->teachers->formatTeacher($teacher, true),
        ]);
    }

    private function storeRules(): array
    {
        return [
            'cedula_identidad' => ['required', 'string', 'max:20', 'unique:persona,cedula_identidad'],
            'nombres' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['required', 'string', 'max:100'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'sexo' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'celular' => ['required', 'string', 'max:30'],
            'correo' => ['required', 'email', 'max:150', 'unique:persona,correo'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'es_profesional_area' => ['required', 'accepted'],
            'tiene_maestria' => ['required', 'accepted'],
            'tiene_diplomado_educacion_superior' => ['required', 'accepted'],
            'nombre_usuario' => ['nullable', 'string', 'max:100', 'unique:usuario,nombre_usuario'],
            'password' => ['nullable', 'string', 'min:8'],
            'correo_verificado' => ['nullable', 'boolean'],
        ];
    }

    private function updateRules(int $id): array
    {
        $teacher = DocenteModel::query()->find($id);
        $personaId = $teacher?->persona_id ?? 0;
        $usuarioId = $teacher?->usuario_id ?? 0;

        return [
            'cedula_identidad' => ['sometimes', 'string', 'max:20', Rule::unique('persona', 'cedula_identidad')->ignore($personaId)],
            'nombres' => ['sometimes', 'string', 'max:100'],
            'apellido_paterno' => ['sometimes', 'string', 'max:100'],
            'apellido_materno' => ['sometimes', 'string', 'max:100'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'sexo' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'celular' => ['sometimes', 'string', 'max:30'],
            'correo' => ['sometimes', 'email', 'max:150', Rule::unique('persona', 'correo')->ignore($personaId)],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'es_profesional_area' => ['sometimes', 'boolean'],
            'tiene_maestria' => ['sometimes', 'boolean'],
            'tiene_diplomado_educacion_superior' => ['sometimes', 'boolean'],
            'activo' => ['sometimes', 'boolean'],
            'nombre_usuario' => ['sometimes', 'string', 'max:100', Rule::unique('usuario', 'nombre_usuario')->ignore($usuarioId)],
            'password' => ['sometimes', 'string', 'min:8'],
            'correo_verificado' => ['sometimes', 'boolean'],
        ];
    }

    private function messages(): array
    {
        return [
            'cedula_identidad.required' => 'La cedula de identidad es obligatoria.',
            'cedula_identidad.unique' => 'La cedula de identidad ya esta registrada.',
            'nombres.required' => 'Los nombres son obligatorios.',
            'apellido_paterno.required' => 'El apellido paterno es obligatorio.',
            'apellido_materno.required' => 'El apellido materno es obligatorio.',
            'celular.required' => 'El celular es obligatorio.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo debe ser valido.',
            'correo.unique' => 'El correo ya esta registrado.',
            'es_profesional_area.required' => 'Debe indicar si el docente es profesional en el area.',
            'es_profesional_area.accepted' => 'El docente debe ser profesional en el area.',
            'tiene_maestria.required' => 'Debe indicar si el docente tiene maestria.',
            'tiene_maestria.accepted' => 'El docente debe tener maestria.',
            'tiene_diplomado_educacion_superior.required' => 'Debe indicar si el docente tiene diplomado en educacion superior.',
            'tiene_diplomado_educacion_superior.accepted' => 'El docente debe tener diplomado en educacion superior.',
            'nombre_usuario.unique' => 'El nombre de usuario ya esta registrado.',
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
        ];
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Models\UsuarioModel;
use App\Services\Users\UserManagementService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(
        private readonly UserManagementService $users,
    ) {
    }

    public function createAdministrator(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->administratorRules(), $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $creator = $request->attributes->get('usuario_autenticado');

        $admin = $this->users->createAdministrator($validator->validated(), $creator);

        return ApiResponse::success(
            'Administrador creado correctamente.',
            ['usuario' => $this->users->formatUser($admin)],
            201
        );
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'rol' => ['nullable', Rule::in(['administrador', 'docente', 'alumno'])],
            'estado' => ['nullable', Rule::in(['true', 'false', '1', '0'])],
            'buscar' => ['nullable', 'string'],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $usuarios = $this->users->listUsers($validator->validated());

        return response()->json([
            'ok' => true,
            'mensaje' => 'Usuarios obtenidos correctamente.',
            'datos' => collect($usuarios->items())->map(fn (UsuarioModel $user) => $this->users->formatUser($user))->values(),
            'meta' => [
                'pagina_actual' => $usuarios->currentPage(),
                'por_pagina' => $usuarios->perPage(),
                'total' => $usuarios->total(),
                'ultima_pagina' => $usuarios->lastPage(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $usuario = $this->users->findUser($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Usuario no encontrado.', [], 404);
        }

        return ApiResponse::success('Usuario obtenido correctamente.', [
            'usuario' => $this->users->formatUser($usuario),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->updateRules($id), $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $usuario = $this->users->updateUser($id, $validator->validated());
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Usuario no encontrado.', [], 404);
        }

        return ApiResponse::success('Usuario actualizado correctamente.', [
            'usuario' => $this->users->formatUser($usuario),
        ]);
    }

    public function status(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'activo' => ['required', 'boolean'],
        ], [
            'activo.required' => 'El estado activo es obligatorio.',
            'activo.boolean' => 'El estado activo debe ser verdadero o falso.',
        ]);

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $usuario = $this->users->updateStatus($id, (bool) $validator->validated()['activo']);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Usuario no encontrado.', [], 404);
        }

        return ApiResponse::success('Estado de usuario actualizado correctamente.', [
            'usuario' => $this->users->formatUser($usuario),
        ]);
    }

    private function administratorRules(): array
    {
        return [
            'cedula_identidad' => ['required', 'string', 'max:20', 'unique:persona,cedula_identidad'],
            'nombres' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'sexo' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'celular' => ['nullable', 'string', 'max:30'],
            'correo' => ['required', 'email', 'max:150', 'unique:persona,correo'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'nombre_usuario' => ['required', 'string', 'max:100', 'unique:usuario,nombre_usuario'],
            'password' => ['required', 'string', 'min:8'],
            'correo_verificado' => ['nullable', 'boolean'],
        ];
    }

    private function updateRules(int $id): array
    {
        $usuario = UsuarioModel::query()->find($id);
        $personaId = $usuario?->persona_id ?? 0;

        return [
            'cedula_identidad' => ['sometimes', 'string', 'max:20', Rule::unique('persona', 'cedula_identidad')->ignore($personaId)],
            'nombres' => ['sometimes', 'string', 'max:100'],
            'apellido_paterno' => ['sometimes', 'string', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'sexo' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'celular' => ['nullable', 'string', 'max:30'],
            'correo' => ['sometimes', 'email', 'max:150', Rule::unique('persona', 'correo')->ignore($personaId)],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'nombre_usuario' => ['sometimes', 'string', 'max:100', Rule::unique('usuario', 'nombre_usuario')->ignore($id)],
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
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo debe ser valido.',
            'correo.unique' => 'El correo ya esta registrado.',
            'nombre_usuario.required' => 'El nombre de usuario es obligatorio.',
            'nombre_usuario.unique' => 'El nombre de usuario ya esta registrado.',
            'password.required' => 'La contrasena es obligatoria.',
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
        ];
    }
}

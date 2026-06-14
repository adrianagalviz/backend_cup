<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Models\PermisoModel;
use App\Models\RolModel;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RolePermissionController extends Controller
{
    public function index(): JsonResponse
    {
        $permissions = PermisoModel::query()
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get();

        $roles = RolModel::query()
            ->with(['permisos' => fn ($query) => $query->orderBy('categoria')->orderBy('nombre')])
            ->withCount('usuarios')
            ->orderBy('id')
            ->get();

        $allPermissionIds = $permissions->pluck('id')->all();

        return ApiResponse::success('Roles y permisos obtenidos correctamente.', [
            'permisos' => $permissions->map(fn (PermisoModel $permission) => $this->formatPermission($permission))->values(),
            'roles' => $roles->map(fn (RolModel $role) => $this->formatRole($role, $allPermissionIds))->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->rules(), $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $data = $validator->validated();

        $role = DB::transaction(function () use ($data): RolModel {
            $role = RolModel::query()->create([
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? null,
                'activo' => $data['activo'] ?? true,
            ]);

            $role->permisos()->sync($data['permisos'] ?? []);

            return $role->load('permisos')->loadCount('usuarios');
        });

        return ApiResponse::success('Rol creado correctamente.', [
            'rol' => $this->formatRole($role),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $role = RolModel::query()->find($id);

        if (!$role) {
            return ApiResponse::error('Rol no encontrado.', [], 404);
        }

        $validator = Validator::make($request->all(), $this->rules($id), $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $data = $validator->validated();

        $role = DB::transaction(function () use ($role, $data): RolModel {
            $role->fill([
                'nombre' => $this->isBaseRole($role) ? $role->nombre : $data['nombre'],
                'descripcion' => $data['descripcion'] ?? null,
                'activo' => $data['activo'] ?? $role->activo,
            ])->save();

            $permissionIds = $role->nombre === 'administrador'
                ? PermisoModel::query()->where('activo', true)->pluck('id')->all()
                : ($data['permisos'] ?? []);

            $role->permisos()->sync($permissionIds);

            return $role->refresh()->load('permisos')->loadCount('usuarios');
        });

        return ApiResponse::success('Rol actualizado correctamente.', [
            'rol' => $this->formatRole($role),
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
            $role = RolModel::query()->findOrFail($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Rol no encontrado.', [], 404);
        }

        if ($role->nombre === 'administrador' && !$validator->validated()['activo']) {
            return ApiResponse::error('El rol administrador debe permanecer activo.', [], 422);
        }

        $role->update(['activo' => (bool) $validator->validated()['activo']]);

        return ApiResponse::success('Estado de rol actualizado correctamente.', [
            'rol' => $this->formatRole($role->refresh()->load('permisos')->loadCount('usuarios')),
        ]);
    }

    private function rules(?int $ignoreId = null): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'max:30',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('rol', 'nombre')->ignore($ignoreId),
            ],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['nullable', 'boolean'],
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['integer', Rule::exists('permiso', 'id')->where('activo', true)],
        ];
    }

    private function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.max' => 'El nombre del rol no debe superar 30 caracteres.',
            'nombre.regex' => 'El nombre del rol solo puede usar minusculas, numeros y guion bajo.',
            'nombre.unique' => 'Ya existe un rol con ese nombre.',
            'permisos.array' => 'Los permisos deben enviarse como lista.',
            'permisos.*.exists' => 'Uno de los permisos seleccionados no existe o esta inactivo.',
        ];
    }

    private function formatRole(RolModel $role, ?array $adminPermissionIds = null): array
    {
        $permissionIds = $role->nombre === 'administrador'
            ? ($adminPermissionIds ?? PermisoModel::query()->where('activo', true)->pluck('id')->all())
            : $role->permisos->pluck('id')->all();

        return [
            'id' => $role->id,
            'nombre' => $role->nombre,
            'descripcion' => $role->descripcion,
            'activo' => $role->activo,
            'base' => $this->isBaseRole($role),
            'usuarios_count' => $role->usuarios_count ?? null,
            'permisos_ids' => array_values($permissionIds),
            'permisos' => $role->nombre === 'administrador'
                ? []
                : $role->permisos->map(fn (PermisoModel $permission) => $this->formatPermission($permission))->values(),
        ];
    }

    private function formatPermission(PermisoModel $permission): array
    {
        return [
            'id' => $permission->id,
            'codigo' => $permission->codigo,
            'nombre' => $permission->nombre,
            'descripcion' => $permission->descripcion,
            'categoria' => $permission->categoria,
            'activo' => $permission->activo,
        ];
    }

    private function isBaseRole(RolModel $role): bool
    {
        return in_array($role->nombre, ['administrador', 'docente', 'alumno'], true);
    }
}

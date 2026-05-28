<?php

namespace App\Services\Users;

use App\Models\UsuarioModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class UserManagementService
{
    public function createAdministrator(array $data, UsuarioModel $creator): UsuarioModel
    {
        return DB::transaction(function () use ($data, $creator): UsuarioModel {
            $personaId = DB::table('persona')->insertGetId([
                'cedula_identidad' => $data['cedula_identidad'],
                'nombres' => $data['nombres'],
                'apellido_paterno' => $data['apellido_paterno'],
                'apellido_materno' => $data['apellido_materno'] ?? null,
                'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
                'sexo' => $data['sexo'] ?? null,
                'direccion' => $data['direccion'] ?? null,
                'telefono' => $data['telefono'] ?? null,
                'celular' => $data['celular'] ?? null,
                'correo' => $data['correo'],
                'ciudad' => $data['ciudad'] ?? null,
                'creado_en' => now(),
            ]);

            $adminRoleId = DB::table('rol')->where('nombre', 'administrador')->value('id');

            $usuarioId = DB::table('usuario')->insertGetId([
                'persona_id' => $personaId,
                'rol_id' => $adminRoleId,
                'nombre_usuario' => $data['nombre_usuario'],
                'correo_verificado' => (bool) ($data['correo_verificado'] ?? false),
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'activo' => true,
                'creado_por_usuario_id' => $creator->id,
                'creado_en' => now(),
            ]);

            DB::table('administrador')->insert([
                'persona_id' => $personaId,
                'usuario_id' => $usuarioId,
                'activo' => true,
                'creado_en' => now(),
            ]);

            return $this->findUser($usuarioId);
        });
    }

    public function listUsers(array $filters): LengthAwarePaginator
    {
        return UsuarioModel::query()
            ->with(['rol', 'persona', 'administrador', 'docente', 'alumno'])
            ->when($filters['rol'] ?? null, function (Builder $query, string $rol): void {
                $query->whereHas('rol', fn (Builder $roleQuery) => $roleQuery->where('nombre', $rol));
            })
            ->when(array_key_exists('estado', $filters), function (Builder $query) use ($filters): void {
                $query->where('activo', filter_var($filters['estado'], FILTER_VALIDATE_BOOLEAN));
            })
            ->when($filters['buscar'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('nombre_usuario', 'ILIKE', "%{$search}%")
                        ->orWhere('codigo_acceso', 'ILIKE', "%{$search}%")
                        ->orWhereHas('persona', function (Builder $personQuery) use ($search): void {
                            $personQuery->where('cedula_identidad', 'ILIKE', "%{$search}%")
                                ->orWhere('correo', 'ILIKE', "%{$search}%")
                                ->orWhere('nombres', 'ILIKE', "%{$search}%")
                                ->orWhere('apellido_paterno', 'ILIKE', "%{$search}%");
                        });
                });
            })
            ->orderBy('id')
            ->paginate((int) ($filters['por_pagina'] ?? 15));
    }

    public function findUser(int $id): UsuarioModel
    {
        return UsuarioModel::query()
            ->with(['rol', 'persona', 'administrador', 'docente', 'alumno'])
            ->findOrFail($id);
    }

    public function updateUser(int $id, array $data): UsuarioModel
    {
        return DB::transaction(function () use ($id, $data): UsuarioModel {
            $usuario = $this->findUser($id);

            $personData = array_intersect_key($data, array_flip([
                'cedula_identidad',
                'nombres',
                'apellido_paterno',
                'apellido_materno',
                'fecha_nacimiento',
                'sexo',
                'direccion',
                'telefono',
                'celular',
                'correo',
                'ciudad',
            ]));

            if ($personData !== []) {
                $personData['actualizado_en'] = now();
                DB::table('persona')->where('id', $usuario->persona_id)->update($personData);
            }

            $userData = array_intersect_key($data, array_flip([
                'nombre_usuario',
                'correo_verificado',
            ]));

            if (isset($data['password'])) {
                $userData['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            if ($userData !== []) {
                $userData['actualizado_en'] = now();
                DB::table('usuario')->where('id', $usuario->id)->update($userData);
            }

            return $this->findUser($id);
        });
    }

    public function updateStatus(int $id, bool $active): UsuarioModel
    {
        DB::table('usuario')
            ->where('id', $id)
            ->update([
                'activo' => $active,
                'actualizado_en' => now(),
            ]);

        return $this->findUser($id);
    }

    public function formatUser(UsuarioModel $usuario): array
    {
        return [
            'id' => $usuario->id,
            'nombre_usuario' => $usuario->nombre_usuario,
            'codigo_acceso' => $usuario->codigo_acceso,
            'correo_verificado' => $usuario->correo_verificado,
            'activo' => $usuario->activo,
            'rol' => [
                'id' => $usuario->rol?->id,
                'nombre' => $usuario->rol?->nombre,
            ],
            'persona' => [
                'id' => $usuario->persona?->id,
                'cedula_identidad' => $usuario->persona?->cedula_identidad,
                'nombres' => $usuario->persona?->nombres,
                'apellido_paterno' => $usuario->persona?->apellido_paterno,
                'apellido_materno' => $usuario->persona?->apellido_materno,
                'correo' => $usuario->persona?->correo,
                'celular' => $usuario->persona?->celular,
                'ciudad' => $usuario->persona?->ciudad,
            ],
        ];
    }
}

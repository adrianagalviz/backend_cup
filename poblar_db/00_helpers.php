<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

$helpers = new class {
    public function now(): Carbon
    {
        return now();
    }

    public function password(string $plain): string
    {
        return password_hash($plain, PASSWORD_DEFAULT);
    }

    public function id(string $table, string $column, mixed $value): int
    {
        $id = DB::table($table)->where($column, $value)->value('id');

        if (! $id) {
            throw new RuntimeException("No existe {$table}.{$column}={$value}");
        }

        return (int) $id;
    }

    public function optionalId(string $table, string $column, mixed $value): ?int
    {
        $id = DB::table($table)->where($column, $value)->value('id');

        return $id ? (int) $id : null;
    }

    public function person(array $data): int
    {
        $payload = [
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
            'ciudad' => $data['ciudad'] ?? 'Santa Cruz',
            'actualizado_en' => $this->now(),
        ];

        DB::table('persona')->updateOrInsert(
            ['cedula_identidad' => $data['cedula_identidad']],
            $payload + ['creado_en' => $this->now()]
        );

        return $this->id('persona', 'cedula_identidad', $data['cedula_identidad']);
    }

    public function user(array $data): int
    {
        $payload = [
            'persona_id' => $data['persona_id'],
            'rol_id' => $data['rol_id'],
            'nombre_usuario' => $data['nombre_usuario'],
            'codigo_acceso' => $data['codigo_acceso'] ?? null,
            'correo_verificado' => (bool) ($data['correo_verificado'] ?? true),
            'firebase_uid' => $data['firebase_uid'] ?? null,
            'password_hash' => array_key_exists('password', $data) ? $this->password($data['password']) : ($data['password_hash'] ?? null),
            'activo' => (bool) ($data['activo'] ?? true),
            'ultimo_inicio_sesion' => $data['ultimo_inicio_sesion'] ?? null,
            'creado_por_usuario_id' => $data['creado_por_usuario_id'] ?? null,
            'actualizado_en' => $this->now(),
        ];

        DB::table('usuario')->updateOrInsert(
            ['nombre_usuario' => $data['nombre_usuario']],
            $payload + ['creado_en' => $this->now()]
        );

        return $this->id('usuario', 'nombre_usuario', $data['nombre_usuario']);
    }

    public function studentCode(int $gestionId, string $ci): string
    {
        $gestion = DB::table('gestion_academica')->where('id', $gestionId)->first();
        $digits = preg_replace('/\D/', '', $ci);

        return (string) $gestion->anio.(string) $gestion->numero_gestion.$digits;
    }

    public function resetSequence(string $table): void
    {
        DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), COALESCE((SELECT MAX(id) FROM {$table}), 1))");
    }

    public function json(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
};

return [
    'h' => $helpers,
    'passwords' => [
        'admin' => 'admin12345',
        'docentes' => 'docente12345',
        'alumnos' => 'alumno12345',
    ],
];

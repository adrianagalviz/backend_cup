<?php

use Illuminate\Support\Facades\DB;

return function (array $ctx): void {
    $h = $ctx['h'];

    DB::table('rol')->upsert([
        ['id' => 1, 'nombre' => 'administrador', 'descripcion' => 'Administrador con acceso completo al sistema.', 'activo' => true],
        ['id' => 2, 'nombre' => 'docente', 'descripcion' => 'Docente con acceso limitado a carga horaria y asistencias.', 'activo' => true],
        ['id' => 3, 'nombre' => 'alumno', 'descripcion' => 'Alumno con acceso a perfil, horarios, asistencia y examenes.', 'activo' => true],
    ], ['id'], ['nombre', 'descripcion', 'activo']);
    $h->resetSequence('rol');

    $adminRoleId = $h->id('rol', 'nombre', 'administrador');

    $admins = [
        [
            'ci' => env('ADMIN_INITIAL_CI', '0000001'),
            'nombres' => env('ADMIN_INITIAL_NAMES', 'Administrador'),
            'apellido_paterno' => env('ADMIN_INITIAL_LASTNAME', 'Inicial'),
            'apellido_materno' => env('ADMIN_INITIAL_SECOND_LASTNAME', 'Sistema'),
            'correo' => env('ADMIN_INITIAL_EMAIL', 'admin@cupficct.local'),
            'usuario' => env('ADMIN_INITIAL_USERNAME', 'admin'),
            'password' => env('ADMIN_INITIAL_PASSWORD', $ctx['passwords']['admin']),
        ],
        [
            'ci' => '9000002',
            'nombres' => 'Mariana',
            'apellido_paterno' => 'Rojas',
            'apellido_materno' => 'Vargas',
            'correo' => 'mariana.rojas@cupficct.local',
            'usuario' => 'admin.academico',
            'password' => $ctx['passwords']['admin'],
        ],
        [
            'ci' => '9000003',
            'nombres' => 'Carlos',
            'apellido_paterno' => 'Paredes',
            'apellido_materno' => 'Mendoza',
            'correo' => 'carlos.paredes@cupficct.local',
            'usuario' => 'admin.reportes',
            'password' => $ctx['passwords']['admin'],
        ],
    ];

    $creatorUserId = null;

    foreach ($admins as $index => $admin) {
        $personaId = $h->person([
            'cedula_identidad' => $admin['ci'],
            'nombres' => $admin['nombres'],
            'apellido_paterno' => $admin['apellido_paterno'],
            'apellido_materno' => $admin['apellido_materno'],
            'correo' => $admin['correo'],
            'celular' => '70000'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
            'ciudad' => 'Santa Cruz',
        ]);

        $usuarioId = $h->user([
            'persona_id' => $personaId,
            'rol_id' => $adminRoleId,
            'nombre_usuario' => $admin['usuario'],
            'password' => $admin['password'],
            'correo_verificado' => true,
            'creado_por_usuario_id' => $creatorUserId,
        ]);

        $creatorUserId ??= $usuarioId;

        DB::table('administrador')->updateOrInsert(
            ['usuario_id' => $usuarioId],
            ['persona_id' => $personaId, 'activo' => true, 'creado_en' => $h->now()]
        );
    }
};

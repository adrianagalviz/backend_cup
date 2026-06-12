<?php

use Illuminate\Support\Facades\DB;

return function (array $ctx): void {
    $h = $ctx['h'];
    $gestionId = $h->id('gestion_academica', 'nombre', '2026-1');
    $studentRoleId = $h->id('rol', 'nombre', 'alumno');
    $adminId = $h->id('usuario', 'nombre_usuario', env('ADMIN_INITIAL_USERNAME', 'admin'));

    $students = [
        '1001001' => 'Grupo A',
        '1001002' => 'Grupo A',
        '1001003' => 'Grupo B',
        '1001004' => 'Grupo B',
        '1001005' => 'Grupo C',
        '1001006' => 'Grupo C',
    ];

    $groups = ['Grupo A', 'Grupo B', 'Grupo C'];

    for ($i = 1; $i <= 150; $i++) {
        $ci = '2000'.str_pad((string) $i, 3, '0', STR_PAD_LEFT);
        $students[$ci] = $groups[($i - 1) % count($groups)];
    }

    foreach ($students as $ci => $groupName) {
        $personaId = $h->id('persona', 'cedula_identidad', $ci);
        $postulante = DB::table('postulante')->where('persona_id', $personaId)->first();
        $code = $h->studentCode($gestionId, $ci);

        $usuarioId = $h->user([
            'persona_id' => $personaId,
            'rol_id' => $studentRoleId,
            'nombre_usuario' => 'alumno_'.$code,
            'codigo_acceso' => $code,
            'password' => $ctx['passwords']['alumnos'],
            'correo_verificado' => false,
            'creado_por_usuario_id' => $adminId,
        ]);

        DB::table('alumno')->updateOrInsert(
            ['postulante_id' => $postulante->id],
            [
                'persona_id' => $personaId,
                'usuario_id' => $usuarioId,
                'gestion_academica_id' => $gestionId,
                'codigo_alumno' => $code,
                'estado_academico' => 'activo',
                'creado_en' => $h->now(),
            ]
        );

        DB::table('postulante')
            ->where('id', $postulante->id)
            ->update([
                'estado_postulante' => 'habilitado_alumno',
                'estado_requisitos' => 'aprobado',
                'estado_pago' => 'pagado',
                'actualizado_en' => $h->now(),
            ]);

        $alumnoId = (int) DB::table('alumno')->where('postulante_id', $postulante->id)->value('id');

        DB::table('grupo_alumno')->updateOrInsert(
            ['grupo_id' => $h->id('grupo', 'nombre', $groupName), 'alumno_id' => $alumnoId],
            ['fecha_asignacion' => $h->now(), 'activo' => true]
        );
    }
};

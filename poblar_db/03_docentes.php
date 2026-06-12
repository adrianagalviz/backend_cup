<?php

use Illuminate\Support\Facades\DB;

return function (array $ctx): void {
    $h = $ctx['h'];
    $teacherRoleId = $h->id('rol', 'nombre', 'docente');
    $creatorId = $h->id('usuario', 'nombre_usuario', env('ADMIN_INITIAL_USERNAME', 'admin'));

    foreach ([
        ['ci' => '8100001', 'nombres' => 'Elena', 'ap' => 'Quiroga', 'am' => 'Salvatierra', 'materia' => 'Fisica'],
        ['ci' => '8100002', 'nombres' => 'Jorge', 'ap' => 'Menacho', 'am' => 'Rivero', 'materia' => 'Matematicas'],
        ['ci' => '8100003', 'nombres' => 'Patricia', 'ap' => 'Suarez', 'am' => 'Lopez', 'materia' => 'Computacion'],
        ['ci' => '8100004', 'nombres' => 'Roberto', 'ap' => 'Arias', 'am' => 'Campos', 'materia' => 'Ingles'],
    ] as $index => $teacher) {
        $slug = strtolower(str_replace(' ', '.', $teacher['nombres'].'.'.$teacher['ap']));
        $personaId = $h->person([
            'cedula_identidad' => $teacher['ci'],
            'nombres' => $teacher['nombres'],
            'apellido_paterno' => $teacher['ap'],
            'apellido_materno' => $teacher['am'],
            'fecha_nacimiento' => '198'.($index + 1).'-04-15',
            'sexo' => $index % 2 === 0 ? 'Femenino' : 'Masculino',
            'direccion' => 'Barrio Universitario, zona '.$teacher['materia'],
            'telefono' => '3344'.str_pad((string) $index, 4, '0', STR_PAD_LEFT),
            'celular' => '71000'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
            'correo' => $slug.'@cupficct.local',
            'ciudad' => 'Santa Cruz',
        ]);

        $usuarioId = $h->user([
            'persona_id' => $personaId,
            'rol_id' => $teacherRoleId,
            'nombre_usuario' => 'docente_'.$teacher['ci'],
            'password' => $ctx['passwords']['docentes'],
            'correo_verificado' => true,
            'creado_por_usuario_id' => $creatorId,
        ]);

        DB::table('docente')->updateOrInsert(
            ['persona_id' => $personaId],
            [
                'usuario_id' => $usuarioId,
                'es_profesional_area' => true,
                'tiene_maestria' => true,
                'tiene_diplomado_educacion_superior' => true,
                'contratado' => true,
                'activo' => true,
                'creado_en' => $h->now(),
                'actualizado_en' => $h->now(),
            ]
        );
    }
};

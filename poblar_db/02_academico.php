<?php

use Illuminate\Support\Facades\DB;

return function (array $ctx): void {
    $h = $ctx['h'];
    $subjects = [
        'F'."\u{00ED}".'sica',
        'Matem'."\u{00E1}".'ticas',
        'Computaci'."\u{00F3}".'n',
        'Ingl'."\u{00E9}".'s',
    ];

    DB::table('gestion_academica')->update(['activa' => false]);
    DB::table('gestion_academica')->updateOrInsert(
        ['anio' => 2026, 'numero_gestion' => 1],
        [
            'nombre' => '2026-1',
            'fecha_inicio' => '2026-02-03',
            'fecha_fin' => '2026-06-30',
            'activa' => true,
            'creado_en' => $h->now(),
        ]
    );

    $gestionId = $h->id('gestion_academica', 'nombre', '2026-1');

    foreach ([
        ['codigo' => 'INF', 'nombre' => 'Ingenieria Informatica', 'descripcion' => 'Carrera de Ingenieria Informatica.'],
        ['codigo' => 'SIS', 'nombre' => 'Ingenieria de Sistemas', 'descripcion' => 'Carrera de Ingenieria de Sistemas.'],
        ['codigo' => 'RED', 'nombre' => 'Ingenieria en Redes y Telecomunicaciones', 'descripcion' => 'Carrera de Redes y Telecomunicaciones.'],
        ['codigo' => 'ROB', 'nombre' => 'Ingenieria Robotica', 'descripcion' => 'Carrera de Robotica.'],
    ] as $career) {
        DB::table('carrera')->updateOrInsert(
            ['codigo' => $career['codigo']],
            $career + ['activa' => true, 'creado_en' => $h->now()]
        );
    }

    foreach ($subjects as $subject) {
        DB::table('materia')->updateOrInsert(
            ['nombre' => $subject],
            ['activa' => true, 'creado_en' => $h->now()]
        );
    }

    foreach ([
        'INF' => 35,
        'SIS' => 30,
        'RED' => 20,
        'ROB' => 15,
    ] as $careerCode => $quota) {
        DB::table('cupo_carrera')->updateOrInsert(
            [
                'carrera_id' => $h->id('carrera', 'codigo', $careerCode),
                'gestion_academica_id' => $gestionId,
            ],
            [
                'cantidad_cupos' => $quota,
                'creado_en' => $h->now(),
                'actualizado_en' => $h->now(),
            ]
        );
    }
};

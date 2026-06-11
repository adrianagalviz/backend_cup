<?php

use Illuminate\Support\Facades\DB;

return function (array $ctx): void {
    $h = $ctx['h'];
    $gestionId = $h->id('gestion_academica', 'nombre', '2026-1');

    foreach ([
        ['nombre' => 'Grupo A', 'cupo_maximo' => 70],
        ['nombre' => 'Grupo B', 'cupo_maximo' => 70],
        ['nombre' => 'Grupo C', 'cupo_maximo' => 60],
    ] as $group) {
        DB::table('grupo')->updateOrInsert(
            ['gestion_academica_id' => $gestionId, 'nombre' => $group['nombre']],
            [
                'cupo_maximo' => $group['cupo_maximo'],
                'activo' => true,
                'creado_en' => $h->now(),
            ]
        );
    }

    foreach ([
        'Modulo 1 - Aula 101',
        'Modulo 1 - Aula 102',
        'Modulo 2 - Laboratorio 201',
        'Modulo 2 - Laboratorio 202',
        'Modulo 3 - Aula 301',
    ] as $classroom) {
        DB::table('aula')->updateOrInsert(
            ['ubicacion' => $classroom],
            ['activa' => true, 'creado_en' => $h->now()]
        );
    }

    foreach ([
        ['nombre' => 'Lunes', 'orden' => 1],
        ['nombre' => 'Martes', 'orden' => 2],
        ['nombre' => 'Miercoles', 'orden' => 3],
        ['nombre' => 'Jueves', 'orden' => 4],
        ['nombre' => 'Viernes', 'orden' => 5],
        ['nombre' => 'Sabado', 'orden' => 6],
    ] as $day) {
        DB::table('dia')->updateOrInsert(
            ['nombre' => $day['nombre']],
            ['orden' => $day['orden'], 'activo' => true]
        );
    }

    foreach ([
        ['nombre' => 'Manana', 'hora_inicio' => '07:00', 'hora_fin' => '13:00'],
        ['nombre' => 'Tarde', 'hora_inicio' => '14:00', 'hora_fin' => '20:00'],
    ] as $shift) {
        DB::table('turno')->updateOrInsert(
            ['nombre' => $shift['nombre']],
            ['hora_inicio' => $shift['hora_inicio'], 'hora_fin' => $shift['hora_fin'], 'activo' => true]
        );

        $turnoId = $h->id('turno', 'nombre', $shift['nombre']);
        $periodStart = new DateTimeImmutable($shift['hora_inicio']);

        for ($number = 1; $number <= 4; $number++) {
            $start = $periodStart->modify('+'.(($number - 1) * 90).' minutes');
            $end = $start->modify('+90 minutes');

            DB::table('periodo')->updateOrInsert(
                ['turno_id' => $turnoId, 'numero_periodo' => $number],
                [
                    'hora_inicio' => $start->format('H:i'),
                    'hora_fin' => $end->format('H:i'),
                    'duracion_minutos' => 90,
                    'activo' => true,
                ]
            );
        }
    }
};

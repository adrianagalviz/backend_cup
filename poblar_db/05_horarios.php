<?php

use Illuminate\Support\Facades\DB;

return function (array $ctx): void {
    $h = $ctx['h'];
    $gestionId = $h->id('gestion_academica', 'nombre', '2026-1');
    $turnoMananaId = $h->id('turno', 'nombre', 'Manana');
    $turnoTardeId = $h->id('turno', 'nombre', 'Tarde');
    $fisica = 'F'."\u{00ED}".'sica';
    $matematicas = 'Matem'."\u{00E1}".'ticas';
    $computacion = 'Computaci'."\u{00F3}".'n';
    $ingles = 'Ingl'."\u{00E9}".'s';

    $docenteId = function (string $ci) use ($h): int {
        $personaId = $h->id('persona', 'cedula_identidad', $ci);

        return (int) DB::table('docente')->where('persona_id', $personaId)->value('id');
    };

    $periodoId = fn (int $turnoId, int $numero): int => (int) DB::table('periodo')
        ->where('turno_id', $turnoId)
        ->where('numero_periodo', $numero)
        ->value('id');

    $schedules = [
        ['Grupo A', $fisica, 'Modulo 1 - Aula 101', 'Lunes', $turnoMananaId, 1, $docenteId('8100001')],
        ['Grupo A', $matematicas, 'Modulo 1 - Aula 101', 'Lunes', $turnoMananaId, 2, $docenteId('8100002')],
        ['Grupo A', $computacion, 'Modulo 2 - Laboratorio 201', 'Martes', $turnoMananaId, 1, $docenteId('8100003')],
        ['Grupo A', $ingles, 'Modulo 1 - Aula 102', 'Martes', $turnoMananaId, 2, $docenteId('8100004')],
        ['Grupo B', $matematicas, 'Modulo 1 - Aula 102', 'Lunes', $turnoMananaId, 1, $docenteId('8100002')],
        ['Grupo B', $fisica, 'Modulo 2 - Laboratorio 202', 'Lunes', $turnoMananaId, 2, $docenteId('8100001')],
        ['Grupo B', $ingles, 'Modulo 1 - Aula 101', 'Miercoles', $turnoMananaId, 1, $docenteId('8100004')],
        ['Grupo B', $computacion, 'Modulo 2 - Laboratorio 201', 'Miercoles', $turnoMananaId, 2, $docenteId('8100003')],
        ['Grupo C', $fisica, 'Modulo 3 - Aula 301', 'Jueves', $turnoTardeId, 1, $docenteId('8100001')],
        ['Grupo C', $matematicas, 'Modulo 3 - Aula 301', 'Jueves', $turnoTardeId, 2, $docenteId('8100002')],
        ['Grupo C', $computacion, 'Modulo 2 - Laboratorio 202', 'Viernes', $turnoTardeId, 1, $docenteId('8100003')],
        ['Grupo C', $ingles, 'Modulo 1 - Aula 102', 'Viernes', $turnoTardeId, 2, $docenteId('8100004')],
    ];

    foreach ($schedules as [$grupo, $materia, $aula, $dia, $turnoId, $numeroPeriodo, $docente]) {
        $periodo = DB::table('periodo')->where('id', $periodoId($turnoId, $numeroPeriodo))->first();

        DB::table('horario_clase')->updateOrInsert(
            [
                'gestion_academica_id' => $gestionId,
                'grupo_id' => $h->id('grupo', 'nombre', $grupo),
                'materia_id' => $h->id('materia', 'nombre', $materia),
                'dia_id' => $h->id('dia', 'nombre', $dia),
                'periodo_id' => $periodo->id,
            ],
            [
                'aula_id' => $h->id('aula', 'ubicacion', $aula),
                'turno_id' => $turnoId,
                'hora_inicio' => $periodo->hora_inicio,
                'hora_fin' => $periodo->hora_fin,
                'activo' => true,
                'creado_en' => $h->now(),
            ]
        );

        $horarioId = (int) DB::table('horario_clase')
            ->where('gestion_academica_id', $gestionId)
            ->where('grupo_id', $h->id('grupo', 'nombre', $grupo))
            ->where('materia_id', $h->id('materia', 'nombre', $materia))
            ->where('dia_id', $h->id('dia', 'nombre', $dia))
            ->where('periodo_id', $periodo->id)
            ->value('id');

        DB::table('asignacion_docente')->updateOrInsert(
            [
                'docente_id' => $docente,
                'materia_id' => $h->id('materia', 'nombre', $materia),
                'grupo_id' => $h->id('grupo', 'nombre', $grupo),
                'horario_clase_id' => $horarioId,
            ],
            [
                'gestion_academica_id' => $gestionId,
                'activo' => true,
                'asignado_en' => $h->now(),
            ]
        );
    }
};

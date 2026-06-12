<?php

use Illuminate\Support\Facades\DB;

return function (array $ctx): void {
    $h = $ctx['h'];
    $gestionId = $h->id('gestion_academica', 'nombre', '2026-1');
    $adminId = $h->id('usuario', 'nombre_usuario', env('ADMIN_INITIAL_USERNAME', 'admin'));
    $fisica = 'F'."\u{00ED}".'sica';
    $matematicas = 'Matem'."\u{00E1}".'ticas';
    $computacion = 'Computaci'."\u{00F3}".'n';
    $ingles = 'Ingl'."\u{00E9}".'s';

    $percentages = [
        $fisica => 25.00,
        $matematicas => 30.00,
        $computacion => 30.00,
        $ingles => 15.00,
    ];

    for ($partial = 1; $partial <= 3; $partial++) {
        DB::table('examen')->updateOrInsert(
            ['gestion_academica_id' => $gestionId, 'numero_parcial' => $partial],
            [
                'titulo' => 'Parcial '.$partial.' CUP FICCT 2026-1',
                'descripcion' => 'Examen demo de admision para el parcial '.$partial.'.',
                'habilitado' => true,
                'fecha_inicio' => now()->addDays($partial),
                'fecha_fin' => now()->addDays($partial)->addHours(2),
                'creado_por_usuario_id' => $adminId,
                'creado_en' => $h->now(),
                'actualizado_en' => $h->now(),
            ]
        );

        $examId = (int) DB::table('examen')
            ->where('gestion_academica_id', $gestionId)
            ->where('numero_parcial', $partial)
            ->value('id');

        foreach ($percentages as $subject => $percentage) {
            $subjectId = $h->id('materia', 'nombre', $subject);

            DB::table('examen_materia_porcentaje')->updateOrInsert(
                ['examen_id' => $examId, 'materia_id' => $subjectId],
                ['porcentaje' => $percentage]
            );

            for ($questionNumber = 1; $questionNumber <= 10; $questionNumber++) {
                $statement = $questionNumber === 1
                    ? 'Pregunta demo de '.$subject.' para parcial '.$partial
                    : 'Pregunta demo '.$questionNumber.' de '.$subject.' para parcial '.$partial;

                DB::table('pregunta')->updateOrInsert(
                    [
                        'examen_id' => $examId,
                        'materia_id' => $subjectId,
                        'enunciado' => $statement,
                    ],
                    [
                        'tipo_pregunta' => 'seleccion_multiple',
                        'puntaje' => round($percentage / 10, 2),
                        'activa' => true,
                        'creado_en' => $h->now(),
                    ]
                );

                $questionId = (int) DB::table('pregunta')
                    ->where('examen_id', $examId)
                    ->where('materia_id', $subjectId)
                    ->where('enunciado', $statement)
                    ->value('id');

                foreach ([
                    1 => ['Respuesta correcta '.$questionNumber.' de '.$subject, true],
                    2 => ['Distractor A '.$questionNumber.' de '.$subject, false],
                    3 => ['Distractor B '.$questionNumber.' de '.$subject, false],
                    4 => ['Distractor C '.$questionNumber.' de '.$subject, false],
                ] as $order => [$text, $correct]) {
                    DB::table('opcion_pregunta')->updateOrInsert(
                        ['pregunta_id' => $questionId, 'orden' => $order],
                        ['texto_opcion' => $text, 'es_correcta' => $correct]
                    );
                }
            }
        }
    }
};

<?php

use Illuminate\Support\Facades\DB;

return function (array $ctx): void {
    $h = $ctx['h'];
    $gestionId = $h->id('gestion_academica', 'nombre', '2026-1');
    $fisica = 'F'."\u{00ED}".'sica';
    $matematicas = 'Matem'."\u{00E1}".'ticas';
    $computacion = 'Computaci'."\u{00F3}".'n';
    $ingles = 'Ingl'."\u{00E9}".'s';

    $partialNotes = [
        '1001001' => [78, 82, 85],
        '1001002' => [65, 70, 72],
        '1001003' => [58, 61, 63],
        '1001004' => [91, 88, 94],
        '1001005' => [45, 52, 49],
        '1001006' => [73, 68, 75],
    ];

    for ($i = 1; $i <= 150; $i++) {
        $ci = '2000'.str_pad((string) $i, 3, '0', STR_PAD_LEFT);
        $baseNote = 45 + (($i * 7) % 51);

        $partialNotes[$ci] = [
            $baseNote,
            min(100, max(0, $baseNote + (($i % 9) - 4))),
            min(100, max(0, $baseNote + ((($i * 3) % 11) - 5))),
        ];
    }

    foreach ($partialNotes as $ci => $notes) {
        $personaId = $h->id('persona', 'cedula_identidad', $ci);
        $alumno = DB::table('alumno')->where('persona_id', $personaId)->first();

        foreach ($notes as $index => $note) {
            $partial = $index + 1;
            $exam = DB::table('examen')
                ->where('gestion_academica_id', $gestionId)
                ->where('numero_parcial', $partial)
                ->first();

            DB::table('intento_examen')->updateOrInsert(
                ['alumno_id' => $alumno->id, 'examen_id' => $exam->id],
                [
                    'fecha_inicio' => now()->subDays(10 - $partial)->setTime(8, 0),
                    'fecha_fin' => now()->subDays(10 - $partial)->setTime(9, 20),
                    'estado' => 'finalizado',
                    'nota_total' => $note,
                    'creado_en' => $h->now(),
                ]
            );

            $attemptId = (int) DB::table('intento_examen')
                ->where('alumno_id', $alumno->id)
                ->where('examen_id', $exam->id)
                ->value('id');

            $questions = DB::table('pregunta')
                ->where('examen_id', $exam->id)
                ->orderBy('materia_id')
                ->orderBy('id')
                ->get();

            $correctAnswersTarget = (int) round($questions->count() * ($note / 100));
            $questionIndex = 0;

            foreach ($questions as $question) {
                $questionIndex++;
                $isCorrect = $questionIndex <= $correctAnswersTarget;

                $optionId = (int) DB::table('opcion_pregunta')
                    ->where('pregunta_id', $question->id)
                    ->where('es_correcta', $isCorrect)
                    ->orderBy('orden')
                    ->value('id');

                DB::table('respuesta_alumno')->updateOrInsert(
                    ['intento_examen_id' => $attemptId, 'pregunta_id' => $question->id],
                    [
                        'opcion_pregunta_id' => $optionId,
                        'es_correcta' => $isCorrect,
                        'respondido_en' => $h->now(),
                    ]
                );
            }

            foreach ([
                $fisica => 25.00,
                $matematicas => 30.00,
                $computacion => 30.00,
                $ingles => 15.00,
            ] as $subject => $percentage) {
                $subjectId = $h->id('materia', 'nombre', $subject);
                $delta = 0;
                if ($subject === $fisica) {
                    $delta = -2;
                } elseif ($subject === $matematicas) {
                    $delta = 1;
                } elseif ($subject === $computacion) {
                    $delta = 3;
                }
                $subjectNote = min(100, max(0, $note + $delta));

                DB::table('nota_examen_materia')->updateOrInsert(
                    ['intento_examen_id' => $attemptId, 'materia_id' => $subjectId],
                    [
                        'nota' => $subjectNote,
                        'porcentaje_aplicado' => $percentage,
                        'nota_ponderada' => round($subjectNote * ($percentage / 100), 2),
                    ]
                );
            }

            DB::table('nota_parcial')->updateOrInsert(
                ['intento_examen_id' => $attemptId],
                [
                    'alumno_id' => $alumno->id,
                    'examen_id' => $exam->id,
                    'numero_parcial' => $partial,
                    'nota' => $note,
                    'registrado_en' => $h->now(),
                ]
            );
        }

        $average = round(array_sum($notes) / 3, 2);
        $state = $average >= 60 ? 'aprobado' : 'reprobado';

        DB::table('promedio_final')->updateOrInsert(
            ['alumno_id' => $alumno->id, 'gestion_academica_id' => $gestionId],
            [
                'parcial_1' => $notes[0],
                'parcial_2' => $notes[1],
                'parcial_3' => $notes[2],
                'promedio' => $average,
                'estado_final' => $state,
                'calculado_en' => $h->now(),
            ]
        );

        DB::table('alumno')->where('id', $alumno->id)->update(['estado_academico' => $state]);

        $postulante = DB::table('postulante')->where('id', $alumno->postulante_id)->first();
        $postulacion = DB::table('postulacion')->where('postulante_id', $postulante->id)->first();

        DB::table('postulacion')
            ->where('postulante_id', $postulante->id)
            ->update([
                'carrera_asignada_id' => $state === 'aprobado' ? $postulacion->primera_carrera_id : null,
                'motivo_asignacion' => $state === 'aprobado' ? 'primera_opcion' : null,
                'promedio_final' => $average,
                'estado_final' => $state,
                'orden_prioridad' => $state === 'aprobado' ? 1 : null,
                'asignado_en' => $state === 'aprobado' ? $h->now() : null,
            ]);
    }
};

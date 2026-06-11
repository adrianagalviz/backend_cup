<?php

use Illuminate\Support\Facades\DB;

return function (array $ctx): void {
    $h = $ctx['h'];
    $adminId = $h->id('usuario', 'nombre_usuario', env('ADMIN_INITIAL_USERNAME', 'admin'));

    $schedules = DB::table('horario_clase')->orderBy('id')->get();

    foreach ($schedules as $index => $schedule) {
        $assignment = DB::table('asignacion_docente')
            ->where('horario_clase_id', $schedule->id)
            ->first();

        $date = now()->subDays(7 - ($index % 5))->toDateString();
        $start = $date.' '.$schedule->hora_inicio;
        $end = $date.' '.$schedule->hora_fin;

        DB::table('asistencia_docente')->updateOrInsert(
            ['docente_id' => $assignment->docente_id, 'horario_clase_id' => $schedule->id, 'fecha' => $date],
            [
                'hora_entrada' => $start,
                'hora_salida' => $end,
                'estado_entrada' => $index % 4 === 0 ? 'retraso' : 'presente',
                'estado_salida' => 'finalizado',
                'marcado_por_usuario_id' => $adminId,
                'observacion' => $index % 4 === 0 ? 'Registro demo con retraso.' : null,
                'creado_en' => $h->now(),
                'actualizado_en' => $h->now(),
            ]
        );

        $studentIds = DB::table('grupo_alumno')
            ->where('grupo_id', $schedule->grupo_id)
            ->where('activo', true)
            ->pluck('alumno_id');

        foreach ($studentIds as $offset => $studentId) {
            DB::table('asistencia_alumno')->updateOrInsert(
                ['alumno_id' => $studentId, 'horario_clase_id' => $schedule->id, 'fecha' => $date],
                [
                    'docente_id' => $assignment->docente_id,
                    'hora_marcada' => $start,
                    'estado_asistencia' => ($offset + $index) % 6 === 0 ? 'falta' : (($offset + $index) % 4 === 0 ? 'retraso' : 'presente'),
                    'registrado_por_usuario_id' => $adminId,
                    'observacion' => null,
                    'creado_en' => $h->now(),
                    'actualizado_en' => $h->now(),
                ]
            );
        }
    }

    foreach ([
        ['tipo' => 'postulantes', 'formato' => 'excel'],
        ['tipo' => 'promedios_generales', 'formato' => 'pdf'],
        ['tipo' => 'asistencia_docentes', 'formato' => 'excel'],
    ] as $report) {
        DB::table('reporte_generado')->updateOrInsert(
            ['usuario_id' => $adminId, 'tipo_reporte' => $report['tipo'], 'formato_exportacion' => $report['formato']],
            [
                'parametros' => $h->json(['gestion' => '2026-1', 'demo' => true]),
                'archivo_url' => '/storage/reports/demo_'.$report['tipo'].'.'.$report['formato'],
                'generado_en' => $h->now(),
            ]
        );
    }

    $reportId = (int) DB::table('reporte_generado')
        ->where('usuario_id', $adminId)
        ->where('tipo_reporte', 'postulantes')
        ->value('id');

    DB::table('comando_voz_reporte')->updateOrInsert(
        ['usuario_id' => $adminId, 'texto_detectado' => 'mostrar reporte de postulantes'],
        [
            'intencion_detectada' => 'postulantes',
            'reporte_generado_id' => $reportId,
            'creado_en' => $h->now(),
        ]
    );

    DB::table('carga_masiva')->updateOrInsert(
        ['usuario_id' => $adminId, 'nombre_archivo' => 'postulantes_demo.csv'],
        [
            'tipo_carga' => 'postulantes',
            'formato_archivo' => 'csv',
            'total_registros' => 10,
            'registros_exitosos' => 9,
            'registros_error' => 1,
            'estado' => 'con_errores',
            'creado_en' => $h->now(),
            'finalizado_en' => $h->now(),
        ]
    );

    $loadId = (int) DB::table('carga_masiva')
        ->where('usuario_id', $adminId)
        ->where('nombre_archivo', 'postulantes_demo.csv')
        ->value('id');

    foreach ([
        1 => ['estado' => 'exitoso', 'mensaje' => null, 'ci' => '1001001'],
        2 => ['estado' => 'exitoso', 'mensaje' => null, 'ci' => '1001002'],
        10 => ['estado' => 'error', 'mensaje' => 'Correo duplicado en archivo demo.', 'ci' => '1001010'],
    ] as $row => $detail) {
        DB::table('detalle_carga_masiva')->updateOrInsert(
            ['carga_masiva_id' => $loadId, 'numero_fila' => $row],
            [
                'estado' => $detail['estado'],
                'mensaje_error' => $detail['mensaje'],
                'datos_fila' => $h->json(['cedula_identidad' => $detail['ci']]),
            ]
        );
    }
};

<?php

use Illuminate\Support\Facades\DB;

return function (array $ctx): void {
    $h = $ctx['h'];
    $gestionId = $h->id('gestion_academica', 'nombre', '2026-1');
    $adminId = $h->id('usuario', 'nombre_usuario', env('ADMIN_INITIAL_USERNAME', 'admin'));

    $applicants = [
        ['ci' => '1001001', 'nombres' => 'Luis', 'ap' => 'Molina', 'am' => 'Roca', 'estado_req' => 'aprobado', 'estado_pago' => 'pagado', 'estado' => 'pagado', 'doc' => 'aprobado', 'pago' => 'pagado', 'p1' => 'INF', 'p2' => 'SIS'],
        ['ci' => '1001002', 'nombres' => 'Camila', 'ap' => 'Vargas', 'am' => 'Soto', 'estado_req' => 'aprobado', 'estado_pago' => 'pagado', 'estado' => 'pagado', 'doc' => 'aprobado', 'pago' => 'pagado', 'p1' => 'SIS', 'p2' => 'INF'],
        ['ci' => '1001003', 'nombres' => 'Mateo', 'ap' => 'Rivera', 'am' => 'Duran', 'estado_req' => 'aprobado', 'estado_pago' => 'pagado', 'estado' => 'pagado', 'doc' => 'aprobado', 'pago' => 'pagado', 'p1' => 'RED', 'p2' => 'SIS'],
        ['ci' => '1001004', 'nombres' => 'Valeria', 'ap' => 'Ortiz', 'am' => 'Cespedes', 'estado_req' => 'aprobado', 'estado_pago' => 'pagado', 'estado' => 'pagado', 'doc' => 'aprobado', 'pago' => 'pagado', 'p1' => 'ROB', 'p2' => 'INF'],
        ['ci' => '1001005', 'nombres' => 'Diego', 'ap' => 'Salazar', 'am' => 'Paz', 'estado_req' => 'aprobado', 'estado_pago' => 'pagado', 'estado' => 'pagado', 'doc' => 'aprobado', 'pago' => 'pagado', 'p1' => 'INF', 'p2' => 'RED'],
        ['ci' => '1001006', 'nombres' => 'Sofia', 'ap' => 'Ledezma', 'am' => 'Vaca', 'estado_req' => 'aprobado', 'estado_pago' => 'pagado', 'estado' => 'pagado', 'doc' => 'aprobado', 'pago' => 'pagado', 'p1' => 'SIS', 'p2' => 'ROB'],
        ['ci' => '1001007', 'nombres' => 'Gabriel', 'ap' => 'Flores', 'am' => 'Nunez', 'estado_req' => 'aprobado', 'estado_pago' => 'pendiente', 'estado' => 'pendiente_pago', 'doc' => 'aprobado', 'pago' => 'pendiente', 'p1' => 'RED', 'p2' => 'INF'],
        ['ci' => '1001008', 'nombres' => 'Luciana', 'ap' => 'Mercado', 'am' => 'Villarroel', 'estado_req' => 'pendiente', 'estado_pago' => 'pendiente', 'estado' => 'registrado', 'doc' => 'pendiente', 'pago' => null, 'p1' => 'ROB', 'p2' => 'SIS'],
        ['ci' => '1001009', 'nombres' => 'Pablo', 'ap' => 'Cuellar', 'am' => 'Aguirre', 'estado_req' => 'rechazado', 'estado_pago' => 'rechazado', 'estado' => 'rechazado', 'doc' => 'rechazado', 'pago' => 'rechazado', 'p1' => 'INF', 'p2' => 'ROB'],
        ['ci' => '1001010', 'nombres' => 'Daniela', 'ap' => 'Justiniano', 'am' => 'Mendez', 'estado_req' => 'aprobado', 'estado_pago' => 'pagado', 'estado' => 'pagado', 'doc' => 'aprobado', 'pago' => 'pagado', 'p1' => 'SIS', 'p2' => 'RED'],
    ];

    foreach ($applicants as $index => $applicant) {
        $personaId = $h->person([
            'cedula_identidad' => $applicant['ci'],
            'nombres' => $applicant['nombres'],
            'apellido_paterno' => $applicant['ap'],
            'apellido_materno' => $applicant['am'],
            'fecha_nacimiento' => '2007-'.str_pad((string) (($index % 9) + 1), 2, '0', STR_PAD_LEFT).'-12',
            'sexo' => $index % 2 === 0 ? 'Masculino' : 'Femenino',
            'direccion' => 'Zona demo postulante '.$applicant['ci'],
            'telefono' => null,
            'celular' => '72000'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
            'correo' => strtolower($applicant['nombres']).'.'.$applicant['ci'].'@postulante.cupficct.local',
            'ciudad' => 'Santa Cruz',
        ]);

        DB::table('postulante')->updateOrInsert(
            ['persona_id' => $personaId],
            [
                'gestion_academica_id' => $gestionId,
                'colegio_procedencia' => 'Colegio Demo FICCT '.(($index % 4) + 1),
                'estado_requisitos' => $applicant['estado_req'],
                'estado_pago' => $applicant['estado_pago'],
                'estado_postulante' => $applicant['estado'],
                'observacion' => $applicant['estado'] === 'rechazado' ? 'Registro demo rechazado por requisito observado.' : null,
                'creado_en' => $h->now(),
                'actualizado_en' => $h->now(),
            ]
        );

        $postulanteId = (int) DB::table('postulante')->where('persona_id', $personaId)->value('id');

        DB::table('postulacion')->updateOrInsert(
            ['postulante_id' => $postulanteId],
            [
                'primera_carrera_id' => $h->id('carrera', 'codigo', $applicant['p1']),
                'segunda_carrera_id' => $h->id('carrera', 'codigo', $applicant['p2']),
                'carrera_asignada_id' => null,
                'motivo_asignacion' => null,
                'promedio_final' => null,
                'estado_final' => null,
                'orden_prioridad' => null,
                'asignado_en' => null,
            ]
        );

        DB::table('documento_postulante')->updateOrInsert(
            ['postulante_id' => $postulanteId, 'tipo_documento' => 'titulo_bachiller'],
            [
                'cloudinary_public_id' => 'demo/titulo_bachiller/'.$applicant['ci'],
                'cloudinary_url' => '/poblar_db/img/titulo_bachiller_generico.jpg',
                'formato_archivo' => 'jpg',
                'subido_en' => $h->now(),
                'estado_revision' => $applicant['doc'],
                'observacion' => $applicant['doc'] === 'rechazado' ? 'Documento ilegible en datos demo.' : null,
            ]
        );

        if ($applicant['pago'] !== null) {
            DB::table('pago_stripe')->updateOrInsert(
                ['postulante_id' => $postulanteId],
                [
                    'stripe_payment_intent_id' => 'pi_demo_'.$applicant['ci'],
                    'stripe_checkout_session_id' => 'cs_demo_'.$applicant['ci'],
                    'monto' => 250.00,
                    'moneda' => 'BOB',
                    'estado_pago' => $applicant['pago'],
                    'fecha_pago' => $applicant['pago'] === 'pagado' ? $h->now() : null,
                    'respuesta_stripe' => $h->json(['demo' => true, 'ci' => $applicant['ci']]),
                    'validado_por_usuario_id' => $applicant['pago'] === 'pagado' ? $adminId : null,
                    'validado_en' => $applicant['pago'] === 'pagado' ? $h->now() : null,
                    'creado_en' => $h->now(),
                ]
            );
        }
    }
};

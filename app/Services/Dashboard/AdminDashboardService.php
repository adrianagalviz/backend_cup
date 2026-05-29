<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    public function summary(array $filters = []): array
    {
        return [
            'resumen_general' => [
                'total_inscritos' => $this->applicantsQuery($filters)->count(),
                'total_aprobados' => $this->finalAveragesQuery($filters, 'aprobado')->count(),
                'total_reprobados' => $this->finalAveragesQuery($filters, 'reprobado')->count(),
                'total_grupos_habilitados' => $this->groupsQuery($filters)->where('activo', true)->count(),
            ],
            'pagos' => $this->paymentIndicators($filters),
        ];
    }

    public function paymentIndicators(array $filters = []): array
    {
        $payments = DB::table('pago_stripe')
            ->join('postulante', 'postulante.id', '=', 'pago_stripe.postulante_id')
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('postulante.gestion_academica_id', (int) $id));

        $readyApplicants = DB::table('postulante')
            ->join('pago_stripe', 'pago_stripe.postulante_id', '=', 'postulante.id')
            ->leftJoin('alumno', 'alumno.postulante_id', '=', 'postulante.id')
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('postulante.gestion_academica_id', (int) $id))
            ->where('postulante.estado_requisitos', 'aprobado')
            ->where('pago_stripe.estado_pago', 'pagado')
            ->whereNotNull('pago_stripe.validado_por_usuario_id')
            ->whereNotNull('pago_stripe.validado_en')
            ->whereNull('alumno.id');

        return [
            'total_pagos_pendientes' => (clone $payments)->where('pago_stripe.estado_pago', 'pendiente')->count(),
            'total_pagos_validados' => (clone $payments)
                ->where('pago_stripe.estado_pago', 'pagado')
                ->whereNotNull('pago_stripe.validado_por_usuario_id')
                ->whereNotNull('pago_stripe.validado_en')
                ->count(),
            'total_pagos_fallidos' => (clone $payments)->where('pago_stripe.estado_pago', 'fallido')->count(),
            'total_postulantes_listos_para_convertirse_en_alumnos' => $readyApplicants->count(),
        ];
    }

    public function attendance(array $filters = []): array
    {
        $teacherAttendance = DB::table('asistencia_docente')
            ->join('horario_clase', 'horario_clase.id', '=', 'asistencia_docente.horario_clase_id')
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('horario_clase.gestion_academica_id', (int) $id));

        $studentAttendance = DB::table('asistencia_alumno')
            ->join('horario_clase', 'horario_clase.id', '=', 'asistencia_alumno.horario_clase_id')
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('horario_clase.gestion_academica_id', (int) $id));

        return [
            'total_asistencias_docentes' => (clone $teacherAttendance)->where('asistencia_docente.estado_entrada', 'presente')->count(),
            'total_faltas_docentes' => (clone $teacherAttendance)->where('asistencia_docente.estado_entrada', 'falta')->count(),
            'total_retrasos_docentes' => (clone $teacherAttendance)->where('asistencia_docente.estado_entrada', 'retraso')->count(),
            'total_asistencias_alumnos' => (clone $studentAttendance)->where('asistencia_alumno.estado_asistencia', 'presente')->count(),
            'total_faltas_alumnos' => (clone $studentAttendance)->where('asistencia_alumno.estado_asistencia', 'falta')->count(),
            'total_retrasos_alumnos' => (clone $studentAttendance)->where('asistencia_alumno.estado_asistencia', 'retraso')->count(),
        ];
    }

    public function quotas(array $filters = []): Collection
    {
        return DB::table('cupo_carrera')
            ->join('carrera', 'carrera.id', '=', 'cupo_carrera.carrera_id')
            ->join('gestion_academica', 'gestion_academica.id', '=', 'cupo_carrera.gestion_academica_id')
            ->leftJoin('postulacion', function ($join): void {
                $join->on('postulacion.carrera_asignada_id', '=', 'carrera.id')
                    ->where('postulacion.estado_final', 'aprobado');
            })
            ->leftJoin('postulante', function ($join): void {
                $join->on('postulante.id', '=', 'postulacion.postulante_id')
                    ->whereColumn('postulante.gestion_academica_id', 'cupo_carrera.gestion_academica_id');
            })
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('cupo_carrera.gestion_academica_id', (int) $id))
            ->groupBy(
                'cupo_carrera.id',
                'cupo_carrera.cantidad_cupos',
                'carrera.id',
                'carrera.nombre',
                'gestion_academica.id',
                'gestion_academica.anio',
                'gestion_academica.numero_gestion',
                'gestion_academica.nombre'
            )
            ->orderByDesc('gestion_academica.anio')
            ->orderByDesc('gestion_academica.numero_gestion')
            ->orderBy('carrera.nombre')
            ->selectRaw('
                cupo_carrera.id as cupo_carrera_id,
                cupo_carrera.cantidad_cupos,
                carrera.id as carrera_id,
                carrera.nombre as carrera,
                gestion_academica.id as gestion_academica_id,
                gestion_academica.anio,
                gestion_academica.numero_gestion,
                gestion_academica.nombre as gestion,
                COUNT(DISTINCT postulante.id) as cupos_ocupados
            ')
            ->get()
            ->map(function ($row): array {
                $total = (int) $row->cantidad_cupos;
                $occupied = (int) $row->cupos_ocupados;

                return [
                    'cupo_carrera_id' => (int) $row->cupo_carrera_id,
                    'carrera' => [
                        'id' => (int) $row->carrera_id,
                        'nombre' => $row->carrera,
                    ],
                    'gestion' => [
                        'id' => (int) $row->gestion_academica_id,
                        'anio' => (int) $row->anio,
                        'numero_gestion' => (int) $row->numero_gestion,
                        'nombre' => $row->gestion,
                    ],
                    'cupos_por_carrera' => $total,
                    'cupos_ocupados' => $occupied,
                    'cupos_disponibles' => max(0, $total - $occupied),
                ];
            });
    }

    public function exams(array $filters = []): array
    {
        $exams = DB::table('examen')
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('gestion_academica_id', (int) $id));

        $enabledExamIds = (clone $exams)
            ->where('habilitado', true)
            ->pluck('id');

        $students = DB::table('alumno')
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('gestion_academica_id', (int) $id));

        $studentsWhoTookExam = DB::table('intento_examen')
            ->join('examen', 'examen.id', '=', 'intento_examen.examen_id')
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('examen.gestion_academica_id', (int) $id))
            ->distinct('intento_examen.alumno_id')
            ->count('intento_examen.alumno_id');

        $studentsPending = (clone $students)
            ->when($enabledExamIds->isNotEmpty(), function ($query) use ($enabledExamIds): void {
                $query->whereNotExists(function ($subquery) use ($enabledExamIds): void {
                    $subquery->select(DB::raw(1))
                        ->from('intento_examen')
                        ->whereColumn('intento_examen.alumno_id', 'alumno.id')
                        ->whereIn('intento_examen.examen_id', $enabledExamIds);
                });
            })
            ->count();

        if ($enabledExamIds->isEmpty()) {
            $studentsPending = 0;
        }

        return [
            'examenes_creados' => (clone $exams)->count(),
            'examenes_habilitados' => (clone $exams)->where('habilitado', true)->count(),
            'alumnos_que_rindieron' => $studentsWhoTookExam,
            'alumnos_pendientes' => $studentsPending,
        ];
    }

    private function applicantsQuery(array $filters)
    {
        return DB::table('postulante')
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('gestion_academica_id', (int) $id));
    }

    private function finalAveragesQuery(array $filters, string $status)
    {
        return DB::table('promedio_final')
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('gestion_academica_id', (int) $id))
            ->where('estado_final', $status);
    }

    private function groupsQuery(array $filters)
    {
        return DB::table('grupo')
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('gestion_academica_id', (int) $id));
    }
}

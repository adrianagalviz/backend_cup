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
            'postulantes_por_estado' => $this->applicantsByStatus($filters),
            'resultados' => $this->finalResults($filters),
        ];
    }

    public function paymentIndicators(array $filters = []): array
    {
        $payments = DB::table('pago_stripe')
            ->join('postulante', 'postulante.id', '=', 'pago_stripe.postulante_id')
            ->leftJoin('postulacion', 'postulacion.postulante_id', '=', 'postulante.id')
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('postulante.gestion_academica_id', (int) $id))
            ->when($filters['carrera_id'] ?? null, function ($query, int|string $id): void {
                $query->where(function ($careerQuery) use ($id): void {
                    $careerQuery->where('postulacion.primera_carrera_id', (int) $id)
                        ->orWhere('postulacion.segunda_carrera_id', (int) $id)
                        ->orWhere('postulacion.carrera_asignada_id', (int) $id);
                });
            });
        $payments = $this->applyDateRange($payments, $filters, 'pago_stripe.creado_en');

        $readyApplicants = DB::table('postulante')
            ->join('pago_stripe', 'pago_stripe.postulante_id', '=', 'postulante.id')
            ->leftJoin('postulacion', 'postulacion.postulante_id', '=', 'postulante.id')
            ->leftJoin('alumno', 'alumno.postulante_id', '=', 'postulante.id')
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('postulante.gestion_academica_id', (int) $id))
            ->when($filters['carrera_id'] ?? null, function ($query, int|string $id): void {
                $query->where(function ($careerQuery) use ($id): void {
                    $careerQuery->where('postulacion.primera_carrera_id', (int) $id)
                        ->orWhere('postulacion.segunda_carrera_id', (int) $id)
                        ->orWhere('postulacion.carrera_asignada_id', (int) $id);
                });
            })
            ->where('postulante.estado_requisitos', 'aprobado')
            ->where('pago_stripe.estado_pago', 'pagado')
            ->whereNotNull('pago_stripe.validado_por_usuario_id')
            ->whereNotNull('pago_stripe.validado_en')
            ->whereNull('alumno.id');
        $readyApplicants = $this->applyDateRange($readyApplicants, $filters, 'pago_stripe.creado_en');

        return [
            'total_pagos_pendientes' => (clone $payments)->where('pago_stripe.estado_pago', 'pendiente')->count(),
            'total_pagos_validados' => (clone $payments)
                ->where('pago_stripe.estado_pago', 'pagado')
                ->whereNotNull('pago_stripe.validado_por_usuario_id')
                ->whereNotNull('pago_stripe.validado_en')
                ->count(),
            'total_pagos_fallidos' => (clone $payments)->where('pago_stripe.estado_pago', 'fallido')->count(),
            'total_postulantes_listos_para_convertirse_en_alumnos' => $readyApplicants->count(),
            'distribucion' => [
                'pendiente' => (clone $payments)->where('pago_stripe.estado_pago', 'pendiente')->count(),
                'pagado' => (clone $payments)->where('pago_stripe.estado_pago', 'pagado')->count(),
                'rechazado' => (clone $payments)->where('pago_stripe.estado_pago', 'rechazado')->count(),
                'fallido' => (clone $payments)->where('pago_stripe.estado_pago', 'fallido')->count(),
            ],
        ];
    }

    public function attendance(array $filters = []): array
    {
        $teacherAttendance = DB::table('asistencia_docente')
            ->join('horario_clase', 'horario_clase.id', '=', 'asistencia_docente.horario_clase_id')
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('horario_clase.gestion_academica_id', (int) $id));
        $teacherAttendance = $this->applyDateRange($teacherAttendance, $filters, 'asistencia_docente.fecha');

        $studentAttendance = DB::table('asistencia_alumno')
            ->join('horario_clase', 'horario_clase.id', '=', 'asistencia_alumno.horario_clase_id')
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('horario_clase.gestion_academica_id', (int) $id));
        $studentAttendance = $this->applyDateRange($studentAttendance, $filters, 'asistencia_alumno.fecha');

        return [
            'total_asistencias_docentes' => (clone $teacherAttendance)->where('asistencia_docente.estado_entrada', 'presente')->count(),
            'total_faltas_docentes' => (clone $teacherAttendance)->where('asistencia_docente.estado_entrada', 'falta')->count(),
            'total_retrasos_docentes' => (clone $teacherAttendance)->where('asistencia_docente.estado_entrada', 'retraso')->count(),
            'total_asistencias_alumnos' => (clone $studentAttendance)->where('asistencia_alumno.estado_asistencia', 'presente')->count(),
            'total_faltas_alumnos' => (clone $studentAttendance)->where('asistencia_alumno.estado_asistencia', 'falta')->count(),
            'total_retrasos_alumnos' => (clone $studentAttendance)->where('asistencia_alumno.estado_asistencia', 'retraso')->count(),
            'docentes_por_estado' => [
                'presente' => (clone $teacherAttendance)->where('asistencia_docente.estado_entrada', 'presente')->count(),
                'retraso' => (clone $teacherAttendance)->where('asistencia_docente.estado_entrada', 'retraso')->count(),
                'falta' => (clone $teacherAttendance)->where('asistencia_docente.estado_entrada', 'falta')->count(),
            ],
            'alumnos_por_estado' => [
                'presente' => (clone $studentAttendance)->where('asistencia_alumno.estado_asistencia', 'presente')->count(),
                'retraso' => (clone $studentAttendance)->where('asistencia_alumno.estado_asistencia', 'retraso')->count(),
                'falta' => (clone $studentAttendance)->where('asistencia_alumno.estado_asistencia', 'falta')->count(),
            ],
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
            ->when($filters['carrera_id'] ?? null, fn ($query, int|string $id) => $query->where('cupo_carrera.carrera_id', (int) $id))
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
        $exams = $this->applyDateRange($exams, $filters, 'creado_en');

        $enabledExamIds = (clone $exams)
            ->where('habilitado', true)
            ->pluck('id');

        $students = DB::table('alumno')
            ->leftJoin('postulacion', 'postulacion.postulante_id', '=', 'alumno.postulante_id')
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('alumno.gestion_academica_id', (int) $id))
            ->when($filters['carrera_id'] ?? null, fn ($query, int|string $id) => $query->where('postulacion.carrera_asignada_id', (int) $id));

        $studentsWhoTookExam = DB::table('intento_examen')
            ->join('examen', 'examen.id', '=', 'intento_examen.examen_id')
            ->join('alumno', 'alumno.id', '=', 'intento_examen.alumno_id')
            ->leftJoin('postulacion', 'postulacion.postulante_id', '=', 'alumno.postulante_id')
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('examen.gestion_academica_id', (int) $id))
            ->when($filters['carrera_id'] ?? null, fn ($query, int|string $id) => $query->where('postulacion.carrera_asignada_id', (int) $id))
            ->when($filters['fecha_desde'] ?? null, fn ($query, string $date) => $query->whereDate('intento_examen.creado_en', '>=', $date))
            ->when($filters['fecha_hasta'] ?? null, fn ($query, string $date) => $query->whereDate('intento_examen.creado_en', '<=', $date))
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
            'distribucion' => [
                'habilitados' => (clone $exams)->where('habilitado', true)->count(),
                'deshabilitados' => (clone $exams)->where('habilitado', false)->count(),
            ],
        ];
    }

    private function applicantsQuery(array $filters)
    {
        return DB::table('postulante')
            ->leftJoin('postulacion', 'postulacion.postulante_id', '=', 'postulante.id')
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('postulante.gestion_academica_id', (int) $id))
            ->when($filters['carrera_id'] ?? null, function ($query, int|string $id): void {
                $query->where(function ($careerQuery) use ($id): void {
                    $careerQuery->where('postulacion.primera_carrera_id', (int) $id)
                        ->orWhere('postulacion.segunda_carrera_id', (int) $id)
                        ->orWhere('postulacion.carrera_asignada_id', (int) $id);
                });
            })
            ->when($filters['fecha_desde'] ?? null, fn ($query, string $date) => $query->whereDate('postulante.creado_en', '>=', $date))
            ->when($filters['fecha_hasta'] ?? null, fn ($query, string $date) => $query->whereDate('postulante.creado_en', '<=', $date));
    }

    private function finalAveragesQuery(array $filters, string $status)
    {
        return DB::table('promedio_final')
            ->join('alumno', 'alumno.id', '=', 'promedio_final.alumno_id')
            ->leftJoin('postulacion', 'postulacion.postulante_id', '=', 'alumno.postulante_id')
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('promedio_final.gestion_academica_id', (int) $id))
            ->when($filters['carrera_id'] ?? null, fn ($query, int|string $id) => $query->where('postulacion.carrera_asignada_id', (int) $id))
            ->when($filters['fecha_desde'] ?? null, fn ($query, string $date) => $query->whereDate('promedio_final.calculado_en', '>=', $date))
            ->when($filters['fecha_hasta'] ?? null, fn ($query, string $date) => $query->whereDate('promedio_final.calculado_en', '<=', $date))
            ->where('promedio_final.estado_final', $status);
    }

    private function groupsQuery(array $filters)
    {
        return DB::table('grupo')
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('gestion_academica_id', (int) $id));
    }

    private function applicantsByStatus(array $filters): array
    {
        return $this->applicantsQuery($filters)
            ->selectRaw('postulante.estado_postulante, COUNT(DISTINCT postulante.id) as total')
            ->groupBy('postulante.estado_postulante')
            ->pluck('total', 'estado_postulante')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    private function finalResults(array $filters): array
    {
        $approved = $this->finalAveragesQuery($filters, 'aprobado')->count();
        $failed = $this->finalAveragesQuery($filters, 'reprobado')->count();
        $total = $approved + $failed;

        return [
            'aprobados' => $approved,
            'reprobados' => $failed,
            'total' => $total,
            'porcentaje_aprobacion' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
            'porcentaje_reprobacion' => $total > 0 ? round(($failed / $total) * 100, 2) : 0,
        ];
    }

    private function applyDateRange($query, array $filters, string $column)
    {
        return $query
            ->when($filters['fecha_desde'] ?? null, fn ($innerQuery, string $date) => $innerQuery->whereDate($column, '>=', $date))
            ->when($filters['fecha_hasta'] ?? null, fn ($innerQuery, string $date) => $innerQuery->whereDate($column, '<=', $date));
    }
}

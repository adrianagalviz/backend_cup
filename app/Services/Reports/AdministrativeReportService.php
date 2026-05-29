<?php

namespace App\Services\Reports;

use App\Models\GrupoModel;
use App\Models\PostulanteModel;
use App\Models\PromedioFinalModel;
use App\Models\ReporteGeneradoModel;
use App\Models\UsuarioModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdministrativeReportService
{
    public function applicants(array $filters): LengthAwarePaginator
    {
        return PostulanteModel::query()
            ->with([
                'persona',
                'gestionAcademica',
                'postulacion.primeraCarrera',
                'postulacion.segundaCarrera',
                'postulacion.carreraAsignada',
            ])
            ->when($filters['gestion_academica_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('gestion_academica_id', (int) $id))
            ->when($filters['estado_postulante'] ?? null, fn (Builder $query, string $state) => $query->where('estado_postulante', $state))
            ->orderByDesc('id')
            ->paginate((int) ($filters['por_pagina'] ?? 15));
    }

    public function approved(array $filters): LengthAwarePaginator
    {
        $filters['estado_final'] = 'aprobado';

        return $this->averages($filters);
    }

    public function failed(array $filters): LengthAwarePaginator
    {
        $filters['estado_final'] = 'reprobado';

        return $this->averages($filters);
    }

    public function averages(array $filters): LengthAwarePaginator
    {
        return PromedioFinalModel::query()
            ->with([
                'alumno.persona',
                'alumno.gestionAcademica',
                'alumno.postulante.postulacion.primeraCarrera',
                'alumno.postulante.postulacion.segundaCarrera',
                'alumno.postulante.postulacion.carreraAsignada',
                'gestionAcademica',
            ])
            ->when($filters['gestion_academica_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('gestion_academica_id', (int) $id))
            ->when($filters['estado_final'] ?? null, fn (Builder $query, string $state) => $query->where('estado_final', $state))
            ->orderByDesc('promedio')
            ->orderBy('alumno_id')
            ->paginate((int) ($filters['por_pagina'] ?? 15));
    }

    public function groups(array $filters): LengthAwarePaginator
    {
        return GrupoModel::query()
            ->with(['gestionAcademica'])
            ->withCount([
                'alumnos as cantidad_alumnos' => fn (Builder $query) => $query->where('activo', true),
            ])
            ->when($filters['gestion_academica_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('gestion_academica_id', (int) $id))
            ->when(array_key_exists('activo', $filters), function (Builder $query) use ($filters): void {
                $query->where('activo', filter_var($filters['activo'], FILTER_VALIDATE_BOOLEAN));
            })
            ->orderByDesc('gestion_academica_id')
            ->orderBy('nombre')
            ->paginate((int) ($filters['por_pagina'] ?? 15));
    }

    public function subjectStatistics(array $filters): Collection
    {
        return DB::table('nota_examen_materia')
            ->join('materia', 'materia.id', '=', 'nota_examen_materia.materia_id')
            ->join('intento_examen', 'intento_examen.id', '=', 'nota_examen_materia.intento_examen_id')
            ->join('examen', 'examen.id', '=', 'intento_examen.examen_id')
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('examen.gestion_academica_id', (int) $id))
            ->groupBy('materia.id', 'materia.nombre')
            ->orderBy('materia.nombre')
            ->selectRaw('
                materia.id as materia_id,
                materia.nombre as materia,
                COUNT(*) as cantidad_notas,
                ROUND(AVG(nota_examen_materia.nota), 2) as promedio_materia,
                ROUND(AVG(nota_examen_materia.nota_ponderada), 2) as promedio_ponderado,
                MIN(nota_examen_materia.nota) as nota_minima,
                MAX(nota_examen_materia.nota) as nota_maxima,
                SUM(CASE WHEN nota_examen_materia.nota >= 60 THEN 1 ELSE 0 END) as cantidad_aprobados,
                SUM(CASE WHEN nota_examen_materia.nota < 60 THEN 1 ELSE 0 END) as cantidad_reprobados
            ')
            ->get()
            ->map(fn ($row): array => [
                'materia_id' => (int) $row->materia_id,
                'materia' => $row->materia,
                'cantidad_notas' => (int) $row->cantidad_notas,
                'promedio_materia' => $row->promedio_materia,
                'promedio_ponderado' => $row->promedio_ponderado,
                'nota_minima' => $row->nota_minima,
                'nota_maxima' => $row->nota_maxima,
                'cantidad_aprobados' => (int) $row->cantidad_aprobados,
                'cantidad_reprobados' => (int) $row->cantidad_reprobados,
            ]);
    }

    public function teachersGroups(array $filters): Collection
    {
        return DB::table('asignacion_docente')
            ->join('docente', 'docente.id', '=', 'asignacion_docente.docente_id')
            ->join('persona', 'persona.id', '=', 'docente.persona_id')
            ->join('materia', 'materia.id', '=', 'asignacion_docente.materia_id')
            ->join('grupo', 'grupo.id', '=', 'asignacion_docente.grupo_id')
            ->leftJoin('horario_clase', 'horario_clase.id', '=', 'asignacion_docente.horario_clase_id')
            ->leftJoin('dia', 'dia.id', '=', 'horario_clase.dia_id')
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('asignacion_docente.gestion_academica_id', (int) $id))
            ->when($filters['docente_id'] ?? null, fn ($query, int|string $id) => $query->where('asignacion_docente.docente_id', (int) $id))
            ->where('asignacion_docente.activo', true)
            ->orderBy('grupo.nombre')
            ->orderBy('persona.apellido_paterno')
            ->orderBy('materia.nombre')
            ->select([
                'asignacion_docente.id',
                'asignacion_docente.gestion_academica_id',
                'docente.id as docente_id',
                'persona.nombres',
                'persona.apellido_paterno',
                'persona.apellido_materno',
                'materia.id as materia_id',
                'materia.nombre as materia',
                'grupo.id as grupo_id',
                'grupo.nombre as grupo',
                'horario_clase.id as horario_clase_id',
                'dia.nombre as dia',
                'horario_clase.hora_inicio',
                'horario_clase.hora_fin',
            ])
            ->get()
            ->map(fn ($row): array => [
                'asignacion_id' => (int) $row->id,
                'gestion_academica_id' => (int) $row->gestion_academica_id,
                'docente' => [
                    'id' => (int) $row->docente_id,
                    'nombres' => $row->nombres,
                    'apellido_paterno' => $row->apellido_paterno,
                    'apellido_materno' => $row->apellido_materno,
                ],
                'materia' => [
                    'id' => (int) $row->materia_id,
                    'nombre' => $row->materia,
                ],
                'grupo' => [
                    'id' => (int) $row->grupo_id,
                    'nombre' => $row->grupo,
                ],
                'horario' => [
                    'id' => $row->horario_clase_id ? (int) $row->horario_clase_id : null,
                    'dia' => $row->dia,
                    'hora_inicio' => $row->hora_inicio,
                    'hora_fin' => $row->hora_fin,
                ],
            ]);
    }

    public function groupsMostApproved(array $filters): Collection
    {
        return DB::table('grupo')
            ->leftJoin('grupo_alumno', function ($join): void {
                $join->on('grupo_alumno.grupo_id', '=', 'grupo.id')
                    ->where('grupo_alumno.activo', true);
            })
            ->leftJoin('promedio_final', function ($join): void {
                $join->on('promedio_final.alumno_id', '=', 'grupo_alumno.alumno_id')
                    ->whereColumn('promedio_final.gestion_academica_id', 'grupo.gestion_academica_id')
                    ->where('promedio_final.estado_final', 'aprobado');
            })
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('grupo.gestion_academica_id', (int) $id))
            ->groupBy('grupo.id', 'grupo.nombre', 'grupo.gestion_academica_id', 'grupo.cupo_maximo', 'grupo.activo')
            ->orderByDesc('cantidad_aprobados')
            ->orderBy('grupo.nombre')
            ->selectRaw('
                grupo.id as grupo_id,
                grupo.nombre as grupo,
                grupo.gestion_academica_id,
                grupo.cupo_maximo,
                grupo.activo,
                COUNT(DISTINCT grupo_alumno.alumno_id) as cantidad_alumnos,
                COUNT(DISTINCT promedio_final.alumno_id) as cantidad_aprobados
            ')
            ->get()
            ->map(fn ($row): array => [
                'grupo_id' => (int) $row->grupo_id,
                'grupo' => $row->grupo,
                'gestion_academica_id' => (int) $row->gestion_academica_id,
                'cupo_maximo' => (int) $row->cupo_maximo,
                'activo' => (bool) $row->activo,
                'cantidad_alumnos' => (int) $row->cantidad_alumnos,
                'cantidad_aprobados' => (int) $row->cantidad_aprobados,
            ]);
    }

    public function teacherAttendance(array $filters): Collection
    {
        return DB::table('asistencia_docente')
            ->join('docente', 'docente.id', '=', 'asistencia_docente.docente_id')
            ->join('persona', 'persona.id', '=', 'docente.persona_id')
            ->join('horario_clase', 'horario_clase.id', '=', 'asistencia_docente.horario_clase_id')
            ->join('grupo', 'grupo.id', '=', 'horario_clase.grupo_id')
            ->join('materia', 'materia.id', '=', 'horario_clase.materia_id')
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('horario_clase.gestion_academica_id', (int) $id))
            ->when($filters['docente_id'] ?? null, fn ($query, int|string $id) => $query->where('asistencia_docente.docente_id', (int) $id))
            ->when($filters['fecha_desde'] ?? null, fn ($query, string $date) => $query->where('asistencia_docente.fecha', '>=', $date))
            ->when($filters['fecha_hasta'] ?? null, fn ($query, string $date) => $query->where('asistencia_docente.fecha', '<=', $date))
            ->groupBy('docente.id', 'persona.nombres', 'persona.apellido_paterno', 'persona.apellido_materno')
            ->orderBy('persona.apellido_paterno')
            ->selectRaw("
                docente.id as docente_id,
                persona.nombres,
                persona.apellido_paterno,
                persona.apellido_materno,
                COUNT(*) as total_registros,
                SUM(CASE WHEN asistencia_docente.estado_entrada = 'presente' THEN 1 ELSE 0 END) as presentes,
                SUM(CASE WHEN asistencia_docente.estado_entrada = 'retraso' THEN 1 ELSE 0 END) as retrasos,
                SUM(CASE WHEN asistencia_docente.estado_entrada = 'falta' THEN 1 ELSE 0 END) as faltas,
                SUM(CASE WHEN asistencia_docente.estado_entrada = 'pendiente' THEN 1 ELSE 0 END) as pendientes
            ")
            ->get()
            ->map(fn ($row): array => [
                'docente' => [
                    'id' => (int) $row->docente_id,
                    'nombres' => $row->nombres,
                    'apellido_paterno' => $row->apellido_paterno,
                    'apellido_materno' => $row->apellido_materno,
                ],
                'total_registros' => (int) $row->total_registros,
                'presentes' => (int) $row->presentes,
                'retrasos' => (int) $row->retrasos,
                'faltas' => (int) $row->faltas,
                'pendientes' => (int) $row->pendientes,
            ]);
    }

    public function studentAttendance(array $filters): Collection
    {
        return DB::table('asistencia_alumno')
            ->join('alumno', 'alumno.id', '=', 'asistencia_alumno.alumno_id')
            ->join('persona', 'persona.id', '=', 'alumno.persona_id')
            ->join('horario_clase', 'horario_clase.id', '=', 'asistencia_alumno.horario_clase_id')
            ->when($filters['gestion_academica_id'] ?? null, fn ($query, int|string $id) => $query->where('horario_clase.gestion_academica_id', (int) $id))
            ->when($filters['alumno_id'] ?? null, fn ($query, int|string $id) => $query->where('asistencia_alumno.alumno_id', (int) $id))
            ->when($filters['grupo_id'] ?? null, fn ($query, int|string $id) => $query->where('horario_clase.grupo_id', (int) $id))
            ->when($filters['fecha_desde'] ?? null, fn ($query, string $date) => $query->where('asistencia_alumno.fecha', '>=', $date))
            ->when($filters['fecha_hasta'] ?? null, fn ($query, string $date) => $query->where('asistencia_alumno.fecha', '<=', $date))
            ->groupBy('alumno.id', 'alumno.codigo_alumno', 'persona.nombres', 'persona.apellido_paterno', 'persona.apellido_materno')
            ->orderBy('persona.apellido_paterno')
            ->selectRaw("
                alumno.id as alumno_id,
                alumno.codigo_alumno,
                persona.nombres,
                persona.apellido_paterno,
                persona.apellido_materno,
                COUNT(*) as total_registros,
                SUM(CASE WHEN asistencia_alumno.estado_asistencia = 'presente' THEN 1 ELSE 0 END) as presentes,
                SUM(CASE WHEN asistencia_alumno.estado_asistencia = 'retraso' THEN 1 ELSE 0 END) as retrasos,
                SUM(CASE WHEN asistencia_alumno.estado_asistencia = 'falta' THEN 1 ELSE 0 END) as faltas,
                SUM(CASE WHEN asistencia_alumno.estado_asistencia = 'pendiente' THEN 1 ELSE 0 END) as pendientes
            ")
            ->get()
            ->map(fn ($row): array => [
                'alumno' => [
                    'id' => (int) $row->alumno_id,
                    'codigo_alumno' => $row->codigo_alumno,
                    'nombres' => $row->nombres,
                    'apellido_paterno' => $row->apellido_paterno,
                    'apellido_materno' => $row->apellido_materno,
                ],
                'total_registros' => (int) $row->total_registros,
                'presentes' => (int) $row->presentes,
                'retrasos' => (int) $row->retrasos,
                'faltas' => (int) $row->faltas,
                'pendientes' => (int) $row->pendientes,
            ]);
    }

    public function registerGeneratedReport(UsuarioModel $user, string $type, array $parameters = [], ?string $format = null, ?string $fileUrl = null): ReporteGeneradoModel
    {
        $id = DB::table('reporte_generado')->insertGetId([
            'usuario_id' => $user->id,
            'tipo_reporte' => $type,
            'formato_exportacion' => $format,
            'parametros' => $parameters === [] ? null : json_encode($parameters),
            'archivo_url' => $fileUrl,
            'generado_en' => now(),
        ]);

        return ReporteGeneradoModel::query()->findOrFail($id);
    }

    public function reportHistory(array $filters): LengthAwarePaginator
    {
        return ReporteGeneradoModel::query()
            ->with('usuario.persona')
            ->when($filters['tipo_reporte'] ?? null, fn (Builder $query, string $type) => $query->where('tipo_reporte', $type))
            ->orderByDesc('generado_en')
            ->paginate((int) ($filters['por_pagina'] ?? 15));
    }

    public function formatGeneratedReport(ReporteGeneradoModel $report): array
    {
        return [
            'id' => $report->id,
            'tipo_reporte' => $report->tipo_reporte,
            'formato_exportacion' => $report->formato_exportacion,
            'parametros' => $report->parametros,
            'archivo_url' => $report->archivo_url,
            'generado_en' => $report->generado_en,
            'usuario' => [
                'id' => $report->usuario?->id,
                'nombre_usuario' => $report->usuario?->nombre_usuario,
                'nombres' => $report->usuario?->persona?->nombres,
                'apellido_paterno' => $report->usuario?->persona?->apellido_paterno,
            ],
        ];
    }

    public function formatApplicant(PostulanteModel $applicant): array
    {
        return [
            'postulante_id' => $applicant->id,
            'ci' => $applicant->persona?->cedula_identidad,
            'nombres' => $applicant->persona?->nombres,
            'apellido_paterno' => $applicant->persona?->apellido_paterno,
            'apellido_materno' => $applicant->persona?->apellido_materno,
            'correo' => $applicant->persona?->correo,
            'celular' => $applicant->persona?->celular,
            'estado' => $applicant->estado_postulante,
            'estado_requisitos' => $applicant->estado_requisitos,
            'estado_pago' => $applicant->estado_pago,
            'gestion' => $this->formatGestion($applicant->gestionAcademica),
            'primera_opcion' => $applicant->postulacion?->primeraCarrera?->nombre,
            'segunda_opcion' => $applicant->postulacion?->segundaCarrera?->nombre,
            'carrera_asignada' => $applicant->postulacion?->carreraAsignada?->nombre,
        ];
    }

    public function formatAverage(PromedioFinalModel $average): array
    {
        $student = $average->alumno;
        $application = $student?->postulante?->postulacion;
        $partialNotes = DB::table('nota_parcial')
            ->where('alumno_id', $average->alumno_id)
            ->orderBy('numero_parcial')
            ->get();

        return [
            'alumno_id' => $student?->id,
            'codigo_alumno' => $student?->codigo_alumno,
            'ci' => $student?->persona?->cedula_identidad,
            'nombres' => $student?->persona?->nombres,
            'apellido_paterno' => $student?->persona?->apellido_paterno,
            'apellido_materno' => $student?->persona?->apellido_materno,
            'gestion' => $this->formatGestion($average->gestionAcademica),
            'parcial_1' => $average->parcial_1,
            'parcial_2' => $average->parcial_2,
            'parcial_3' => $average->parcial_3,
            'notas_parciales' => $partialNotes->map(fn ($note): array => [
                'id' => $note->id,
                'examen_id' => $note->examen_id,
                'numero_parcial' => $note->numero_parcial,
                'nota' => $note->nota,
                'registrado_en' => $note->registrado_en,
            ])->values(),
            'promedio' => $average->promedio,
            'estado_final' => $average->estado_final,
            'primera_opcion' => $application?->primeraCarrera?->nombre,
            'segunda_opcion' => $application?->segundaCarrera?->nombre,
            'carrera_asignada' => $application?->carreraAsignada?->nombre,
            'calculado_en' => $average->calculado_en,
        ];
    }

    public function formatGroup(GrupoModel $group): array
    {
        return [
            'grupo_id' => $group->id,
            'nombre' => $group->nombre,
            'gestion' => $this->formatGestion($group->gestionAcademica),
            'cupo_maximo' => $group->cupo_maximo,
            'cantidad_alumnos' => (int) ($group->cantidad_alumnos ?? 0),
            'cupos_disponibles' => max(0, (int) $group->cupo_maximo - (int) ($group->cantidad_alumnos ?? 0)),
            'activo' => $group->activo,
            'creado_en' => $group->creado_en,
        ];
    }

    private function formatGestion($gestion): ?array
    {
        if (! $gestion) {
            return null;
        }

        return [
            'id' => $gestion->id,
            'anio' => $gestion->anio,
            'numero_gestion' => $gestion->numero_gestion,
            'nombre' => $gestion->nombre,
        ];
    }
}

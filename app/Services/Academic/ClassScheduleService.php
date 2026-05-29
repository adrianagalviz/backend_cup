<?php

namespace App\Services\Academic;

use App\Models\AlumnoModel;
use App\Models\AulaModel;
use App\Models\DiaModel;
use App\Models\DocenteModel;
use App\Models\GrupoModel;
use App\Models\HorarioClaseModel;
use App\Models\MateriaModel;
use App\Models\PeriodoModel;
use App\Models\TurnoModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ClassScheduleService
{
    public function createSchedule(array $data): HorarioClaseModel
    {
        return DB::transaction(function () use ($data): HorarioClaseModel {
            $group = GrupoModel::query()->findOrFail($data['grupo_id']);
            $teacher = DocenteModel::query()->findOrFail($data['docente_id']);
            $period = PeriodoModel::query()->findOrFail($data['periodo_id']);

            $this->validateEntities($data, $group, $teacher, $period);
            $this->validateScheduleConflicts($data);

            $scheduleId = DB::table('horario_clase')->insertGetId([
                'gestion_academica_id' => $data['gestion_academica_id'],
                'grupo_id' => $data['grupo_id'],
                'materia_id' => $data['materia_id'],
                'aula_id' => $data['aula_id'],
                'dia_id' => $data['dia_id'],
                'turno_id' => $data['turno_id'],
                'periodo_id' => $data['periodo_id'],
                'hora_inicio' => $period->hora_inicio,
                'hora_fin' => $period->hora_fin,
                'activo' => (bool) ($data['activo'] ?? true),
                'creado_en' => now(),
            ]);

            DB::table('asignacion_docente')->insert([
                'docente_id' => $teacher->id,
                'materia_id' => $data['materia_id'],
                'grupo_id' => $group->id,
                'horario_clase_id' => $scheduleId,
                'gestion_academica_id' => $data['gestion_academica_id'],
                'activo' => true,
                'asignado_en' => now(),
            ]);

            return $this->findSchedule($scheduleId);
        });
    }

    public function listSchedules(array $filters): LengthAwarePaginator
    {
        return $this->scheduleQuery()
            ->when($filters['gestion_academica_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('gestion_academica_id', (int) $id))
            ->when($filters['grupo_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('grupo_id', (int) $id))
            ->when($filters['materia_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('materia_id', (int) $id))
            ->when($filters['aula_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('aula_id', (int) $id))
            ->when($filters['dia_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('dia_id', (int) $id))
            ->when($filters['turno_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('turno_id', (int) $id))
            ->when($filters['periodo_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('periodo_id', (int) $id))
            ->when($filters['docente_id'] ?? null, function (Builder $query, int|string $id): void {
                $query->whereHas('asignacionesDocentes', fn (Builder $assignment) => $assignment
                    ->where('docente_id', (int) $id)
                    ->where('activo', true));
            })
            ->when(array_key_exists('activo', $filters), function (Builder $query) use ($filters): void {
                $query->where('activo', filter_var($filters['activo'], FILTER_VALIDATE_BOOLEAN));
            })
            ->orderByDesc('gestion_academica_id')
            ->orderBy('dia_id')
            ->orderBy('periodo_id')
            ->paginate((int) ($filters['por_pagina'] ?? 15));
    }

    public function schedulesByTeacher(int $teacherId): Collection
    {
        DocenteModel::query()->findOrFail($teacherId);

        return $this->scheduleQuery()
            ->whereHas('asignacionesDocentes', fn (Builder $query) => $query
                ->where('docente_id', $teacherId)
                ->where('activo', true))
            ->where('activo', true)
            ->orderBy('dia_id')
            ->orderBy('periodo_id')
            ->get();
    }

    public function schedulesByStudent(int $studentId): Collection
    {
        $student = AlumnoModel::query()->findOrFail($studentId);
        $groupIds = DB::table('grupo_alumno')
            ->where('alumno_id', $student->id)
            ->where('activo', true)
            ->pluck('grupo_id');

        return $this->scheduleQuery()
            ->whereIn('grupo_id', $groupIds)
            ->where('gestion_academica_id', $student->gestion_academica_id)
            ->where('activo', true)
            ->orderBy('dia_id')
            ->orderBy('periodo_id')
            ->get();
    }

    public function findSchedule(int $id): HorarioClaseModel
    {
        return $this->scheduleQuery()->findOrFail($id);
    }

    public function formatSchedule(HorarioClaseModel $schedule): array
    {
        $assignment = $schedule->asignacionesDocentes->first();

        return [
            'id' => $schedule->id,
            'hora_inicio' => $schedule->hora_inicio,
            'hora_fin' => $schedule->hora_fin,
            'activo' => $schedule->activo,
            'gestion_academica' => [
                'id' => $schedule->gestionAcademica?->id,
                'anio' => $schedule->gestionAcademica?->anio,
                'numero_gestion' => $schedule->gestionAcademica?->numero_gestion,
                'nombre' => $schedule->gestionAcademica?->nombre,
            ],
            'dia' => [
                'id' => $schedule->dia?->id,
                'nombre' => $schedule->dia?->nombre,
                'orden' => $schedule->dia?->orden,
            ],
            'turno' => [
                'id' => $schedule->turno?->id,
                'nombre' => $schedule->turno?->nombre,
            ],
            'periodo' => [
                'id' => $schedule->periodo?->id,
                'numero_periodo' => $schedule->periodo?->numero_periodo,
                'duracion_minutos' => $schedule->periodo?->duracion_minutos,
            ],
            'aula' => [
                'id' => $schedule->aula?->id,
                'ubicacion' => $schedule->aula?->ubicacion,
            ],
            'grupo' => [
                'id' => $schedule->grupo?->id,
                'nombre' => $schedule->grupo?->nombre,
            ],
            'materia' => [
                'id' => $schedule->materia?->id,
                'nombre' => $schedule->materia?->nombre,
            ],
            'docente' => $assignment ? [
                'id' => $assignment->docente?->id,
                'nombres' => $assignment->docente?->persona?->nombres,
                'apellido_paterno' => $assignment->docente?->persona?->apellido_paterno,
                'apellido_materno' => $assignment->docente?->persona?->apellido_materno,
            ] : null,
            'creado_en' => $schedule->creado_en,
        ];
    }

    private function scheduleQuery(): Builder
    {
        return HorarioClaseModel::query()
            ->with([
                'gestionAcademica',
                'grupo',
                'materia',
                'aula',
                'dia',
                'turno',
                'periodo',
                'asignacionesDocentes.docente.persona',
            ]);
    }

    private function validateEntities(array $data, GrupoModel $group, DocenteModel $teacher, PeriodoModel $period): void
    {
        if (! $teacher->activo || ! $teacher->contratado) {
            throw new RuntimeException('El docente debe estar activo y contratado.');
        }

        $this->ensureActive(DiaModel::query()->findOrFail($data['dia_id']), 'dia');
        $this->ensureActive(TurnoModel::query()->findOrFail($data['turno_id']), 'turno');
        $this->ensureActive($period, 'periodo');
        $this->ensureActive(AulaModel::query()->findOrFail($data['aula_id']), 'aula');
        $this->ensureActive($group, 'grupo');
        $this->ensureActive(MateriaModel::query()->findOrFail($data['materia_id']), 'materia');

        if ((int) $group->gestion_academica_id !== (int) $data['gestion_academica_id']) {
            throw new RuntimeException('El grupo no corresponde a la gestion academica indicada.');
        }

        if ((int) $period->turno_id !== (int) $data['turno_id']) {
            throw new RuntimeException('El periodo no corresponde al turno indicado.');
        }
    }

    private function validateScheduleConflicts(array $data): void
    {
        $base = DB::table('horario_clase')
            ->where('gestion_academica_id', $data['gestion_academica_id'])
            ->where('dia_id', $data['dia_id'])
            ->where('periodo_id', $data['periodo_id'])
            ->where('activo', true);

        if ((clone $base)->where('grupo_id', $data['grupo_id'])->exists()) {
            throw new RuntimeException('El grupo ya tiene clase en ese dia, periodo y gestion.');
        }

        if ((clone $base)->where('aula_id', $data['aula_id'])->exists()) {
            throw new RuntimeException('El aula ya esta ocupada en ese dia, periodo y gestion.');
        }

        $teacherConflict = DB::table('asignacion_docente')
            ->join('horario_clase', 'horario_clase.id', '=', 'asignacion_docente.horario_clase_id')
            ->where('asignacion_docente.docente_id', $data['docente_id'])
            ->where('asignacion_docente.activo', true)
            ->where('horario_clase.activo', true)
            ->where('horario_clase.gestion_academica_id', $data['gestion_academica_id'])
            ->where('horario_clase.dia_id', $data['dia_id'])
            ->where('horario_clase.periodo_id', $data['periodo_id'])
            ->exists();

        if ($teacherConflict) {
            throw new RuntimeException('El docente ya tiene clase en ese dia, periodo y gestion.');
        }
    }

    private function ensureActive(object $model, string $name): void
    {
        if (method_exists($model, 'getAttributes') && array_key_exists('activo', $model->getAttributes()) && ! $model->activo) {
            throw new RuntimeException("El {$name} indicado no esta activo.");
        }

        if (method_exists($model, 'getAttributes') && array_key_exists('activa', $model->getAttributes()) && ! $model->activa) {
            throw new RuntimeException("El {$name} indicado no esta activo.");
        }
    }
}

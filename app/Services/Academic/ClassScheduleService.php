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
            $this->validateScheduleConflicts($data, $period->hora_inicio, $period->hora_fin);

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

    public function updateSchedule(int $id, array $data): HorarioClaseModel
    {
        return DB::transaction(function () use ($id, $data): HorarioClaseModel {
            $schedule = $this->findSchedule($id);

            if (array_keys($data) === ['activo']) {
                DB::table('horario_clase')->where('id', $schedule->id)->update([
                    'activo' => (bool) $data['activo'],
                ]);

                DB::table('asignacion_docente')->where('horario_clase_id', $schedule->id)->update([
                    'activo' => (bool) $data['activo'],
                ]);

                return $this->findSchedule($schedule->id);
            }

            $merged = [
                'gestion_academica_id' => $data['gestion_academica_id'] ?? $schedule->gestion_academica_id,
                'grupo_id' => $data['grupo_id'] ?? $schedule->grupo_id,
                'materia_id' => $data['materia_id'] ?? $schedule->materia_id,
                'aula_id' => $data['aula_id'] ?? $schedule->aula_id,
                'dia_id' => $data['dia_id'] ?? $schedule->dia_id,
                'turno_id' => $data['turno_id'] ?? $schedule->turno_id,
                'periodo_id' => $data['periodo_id'] ?? $schedule->periodo_id,
                'docente_id' => $data['docente_id'] ?? $schedule->asignacionesDocentes->first()?->docente_id,
                'activo' => $data['activo'] ?? $schedule->activo,
            ];

            $group = GrupoModel::query()->findOrFail($merged['grupo_id']);
            $teacher = DocenteModel::query()->findOrFail($merged['docente_id']);
            $period = PeriodoModel::query()->findOrFail($merged['periodo_id']);

            $this->validateEntities($merged, $group, $teacher, $period);
            $this->validateScheduleConflicts($merged, $period->hora_inicio, $period->hora_fin, $schedule->id);

            DB::table('horario_clase')->where('id', $schedule->id)->update([
                'gestion_academica_id' => $merged['gestion_academica_id'],
                'grupo_id' => $merged['grupo_id'],
                'materia_id' => $merged['materia_id'],
                'aula_id' => $merged['aula_id'],
                'dia_id' => $merged['dia_id'],
                'turno_id' => $merged['turno_id'],
                'periodo_id' => $merged['periodo_id'],
                'hora_inicio' => $period->hora_inicio,
                'hora_fin' => $period->hora_fin,
                'activo' => (bool) $merged['activo'],
            ]);

            DB::table('asignacion_docente')->where('horario_clase_id', $schedule->id)->update([
                'docente_id' => $teacher->id,
                'materia_id' => $merged['materia_id'],
                'grupo_id' => $group->id,
                'gestion_academica_id' => $merged['gestion_academica_id'],
                'activo' => (bool) $merged['activo'],
            ]);

            if (! DB::table('asignacion_docente')->where('horario_clase_id', $schedule->id)->exists()) {
                DB::table('asignacion_docente')->insert([
                    'docente_id' => $teacher->id,
                    'materia_id' => $merged['materia_id'],
                    'grupo_id' => $group->id,
                    'horario_clase_id' => $schedule->id,
                    'gestion_academica_id' => $merged['gestion_academica_id'],
                    'activo' => (bool) $merged['activo'],
                    'asignado_en' => now(),
                ]);
            }

            return $this->findSchedule($schedule->id);
        });
    }

    public function deleteSchedule(int $id): void
    {
        $schedule = $this->findSchedule($id);

        DB::transaction(function () use ($schedule): void {
            DB::table('asistencia_alumno')->where('horario_clase_id', $schedule->id)->delete();
            DB::table('asistencia_docente')->where('horario_clase_id', $schedule->id)->delete();
            DB::table('asignacion_docente')->where('horario_clase_id', $schedule->id)->delete();
            DB::table('horario_clase')->where('id', $schedule->id)->delete();
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

        $day = DiaModel::query()->findOrFail($data['dia_id']);
        $this->ensureActive($day, 'dia');
        $this->ensureActive(TurnoModel::query()->findOrFail($data['turno_id']), 'turno');
        $this->ensureActive($period, 'periodo');
        $this->ensureActive(AulaModel::query()->findOrFail($data['aula_id']), 'aula');
        $this->ensureActive($group, 'grupo');
        $this->ensureActive(MateriaModel::query()->findOrFail($data['materia_id']), 'materia');

        if ((int) $day->orden > 6) {
            throw new RuntimeException('Solo se pueden programar clases de lunes a sabado.');
        }

        if ((int) $group->gestion_academica_id !== (int) $data['gestion_academica_id']) {
            throw new RuntimeException('El grupo no corresponde a la gestion academica indicada.');
        }

        if ((int) $period->turno_id !== (int) $data['turno_id']) {
            throw new RuntimeException('El periodo no corresponde al turno indicado.');
        }

        if ((int) $period->duracion_minutos !== 90) {
            throw new RuntimeException('Cada clase debe usar un periodo de 90 minutos.');
        }
    }

    private function validateScheduleConflicts(array $data, string $start, string $end, ?int $ignoreScheduleId = null): void
    {
        $base = DB::table('horario_clase')
            ->where('gestion_academica_id', $data['gestion_academica_id'])
            ->where('dia_id', $data['dia_id'])
            ->where('activo', true)
            ->where('hora_inicio', '<', $end)
            ->where('hora_fin', '>', $start);

        if ($ignoreScheduleId) {
            $base->where('id', '<>', $ignoreScheduleId);
        }

        if ((clone $base)->where('grupo_id', $data['grupo_id'])->exists()) {
            throw new RuntimeException('El grupo ya tiene clase en ese dia y horario.');
        }

        if ((clone $base)->where('aula_id', $data['aula_id'])->exists()) {
            throw new RuntimeException('El aula ya esta ocupada en ese dia y horario.');
        }

        $teacherConflict = DB::table('asignacion_docente')
            ->join('horario_clase', 'horario_clase.id', '=', 'asignacion_docente.horario_clase_id')
            ->where('asignacion_docente.docente_id', $data['docente_id'])
            ->where('asignacion_docente.activo', true)
            ->where('horario_clase.activo', true)
            ->where('horario_clase.gestion_academica_id', $data['gestion_academica_id'])
            ->where('horario_clase.dia_id', $data['dia_id'])
            ->where('horario_clase.hora_inicio', '<', $end)
            ->where('horario_clase.hora_fin', '>', $start)
            ->when($ignoreScheduleId, fn ($query) => $query->where('horario_clase.id', '<>', $ignoreScheduleId))
            ->exists();

        if ($teacherConflict) {
            throw new RuntimeException('El docente ya tiene clase en ese dia y horario.');
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

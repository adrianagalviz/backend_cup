<?php

namespace App\Services\Academic;

use App\Models\AsignacionDocenteModel;
use App\Models\DocenteModel;
use App\Models\GrupoModel;
use App\Models\HorarioClaseModel;
use App\Models\MateriaModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TeacherAssignmentService
{
    public function createAssignment(array $data): AsignacionDocenteModel
    {
        return DB::transaction(function () use ($data): AsignacionDocenteModel {
            $teacher = DocenteModel::query()->findOrFail($data['docente_id']);
            $subject = MateriaModel::query()->findOrFail($data['materia_id']);
            $group = GrupoModel::query()->findOrFail($data['grupo_id']);
            $schedule = HorarioClaseModel::query()->findOrFail($data['horario_clase_id']);

            $this->validateEntities($teacher, $subject, $group, $schedule, $data);
            $this->validateTeacherLimits($teacher->id, (int) $data['materia_id'], (int) $data['grupo_id']);

            $existing = AsignacionDocenteModel::query()
                ->where('docente_id', $teacher->id)
                ->where('materia_id', $data['materia_id'])
                ->where('grupo_id', $data['grupo_id'])
                ->where('horario_clase_id', $data['horario_clase_id'])
                ->first();

            if ($existing) {
                DB::table('asignacion_docente')
                    ->where('id', $existing->id)
                    ->update([
                        'gestion_academica_id' => $data['gestion_academica_id'],
                        'activo' => true,
                    ]);

                return $this->findAssignment($existing->id);
            }

            $assignmentId = DB::table('asignacion_docente')->insertGetId([
                'docente_id' => $teacher->id,
                'materia_id' => $data['materia_id'],
                'grupo_id' => $data['grupo_id'],
                'horario_clase_id' => $data['horario_clase_id'],
                'gestion_academica_id' => $data['gestion_academica_id'],
                'activo' => true,
                'asignado_en' => now(),
            ]);

            return $this->findAssignment($assignmentId);
        });
    }

    public function byTeacher(int $teacherId): Collection
    {
        DocenteModel::query()->findOrFail($teacherId);

        return $this->baseQuery()
            ->where('docente_id', $teacherId)
            ->orderByDesc('id')
            ->get();
    }

    public function byGroup(int $groupId): Collection
    {
        GrupoModel::query()->findOrFail($groupId);

        return $this->baseQuery()
            ->where('grupo_id', $groupId)
            ->orderByDesc('id')
            ->get();
    }

    public function bySubject(int $subjectId): Collection
    {
        MateriaModel::query()->findOrFail($subjectId);

        return $this->baseQuery()
            ->where('materia_id', $subjectId)
            ->orderByDesc('id')
            ->get();
    }

    public function deactivateAssignment(int $id): AsignacionDocenteModel
    {
        $assignment = $this->findAssignment($id);

        DB::table('asignacion_docente')
            ->where('id', $assignment->id)
            ->update(['activo' => false]);

        return $this->findAssignment($id);
    }

    public function findAssignment(int $id): AsignacionDocenteModel
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function formatAssignment(AsignacionDocenteModel $assignment): array
    {
        return [
            'id' => $assignment->id,
            'activo' => $assignment->activo,
            'asignado_en' => $assignment->asignado_en,
            'docente' => [
                'id' => $assignment->docente?->id,
                'nombres' => $assignment->docente?->persona?->nombres,
                'apellido_paterno' => $assignment->docente?->persona?->apellido_paterno,
                'apellido_materno' => $assignment->docente?->persona?->apellido_materno,
            ],
            'materia' => [
                'id' => $assignment->materia?->id,
                'nombre' => $assignment->materia?->nombre,
            ],
            'grupo' => [
                'id' => $assignment->grupo?->id,
                'nombre' => $assignment->grupo?->nombre,
            ],
            'gestion_academica' => [
                'id' => $assignment->gestionAcademica?->id,
                'anio' => $assignment->gestionAcademica?->anio,
                'numero_gestion' => $assignment->gestionAcademica?->numero_gestion,
                'nombre' => $assignment->gestionAcademica?->nombre,
            ],
            'horario' => [
                'id' => $assignment->horarioClase?->id,
                'dia' => $assignment->horarioClase?->dia?->nombre,
                'turno' => $assignment->horarioClase?->turno?->nombre,
                'periodo' => $assignment->horarioClase?->periodo?->numero_periodo,
                'hora_inicio' => $assignment->horarioClase?->hora_inicio,
                'hora_fin' => $assignment->horarioClase?->hora_fin,
                'aula' => $assignment->horarioClase?->aula?->ubicacion,
            ],
        ];
    }

    private function baseQuery()
    {
        return AsignacionDocenteModel::query()
            ->with([
                'docente.persona',
                'materia',
                'grupo',
                'gestionAcademica',
                'horarioClase.dia',
                'horarioClase.turno',
                'horarioClase.periodo',
                'horarioClase.aula',
            ]);
    }

    private function validateEntities(DocenteModel $teacher, MateriaModel $subject, GrupoModel $group, HorarioClaseModel $schedule, array $data): void
    {
        if (! $teacher->activo || ! $teacher->contratado) {
            throw new RuntimeException('El docente debe estar activo y contratado.');
        }

        if (! $subject->activa) {
            throw new RuntimeException('La materia indicada no esta activa.');
        }

        if (! $group->activo) {
            throw new RuntimeException('El grupo indicado no esta activo.');
        }

        if (! $schedule->activo) {
            throw new RuntimeException('El horario indicado no esta activo.');
        }

        if ((int) $group->gestion_academica_id !== (int) $data['gestion_academica_id']) {
            throw new RuntimeException('El grupo no corresponde a la gestion academica indicada.');
        }

        if ((int) $schedule->gestion_academica_id !== (int) $data['gestion_academica_id']
            || (int) $schedule->grupo_id !== (int) $data['grupo_id']
            || (int) $schedule->materia_id !== (int) $data['materia_id']) {
            throw new RuntimeException('El horario no corresponde a la gestion, grupo y materia indicados.');
        }
    }

    private function validateTeacherLimits(int $teacherId, int $subjectId, int $groupId): void
    {
        $groupIds = DB::table('asignacion_docente')
            ->where('docente_id', $teacherId)
            ->where('activo', true)
            ->distinct()
            ->pluck('grupo_id')
            ->push($groupId)
            ->unique();

        if ($groupIds->count() > 4) {
            throw new RuntimeException('El docente no puede superar 4 grupos asignados.');
        }

        $subjectIds = DB::table('asignacion_docente')
            ->where('docente_id', $teacherId)
            ->where('activo', true)
            ->distinct()
            ->pluck('materia_id')
            ->push($subjectId)
            ->unique();

        if ($subjectIds->count() > 4) {
            throw new RuntimeException('El docente no puede superar 4 materias asignadas.');
        }
    }
}

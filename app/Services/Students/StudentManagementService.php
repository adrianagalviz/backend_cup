<?php

namespace App\Services\Students;

use App\Models\AlumnoModel;
use App\Models\GrupoAlumnoModel;
use App\Models\PromedioFinalModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class StudentManagementService
{
    public function listStudents(array $filters): LengthAwarePaginator
    {
        return $this->studentQuery()
            ->when($filters['gestion_academica_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('gestion_academica_id', (int) $id))
            ->when($filters['estado_academico'] ?? null, function (Builder $query, string $state): void {
                if ($state === 'activo') {
                    $query->whereDoesntHave('promediosFinales', fn (Builder $averageQuery) => $averageQuery
                        ->whereColumn('promedio_final.gestion_academica_id', 'alumno.gestion_academica_id'));

                    return;
                }

                $query->whereHas('promediosFinales', fn (Builder $averageQuery) => $averageQuery
                    ->whereColumn('promedio_final.gestion_academica_id', 'alumno.gestion_academica_id')
                    ->where('estado_final', $state));
            })
            ->when($filters['grupo_id'] ?? null, function (Builder $query, int|string $id): void {
                $query->whereHas('grupos', fn (Builder $groupQuery) => $groupQuery
                    ->where('grupo_id', (int) $id)
                    ->where('activo', true));
            })
            ->when($filters['buscar'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $studentQuery) use ($search): void {
                    $studentQuery->where('codigo_alumno', 'ILIKE', "%{$search}%")
                        ->orWhereHas('persona', function (Builder $personQuery) use ($search): void {
                            $personQuery->where('cedula_identidad', 'ILIKE', "%{$search}%")
                                ->orWhere('nombres', 'ILIKE', "%{$search}%")
                                ->orWhere('apellido_paterno', 'ILIKE', "%{$search}%")
                                ->orWhere('apellido_materno', 'ILIKE', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('id')
            ->paginate((int) ($filters['por_pagina'] ?? 15));
    }

    public function findStudent(int $id): AlumnoModel
    {
        return $this->studentQuery()->findOrFail($id);
    }

    public function formatStudent(AlumnoModel $student, bool $detailed = false): array
    {
        $activeGroup = $this->activeGroup($student);
        $average = $this->average($student);
        $attendanceSummary = $this->attendanceSummary($student);
        $academicState = $average?->estado_final ?: 'activo';

        $data = [
            'id' => $student->id,
            'codigo_alumno' => $student->codigo_alumno,
            'estado_academico' => $academicState,
            'creado_en' => $student->creado_en,
            'persona' => [
                'id' => $student->persona?->id,
                'cedula_identidad' => $student->persona?->cedula_identidad,
                'nombres' => $student->persona?->nombres,
                'apellido_paterno' => $student->persona?->apellido_paterno,
                'apellido_materno' => $student->persona?->apellido_materno,
                'fecha_nacimiento' => $student->persona?->fecha_nacimiento,
                'sexo' => $student->persona?->sexo,
                'direccion' => $student->persona?->direccion,
                'telefono' => $student->persona?->telefono,
                'celular' => $student->persona?->celular,
                'correo' => $student->persona?->correo,
                'ciudad' => $student->persona?->ciudad,
            ],
            'usuario' => [
                'id' => $student->usuario?->id,
                'nombre_usuario' => $student->usuario?->nombre_usuario,
                'codigo_acceso' => $student->usuario?->codigo_acceso,
                'rol' => $student->usuario?->rol?->nombre,
                'activo' => $student->usuario?->activo,
            ],
            'gestion_academica' => [
                'id' => $student->gestionAcademica?->id,
                'anio' => $student->gestionAcademica?->anio,
                'numero_gestion' => $student->gestionAcademica?->numero_gestion,
                'nombre' => $student->gestionAcademica?->nombre,
            ],
            'postulante' => [
                'id' => $student->postulante?->id,
                'estado_postulante' => $student->postulante?->estado_postulante,
                'estado_requisitos' => $student->postulante?->estado_requisitos,
                'estado_pago' => $student->postulante?->estado_pago,
            ],
            'grupo_activo' => $activeGroup ? [
                'id' => $activeGroup->grupo?->id,
                'nombre' => $activeGroup->grupo?->nombre,
                'cupo_maximo' => $activeGroup->grupo?->cupo_maximo,
                'fecha_asignacion' => $activeGroup->fecha_asignacion,
                'activo' => $activeGroup->activo,
            ] : null,
            'promedio_final' => $average ? [
                'id' => $average->id,
                'parcial_1' => $average->parcial_1,
                'parcial_2' => $average->parcial_2,
                'parcial_3' => $average->parcial_3,
                'promedio' => $average->promedio,
                'estado_final' => $average->estado_final,
                'calculado_en' => $average->calculado_en,
            ] : null,
            'asistencia_resumen' => $attendanceSummary,
        ];

        if (! $detailed) {
            return $data;
        }

        return array_merge($data, [
            'grupos' => $student->grupos->map(fn (GrupoAlumnoModel $groupStudent): array => [
                'id' => $groupStudent->id,
                'activo' => $groupStudent->activo,
                'fecha_asignacion' => $groupStudent->fecha_asignacion,
                'grupo' => [
                    'id' => $groupStudent->grupo?->id,
                    'nombre' => $groupStudent->grupo?->nombre,
                    'cupo_maximo' => $groupStudent->grupo?->cupo_maximo,
                ],
            ])->values(),
        ]);
    }

    private function studentQuery(): Builder
    {
        return AlumnoModel::query()
            ->with(['persona', 'usuario.rol', 'postulante', 'gestionAcademica', 'grupos.grupo']);
    }

    private function activeGroup(AlumnoModel $student): ?GrupoAlumnoModel
    {
        return $student->grupos
            ->first(fn (GrupoAlumnoModel $groupStudent): bool => (bool) $groupStudent->activo);
    }

    private function average(AlumnoModel $student): ?PromedioFinalModel
    {
        return PromedioFinalModel::query()
            ->where('alumno_id', $student->id)
            ->where('gestion_academica_id', $student->gestion_academica_id)
            ->latest('id')
            ->first();
    }

    private function attendanceSummary(AlumnoModel $student): array
    {
        $summary = DB::table('asistencia_alumno')
            ->select('estado_asistencia', DB::raw('COUNT(*) as total'))
            ->where('alumno_id', $student->id)
            ->groupBy('estado_asistencia')
            ->pluck('total', 'estado_asistencia');

        return [
            'presentes' => (int) ($summary['presente'] ?? 0),
            'retrasos' => (int) ($summary['retraso'] ?? 0),
            'faltas' => (int) ($summary['falta'] ?? 0),
            'pendientes' => (int) ($summary['pendiente'] ?? 0),
        ];
    }
}

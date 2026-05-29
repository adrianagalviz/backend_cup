<?php

namespace App\Services\Admissions;

use App\Models\AlumnoModel;
use App\Models\CarreraModel;
use App\Models\PostulacionModel;
use App\Models\PromedioFinalModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CareerAssignmentService
{
    public function assignByGestion(int $gestionId, bool $reassign = false): array
    {
        return DB::transaction(function () use ($gestionId, $reassign): array {
            $approved = $this->approvedStudentsByPriority($gestionId);

            if ($approved->isEmpty()) {
                return [
                    'gestion_academica_id' => $gestionId,
                    'cantidad_aprobados' => 0,
                    'cantidad_asignados' => 0,
                    'cantidad_omitidos' => 0,
                    'aprobados_ordenados' => [],
                    'asignaciones' => [],
                    'omitidos' => [],
                    'cupos' => $this->quotaSummary($gestionId),
                ];
            }

            if ($reassign) {
                $this->clearAssignments($gestionId);
            }

            $assignments = [];
            $skipped = [];
            $priority = 1;

            foreach ($approved as $average) {
                $student = $average->alumno;
                $application = PostulacionModel::query()
                    ->with(['primeraCarrera', 'segundaCarrera', 'carreraAsignada'])
                    ->where('postulante_id', $student->postulante_id)
                    ->first();

                if (! $application) {
                    $skipped[] = $this->skip($student, 'El alumno no tiene postulacion registrada.');
                    continue;
                }

                if ($application->carrera_asignada_id && ! $reassign) {
                    $skipped[] = $this->skip($student, 'El alumno ya tiene carrera asignada.', $application);
                    continue;
                }

                $decision = $this->selectCareer($application, $gestionId);

                if (! $decision) {
                    $skipped[] = $this->skip($student, 'No existen cupos disponibles para asignacion final.', $application);
                    continue;
                }

                DB::table('postulacion')
                    ->where('id', $application->id)
                    ->update([
                        'carrera_asignada_id' => $decision['carrera_id'],
                        'motivo_asignacion' => $decision['motivo'],
                        'promedio_final' => $average->promedio,
                        'estado_final' => 'aprobado',
                        'orden_prioridad' => $priority,
                        'asignado_en' => now(),
                    ]);

                $application = PostulacionModel::query()
                    ->with(['primeraCarrera', 'segundaCarrera', 'carreraAsignada'])
                    ->findOrFail($application->id);

                $assignments[] = [
                    'orden_prioridad' => $priority,
                    'alumno' => $this->formatStudent($student),
                    'promedio' => $average->promedio,
                    'primera_opcion' => $this->formatCareer($application->primeraCarrera),
                    'segunda_opcion' => $this->formatCareer($application->segundaCarrera),
                    'carrera_asignada' => $this->formatCareer($application->carreraAsignada),
                    'motivo_asignacion' => $application->motivo_asignacion,
                ];

                $priority++;
            }

            return [
                'gestion_academica_id' => $gestionId,
                'reasignado' => $reassign,
                'cantidad_aprobados' => $approved->count(),
                'cantidad_asignados' => count($assignments),
                'cantidad_omitidos' => count($skipped),
                'aprobados_ordenados' => $approved
                    ->map(fn (PromedioFinalModel $average) => [
                        'alumno' => $this->formatStudent($average->alumno),
                        'promedio' => $average->promedio,
                        'estado_final' => $average->estado_final,
                    ])
                    ->values(),
                'asignaciones' => $assignments,
                'omitidos' => $skipped,
                'cupos' => $this->quotaSummary($gestionId),
            ];
        });
    }

    private function approvedStudentsByPriority(int $gestionId): Collection
    {
        return PromedioFinalModel::query()
            ->with(['alumno.persona', 'alumno.gestionAcademica'])
            ->where('gestion_academica_id', $gestionId)
            ->where('estado_final', 'aprobado')
            ->orderByDesc('promedio')
            ->orderBy('alumno_id')
            ->get();
    }

    private function selectCareer(PostulacionModel $application, int $gestionId): ?array
    {
        if ($this->hasAvailableQuota($application->primera_carrera_id, $gestionId)) {
            return [
                'carrera_id' => $application->primera_carrera_id,
                'motivo' => 'primera_opcion',
            ];
        }

        if ($this->hasAvailableQuota($application->segunda_carrera_id, $gestionId)) {
            return [
                'carrera_id' => $application->segunda_carrera_id,
                'motivo' => 'segunda_opcion',
            ];
        }

        $leastOccupied = $this->leastOccupiedCareerWithQuota($gestionId);

        if (! $leastOccupied) {
            return null;
        }

        return [
            'carrera_id' => $leastOccupied['carrera_id'],
            'motivo' => 'carrera_con_menos_personas',
        ];
    }

    private function hasAvailableQuota(int $careerId, int $gestionId): bool
    {
        $quota = DB::table('cupo_carrera')
            ->where('carrera_id', $careerId)
            ->where('gestion_academica_id', $gestionId)
            ->first();

        if (! $quota) {
            return false;
        }

        return $this->occupiedCount($careerId, $gestionId) < (int) $quota->cantidad_cupos;
    }

    private function leastOccupiedCareerWithQuota(int $gestionId): ?array
    {
        return collect(DB::table('cupo_carrera')
            ->join('carrera', 'carrera.id', '=', 'cupo_carrera.carrera_id')
            ->where('cupo_carrera.gestion_academica_id', $gestionId)
            ->where('carrera.activa', true)
            ->select('cupo_carrera.carrera_id', 'cupo_carrera.cantidad_cupos', 'carrera.nombre')
            ->get())
            ->map(function ($quota) use ($gestionId): array {
                $occupied = $this->occupiedCount((int) $quota->carrera_id, $gestionId);

                return [
                    'carrera_id' => (int) $quota->carrera_id,
                    'nombre' => $quota->nombre,
                    'cantidad_cupos' => (int) $quota->cantidad_cupos,
                    'ocupados' => $occupied,
                    'disponibles' => max(0, (int) $quota->cantidad_cupos - $occupied),
                ];
            })
            ->filter(fn (array $quota): bool => $quota['disponibles'] > 0)
            ->sortBy([
                ['ocupados', 'asc'],
                ['carrera_id', 'asc'],
            ])
            ->first();
    }

    private function occupiedCount(int $careerId, int $gestionId): int
    {
        return DB::table('postulacion')
            ->join('postulante', 'postulante.id', '=', 'postulacion.postulante_id')
            ->where('postulante.gestion_academica_id', $gestionId)
            ->where('postulacion.carrera_asignada_id', $careerId)
            ->count();
    }

    private function quotaSummary(int $gestionId): Collection
    {
        return collect(DB::table('cupo_carrera')
            ->join('carrera', 'carrera.id', '=', 'cupo_carrera.carrera_id')
            ->where('cupo_carrera.gestion_academica_id', $gestionId)
            ->select('cupo_carrera.carrera_id', 'carrera.nombre', 'cupo_carrera.cantidad_cupos')
            ->orderBy('carrera.nombre')
            ->get())
            ->map(function ($quota) use ($gestionId): array {
                $occupied = $this->occupiedCount((int) $quota->carrera_id, $gestionId);

                return [
                    'carrera_id' => (int) $quota->carrera_id,
                    'carrera' => $quota->nombre,
                    'cantidad_cupos' => (int) $quota->cantidad_cupos,
                    'cupos_ocupados' => $occupied,
                    'cupos_disponibles' => max(0, (int) $quota->cantidad_cupos - $occupied),
                ];
            })
            ->values();
    }

    private function clearAssignments(int $gestionId): void
    {
        $postulanteIds = DB::table('postulante')
            ->where('gestion_academica_id', $gestionId)
            ->pluck('id');

        DB::table('postulacion')
            ->whereIn('postulante_id', $postulanteIds)
            ->where('estado_final', 'aprobado')
            ->update([
                'carrera_asignada_id' => null,
                'motivo_asignacion' => null,
                'orden_prioridad' => null,
                'asignado_en' => null,
            ]);
    }

    private function skip(AlumnoModel $student, string $message, ?PostulacionModel $application = null): array
    {
        return [
            'alumno' => $this->formatStudent($student),
            'mensaje' => $message,
            'carrera_asignada' => $application?->carreraAsignada ? $this->formatCareer($application->carreraAsignada) : null,
        ];
    }

    private function formatStudent(AlumnoModel $student): array
    {
        return [
            'id' => $student->id,
            'codigo_alumno' => $student->codigo_alumno,
            'estado_academico' => $student->estado_academico,
            'persona' => [
                'id' => $student->persona?->id,
                'cedula_identidad' => $student->persona?->cedula_identidad,
                'nombres' => $student->persona?->nombres,
                'apellido_paterno' => $student->persona?->apellido_paterno,
                'apellido_materno' => $student->persona?->apellido_materno,
            ],
        ];
    }

    private function formatCareer(?CarreraModel $career): ?array
    {
        if (! $career) {
            return null;
        }

        return [
            'id' => $career->id,
            'nombre' => $career->nombre,
        ];
    }
}

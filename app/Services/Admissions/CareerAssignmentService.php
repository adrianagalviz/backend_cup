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
    public function summaryByGestion(int $gestionId): array
    {
        $approved = $this->approvedStudentsByPriority($gestionId);

        return $this->buildSummary($gestionId, $approved);
    }

    public function assignByGestion(int $gestionId, bool $reassign = false): array
    {
        return DB::transaction(function () use ($gestionId, $reassign): array {
            $approved = $this->approvedStudentsByPriority($gestionId);

            if ($reassign) {
                $this->clearAssignments($gestionId);
            }

            $priority = 1;

            foreach ($approved as $average) {
                $student = $average->alumno;
                $application = PostulacionModel::query()
                    ->with(['primeraCarrera', 'segundaCarrera', 'carreraAsignada'])
                    ->where('postulante_id', $student->postulante_id)
                    ->first();

                if (! $application) {
                    $priority++;
                    continue;
                }

                if ($application->carrera_asignada_id && ! $reassign) {
                    $priority++;
                    continue;
                }

                $decision = $this->selectCareer($application, $gestionId);

                if (! $decision) {
                    $priority++;
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

                $priority++;
            }

            return [
                ...$this->buildSummary($gestionId, $approved),
                'reasignado' => $reassign,
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

        return null;
    }

    private function occupiedCount(int $careerId, int $gestionId): int
    {
        return DB::table('postulacion')
            ->join('postulante', 'postulante.id', '=', 'postulacion.postulante_id')
            ->where('postulante.gestion_academica_id', $gestionId)
            ->where('postulacion.carrera_asignada_id', $careerId)
            ->count();
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

    private function buildSummary(int $gestionId, Collection $approved): array
    {
        $applicationsByStudent = $this->applicationsByStudent($approved);

        $approvedList = $approved
            ->map(function (PromedioFinalModel $average, int $index) use ($applicationsByStudent): array {
                $application = $applicationsByStudent->get($average->alumno->postulante_id);

                return [
                    'orden_prioridad' => $application?->orden_prioridad ?: $index + 1,
                    'alumno' => $this->formatStudent($average->alumno),
                    'promedio' => $average->promedio,
                    'estado_final' => $average->estado_final,
                    'primera_opcion' => $this->formatCareer($application?->primeraCarrera),
                    'segunda_opcion' => $this->formatCareer($application?->segundaCarrera),
                    'carrera_asignada' => $this->formatCareer($application?->carreraAsignada),
                    'motivo_asignacion' => $application?->motivo_asignacion,
                    'asignado_en' => $application?->asignado_en,
                ];
            })
            ->values();

        $assignments = $approvedList
            ->filter(fn (array $item): bool => $item['carrera_asignada'] !== null)
            ->values();

        $skipped = $approved
            ->filter(function (PromedioFinalModel $average) use ($applicationsByStudent, $gestionId): bool {
                $application = $applicationsByStudent->get($average->alumno->postulante_id);

                return ! $application
                    || (! $application->carrera_asignada_id && ! $this->hasAvailableQuota($application->primera_carrera_id, $gestionId) && ! $this->hasAvailableQuota($application->segunda_carrera_id, $gestionId));
            })
            ->map(function (PromedioFinalModel $average) use ($applicationsByStudent): array {
                $application = $applicationsByStudent->get($average->alumno->postulante_id);
                $message = $application
                    ? 'Primera y segunda opcion sin cupo disponible.'
                    : 'El alumno no tiene postulacion registrada.';

                return $this->skip($average->alumno, $message, $application);
            })
            ->values();

        return [
            'gestion_academica_id' => $gestionId,
            'cantidad_aprobados' => $approved->count(),
            'cantidad_asignados' => $assignments->count(),
            'cantidad_omitidos' => $skipped->count(),
            'aprobados_ordenados' => $approvedList,
            'asignaciones' => $assignments,
            'omitidos' => $skipped,
            'cupos' => $this->quotaSummary($gestionId),
        ];
    }

    private function applicationsByStudent(Collection $approved): Collection
    {
        $postulanteIds = $approved
            ->pluck('alumno.postulante_id')
            ->filter()
            ->values();

        if ($postulanteIds->isEmpty()) {
            return collect();
        }

        return PostulacionModel::query()
            ->with(['primeraCarrera', 'segundaCarrera', 'carreraAsignada'])
            ->whereIn('postulante_id', $postulanteIds)
            ->get()
            ->keyBy('postulante_id');
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
            'codigo' => $career->codigo,
            'nombre' => $career->nombre,
        ];
    }
}

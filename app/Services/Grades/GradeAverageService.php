<?php

namespace App\Services\Grades;

use App\Models\AlumnoModel;
use App\Models\NotaParcialModel;
use App\Models\PromedioFinalModel;
use App\Models\UsuarioModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GradeAverageService
{
    public function calculate(array $data): array
    {
        if (isset($data['alumno_id'])) {
            $average = $this->calculateForStudent((int) $data['alumno_id'], $data['gestion_academica_id'] ?? null);

            return [
                'cantidad_calculada' => 1,
                'promedios' => [$this->formatAverage($average)],
            ];
        }

        $students = AlumnoModel::query()
            ->where('gestion_academica_id', (int) $data['gestion_academica_id'])
            ->orderBy('id')
            ->get();

        $averages = [];
        $errors = [];

        foreach ($students as $student) {
            try {
                $averages[] = $this->calculateForStudent($student->id, $student->gestion_academica_id);
            } catch (RuntimeException $exception) {
                $errors[] = [
                    'alumno_id' => $student->id,
                    'mensaje' => $exception->getMessage(),
                ];
            }
        }

        return [
            'cantidad_calculada' => count($averages),
            'cantidad_omitida' => count($errors),
            'omitidos' => $errors,
            'promedios' => collect($averages)->map(fn (PromedioFinalModel $average) => $this->formatAverage($average))->values(),
        ];
    }

    public function notesForStudent(UsuarioModel $user, int $studentId): array
    {
        $this->ensureCanAccessStudent($user, $studentId);

        $student = AlumnoModel::query()
            ->with(['persona', 'gestionAcademica'])
            ->findOrFail($studentId);

        $notes = NotaParcialModel::query()
            ->with(['examen.gestionAcademica'])
            ->where('alumno_id', $student->id)
            ->orderBy('numero_parcial')
            ->get();

        $average = PromedioFinalModel::query()
            ->with(['alumno.persona', 'gestionAcademica'])
            ->where('alumno_id', $student->id)
            ->where('gestion_academica_id', $student->gestion_academica_id)
            ->first();

        return [
            'alumno' => $this->formatStudent($student),
            'notas_parciales' => $notes->map(fn (NotaParcialModel $note) => $this->formatPartialNote($note))->values(),
            'promedio_final' => $average ? $this->formatAverage($average) : null,
        ];
    }

    public function listAverages(UsuarioModel $user, array $filters): LengthAwarePaginator
    {
        return $this->averageQuery($user, $filters)
            ->orderByDesc('promedio')
            ->orderBy('alumno_id')
            ->paginate((int) ($filters['por_pagina'] ?? 15));
    }

    public function approved(UsuarioModel $user, array $filters): LengthAwarePaginator
    {
        $filters['estado_final'] = 'aprobado';

        return $this->listAverages($user, $filters);
    }

    public function failed(UsuarioModel $user, array $filters): LengthAwarePaginator
    {
        $filters['estado_final'] = 'reprobado';

        return $this->listAverages($user, $filters);
    }

    public function formatAverage(PromedioFinalModel $average): array
    {
        return [
            'id' => $average->id,
            'alumno' => $average->alumno ? $this->formatStudent($average->alumno) : null,
            'gestion_academica' => [
                'id' => $average->gestionAcademica?->id,
                'anio' => $average->gestionAcademica?->anio,
                'numero_gestion' => $average->gestionAcademica?->numero_gestion,
                'nombre' => $average->gestionAcademica?->nombre,
            ],
            'parcial_1' => $average->parcial_1,
            'parcial_2' => $average->parcial_2,
            'parcial_3' => $average->parcial_3,
            'promedio' => $average->promedio,
            'estado_final' => $average->estado_final,
            'calculado_en' => $average->calculado_en,
        ];
    }

    public function formatPartialNote(NotaParcialModel $note): array
    {
        return [
            'id' => $note->id,
            'alumno_id' => $note->alumno_id,
            'examen' => [
                'id' => $note->examen?->id,
                'titulo' => $note->examen?->titulo,
                'numero_parcial' => $note->examen?->numero_parcial,
                'gestion_academica_id' => $note->examen?->gestion_academica_id,
            ],
            'intento_examen_id' => $note->intento_examen_id,
            'numero_parcial' => $note->numero_parcial,
            'nota' => $note->nota,
            'registrado_en' => $note->registrado_en,
        ];
    }

    private function calculateForStudent(int $studentId, int|string|null $gestionId = null): PromedioFinalModel
    {
        return DB::transaction(function () use ($studentId, $gestionId): PromedioFinalModel {
            $student = AlumnoModel::query()->with(['persona', 'gestionAcademica'])->findOrFail($studentId);
            $targetGestionId = (int) ($gestionId ?? $student->gestion_academica_id);

            if ((int) $student->gestion_academica_id !== $targetGestionId) {
                throw new RuntimeException('El alumno no pertenece a la gestion academica indicada.');
            }

            $notes = NotaParcialModel::query()
                ->where('alumno_id', $student->id)
                ->whereHas('examen', fn (Builder $query) => $query->where('gestion_academica_id', $targetGestionId))
                ->get()
                ->keyBy('numero_parcial');

            $this->ensureThreePartials($notes);

            $partial1 = (float) $notes->get(1)->nota;
            $partial2 = (float) $notes->get(2)->nota;
            $partial3 = (float) $notes->get(3)->nota;
            $averageValue = round(($partial1 + $partial2 + $partial3) / 3, 2);
            $state = $averageValue >= 60 ? 'aprobado' : 'reprobado';

            DB::table('promedio_final')->updateOrInsert(
                [
                    'alumno_id' => $student->id,
                    'gestion_academica_id' => $targetGestionId,
                ],
                [
                    'parcial_1' => $partial1,
                    'parcial_2' => $partial2,
                    'parcial_3' => $partial3,
                    'promedio' => $averageValue,
                    'estado_final' => $state,
                    'calculado_en' => now(),
                ]
            );

            DB::table('alumno')->where('id', $student->id)->update([
                'estado_academico' => $state,
            ]);

            DB::table('postulacion')
                ->where('postulante_id', $student->postulante_id)
                ->update([
                    'promedio_final' => $averageValue,
                    'estado_final' => $state,
                ]);

            return PromedioFinalModel::query()
                ->with(['alumno.persona', 'alumno.gestionAcademica', 'gestionAcademica'])
                ->where('alumno_id', $student->id)
                ->where('gestion_academica_id', $targetGestionId)
                ->firstOrFail();
        });
    }

    private function ensureThreePartials(Collection $notes): void
    {
        foreach ([1, 2, 3] as $partial) {
            if (! $notes->has($partial)) {
                throw new RuntimeException('El alumno debe tener registrados los 3 parciales.');
            }

            $note = (float) $notes->get($partial)->nota;

            if ($note < 0 || $note > 100) {
                throw new RuntimeException('Cada nota parcial debe estar entre 0 y 100.');
            }
        }
    }

    private function averageQuery(UsuarioModel $user, array $filters): Builder
    {
        $query = PromedioFinalModel::query()
            ->with(['alumno.persona', 'alumno.gestionAcademica', 'gestionAcademica'])
            ->when($filters['gestion_academica_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('gestion_academica_id', (int) $id))
            ->when($filters['alumno_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('alumno_id', (int) $id))
            ->when($filters['estado_final'] ?? null, fn (Builder $query, string $state) => $query->where('estado_final', $state));

        if ($user->rol?->nombre === 'alumno') {
            if (! $user->alumno) {
                throw new RuntimeException('El usuario autenticado debe ser alumno.');
            }

            $query->where('alumno_id', $user->alumno->id);
        }

        return $query;
    }

    private function ensureCanAccessStudent(UsuarioModel $user, int $studentId): void
    {
        if ($user->rol?->nombre === 'administrador') {
            return;
        }

        if ($user->rol?->nombre === 'alumno' && $user->alumno && (int) $user->alumno->id === $studentId) {
            return;
        }

        throw new RuntimeException('No tienes permisos para consultar las notas de este alumno.', 403);
    }

    private function formatStudent(AlumnoModel $student): array
    {
        return [
            'id' => $student->id,
            'codigo_alumno' => $student->codigo_alumno,
            'estado_academico' => $student->estado_academico,
            'gestion_academica_id' => $student->gestion_academica_id,
            'persona' => [
                'id' => $student->persona?->id,
                'cedula_identidad' => $student->persona?->cedula_identidad,
                'nombres' => $student->persona?->nombres,
                'apellido_paterno' => $student->persona?->apellido_paterno,
                'apellido_materno' => $student->persona?->apellido_materno,
            ],
        ];
    }
}

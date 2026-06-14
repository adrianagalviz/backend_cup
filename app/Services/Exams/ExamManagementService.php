<?php

namespace App\Services\Exams;

use App\Models\ExamenMateriaPorcentajeModel;
use App\Models\ExamenModel;
use App\Models\OpcionPreguntaModel;
use App\Models\PreguntaModel;
use App\Models\UsuarioModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ExamManagementService
{
    public function createExam(UsuarioModel $user, array $data): ExamenModel
    {
        $id = DB::table('examen')->insertGetId([
            'gestion_academica_id' => $data['gestion_academica_id'],
            'numero_parcial' => $data['numero_parcial'],
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'] ?? null,
            'habilitado' => false,
            'fecha_inicio' => $data['fecha_inicio'] ?? null,
            'fecha_fin' => $data['fecha_fin'] ?? null,
            'creado_por_usuario_id' => $user->id,
            'creado_en' => now(),
        ]);

        return $this->findExam($id);
    }

    public function listExams(array $filters): LengthAwarePaginator
    {
        return ExamenModel::query()
            ->with(['gestionAcademica', 'materiasPorcentaje.materia', 'preguntas.opciones'])
            ->when($filters['gestion_academica_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('gestion_academica_id', (int) $id))
            ->when($filters['numero_parcial'] ?? null, fn (Builder $query, int|string $partial) => $query->where('numero_parcial', (int) $partial))
            ->when(array_key_exists('habilitado', $filters), function (Builder $query) use ($filters): void {
                $query->where('habilitado', filter_var($filters['habilitado'], FILTER_VALIDATE_BOOLEAN));
            })
            ->orderByDesc('gestion_academica_id')
            ->orderBy('numero_parcial')
            ->paginate((int) ($filters['por_pagina'] ?? 15));
    }

    public function syncSubjects(int $examId, array $subjects): ExamenModel
    {
        return DB::transaction(function () use ($examId, $subjects): ExamenModel {
            $exam = $this->findExam($examId);
            $this->ensureCanEditExam($exam);
            $this->ensurePercentagesSumOneHundred($subjects);

            DB::table('examen_materia_porcentaje')->where('examen_id', $exam->id)->delete();

            foreach ($subjects as $subject) {
                DB::table('examen_materia_porcentaje')->insert([
                    'examen_id' => $exam->id,
                    'materia_id' => $subject['materia_id'],
                    'porcentaje' => $subject['porcentaje'],
                ]);
            }

            $this->recalculateQuestionScores($exam->id);

            return $this->findExam($exam->id);
        });
    }

    public function createQuestion(int $examId, array $data): PreguntaModel
    {
        return DB::transaction(function () use ($examId, $data): PreguntaModel {
            $exam = $this->findExam($examId);
            $this->ensureCanEditExam($exam);
            $this->ensureSubjectBelongsToExam($exam->id, (int) $data['materia_id']);

            $id = DB::table('pregunta')->insertGetId([
                'examen_id' => $exam->id,
                'materia_id' => $data['materia_id'],
                'enunciado' => $data['enunciado'],
                'tipo_pregunta' => 'seleccion_multiple',
                'puntaje' => 1,
                'activa' => (bool) ($data['activa'] ?? true),
                'creado_en' => now(),
            ]);

            $this->recalculateQuestionScores($exam->id, (int) $data['materia_id']);

            return $this->findQuestion($id);
        });
    }

    public function syncOptions(int $questionId, array $options): PreguntaModel
    {
        return DB::transaction(function () use ($questionId, $options): PreguntaModel {
            $question = $this->findQuestion($questionId);
            $this->ensureCanEditExam($question->examen);
            $this->ensureSingleCorrectOption($options);

            DB::table('opcion_pregunta')->where('pregunta_id', $question->id)->delete();

            foreach (array_values($options) as $index => $option) {
                DB::table('opcion_pregunta')->insert([
                    'pregunta_id' => $question->id,
                    'texto_opcion' => $option['texto_opcion'],
                    'es_correcta' => (bool) ($option['es_correcta'] ?? false),
                    'orden' => $option['orden'] ?? ($index + 1),
                ]);
            }

            return $this->findQuestion($question->id);
        });
    }

    public function enableExam(int $id): ExamenModel
    {
        $exam = $this->findExam($id);
        $this->validateExamCanBeEnabled($exam);

        DB::table('examen')->where('id', $exam->id)->update([
            'habilitado' => true,
            'actualizado_en' => now(),
        ]);

        return $this->findExam($exam->id);
    }

    public function disableExam(int $id): ExamenModel
    {
        $exam = $this->findExam($id);

        DB::table('examen')->where('id', $exam->id)->update([
            'habilitado' => false,
            'actualizado_en' => now(),
        ]);

        return $this->findExam($exam->id);
    }

    public function findExam(int $id): ExamenModel
    {
        return ExamenModel::query()
            ->with(['gestionAcademica', 'materiasPorcentaje.materia', 'preguntas.materia', 'preguntas.opciones'])
            ->findOrFail($id);
    }

    public function findQuestion(int $id): PreguntaModel
    {
        return PreguntaModel::query()
            ->with(['examen.gestionAcademica', 'materia', 'opciones'])
            ->findOrFail($id);
    }

    public function formatExam(ExamenModel $exam): array
    {
        return [
            'id' => $exam->id,
            'numero_parcial' => $exam->numero_parcial,
            'titulo' => $exam->titulo,
            'descripcion' => $exam->descripcion,
            'habilitado' => $exam->habilitado,
            'fecha_inicio' => $exam->fecha_inicio,
            'fecha_fin' => $exam->fecha_fin,
            'gestion_academica' => [
                'id' => $exam->gestionAcademica?->id,
                'anio' => $exam->gestionAcademica?->anio,
                'numero_gestion' => $exam->gestionAcademica?->numero_gestion,
                'nombre' => $exam->gestionAcademica?->nombre,
            ],
            'materias' => $exam->materiasPorcentaje
                ->map(fn (ExamenMateriaPorcentajeModel $subject) => $this->formatSubjectPercentage($subject))
                ->values(),
            'preguntas' => $exam->preguntas
                ->map(fn (PreguntaModel $question) => $this->formatQuestion($question))
                ->values(),
            'creado_en' => $exam->creado_en,
            'actualizado_en' => $exam->actualizado_en,
        ];
    }

    public function formatQuestion(PreguntaModel $question): array
    {
        return [
            'id' => $question->id,
            'examen_id' => $question->examen_id,
            'materia' => [
                'id' => $question->materia?->id,
                'nombre' => $question->materia?->nombre,
            ],
            'enunciado' => $question->enunciado,
            'tipo_pregunta' => $question->tipo_pregunta,
            'puntaje' => $question->puntaje,
            'activa' => $question->activa,
            'opciones' => $question->opciones
                ->sortBy('orden')
                ->map(fn (OpcionPreguntaModel $option) => $this->formatOption($option))
                ->values(),
        ];
    }

    private function formatSubjectPercentage(ExamenMateriaPorcentajeModel $subject): array
    {
        return [
            'id' => $subject->id,
            'materia_id' => $subject->materia_id,
            'materia' => $subject->materia?->nombre,
            'porcentaje' => $subject->porcentaje,
        ];
    }

    private function formatOption(OpcionPreguntaModel $option): array
    {
        return [
            'id' => $option->id,
            'texto_opcion' => $option->texto_opcion,
            'es_correcta' => $option->es_correcta,
            'orden' => $option->orden,
        ];
    }

    private function ensurePercentagesSumOneHundred(array $subjects): void
    {
        $sum = collect($subjects)->sum(fn (array $subject): float => (float) $subject['porcentaje']);

        if (round($sum, 2) !== 100.0) {
            throw new RuntimeException('La suma de porcentajes de materias debe ser 100.');
        }
    }

    private function ensureSubjectBelongsToExam(int $examId, int $subjectId): void
    {
        $exists = DB::table('examen_materia_porcentaje')
            ->where('examen_id', $examId)
            ->where('materia_id', $subjectId)
            ->exists();

        if (! $exists) {
            throw new RuntimeException('La materia debe estar asociada al examen antes de crear preguntas.');
        }
    }

    private function recalculateQuestionScores(int $examId, ?int $subjectId = null): void
    {
        $percentages = DB::table('examen_materia_porcentaje')
            ->where('examen_id', $examId)
            ->when($subjectId, fn ($query) => $query->where('materia_id', $subjectId))
            ->get(['materia_id', 'porcentaje']);

        foreach ($percentages as $percentage) {
            $activeQuestions = DB::table('pregunta')
                ->where('examen_id', $examId)
                ->where('materia_id', $percentage->materia_id)
                ->where('activa', true)
                ->count();

            if ($activeQuestions < 1) {
                continue;
            }

            $score = round(((float) $percentage->porcentaje) / $activeQuestions, 2);

            DB::table('pregunta')
                ->where('examen_id', $examId)
                ->where('materia_id', $percentage->materia_id)
                ->where('activa', true)
                ->update([
                    'puntaje' => $score,
                ]);
        }
    }

    private function ensureSingleCorrectOption(array $options): void
    {
        $correct = collect($options)->filter(fn (array $option): bool => (bool) ($option['es_correcta'] ?? false))->count();

        if ($correct !== 1) {
            throw new RuntimeException('Cada pregunta debe tener exactamente una opcion correcta.');
        }
    }

    private function validateExamCanBeEnabled(ExamenModel $exam): void
    {
        $subjects = $exam->materiasPorcentaje;

        if ($subjects->isEmpty()) {
            throw new RuntimeException('El examen debe tener materias asociadas.');
        }

        $this->ensurePercentagesSumOneHundred($subjects->map(fn (ExamenMateriaPorcentajeModel $subject): array => [
            'porcentaje' => $subject->porcentaje,
        ])->all());

        if ($exam->preguntas->isEmpty()) {
            throw new RuntimeException('El examen debe tener preguntas registradas.');
        }

        $this->ensureEverySubjectHasQuestion($exam);
        $this->ensureQuestionsHaveValidOptions($exam->preguntas->where('activa', true));
    }

    private function ensureEverySubjectHasQuestion(ExamenModel $exam): void
    {
        $questionSubjectIds = $exam->preguntas
            ->where('activa', true)
            ->pluck('materia_id')
            ->unique()
            ->values();

        $missing = $exam->materiasPorcentaje
            ->pluck('materia_id')
            ->diff($questionSubjectIds);

        if ($missing->isNotEmpty()) {
            $subjects = $exam->materiasPorcentaje
                ->whereIn('materia_id', $missing)
                ->map(fn (ExamenMateriaPorcentajeModel $subject): string => $subject->materia?->nombre ?? 'Materia '.$subject->materia_id)
                ->values()
                ->implode(', ');

            throw new RuntimeException('Cada materia asociada debe tener al menos una pregunta activa. Faltan preguntas para: '.$subjects.'.');
        }
    }

    private function ensureQuestionsHaveValidOptions(Collection $questions): void
    {
        foreach ($questions as $question) {
            if ($question->opciones->count() < 2) {
                throw new RuntimeException('Cada pregunta debe tener al menos dos opciones de respuesta.');
            }

            $this->ensureSingleCorrectOption($question->opciones->map(fn (OpcionPreguntaModel $option): array => [
                'es_correcta' => $option->es_correcta,
            ])->all());
        }
    }

    private function ensureCanEditExam(ExamenModel $exam): void
    {
        if ($exam->habilitado) {
            throw new RuntimeException('No se puede modificar la estructura de un examen habilitado.');
        }
    }
}

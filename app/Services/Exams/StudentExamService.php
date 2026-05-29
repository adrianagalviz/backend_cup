<?php

namespace App\Services\Exams;

use App\Models\AlumnoModel;
use App\Models\ExamenMateriaPorcentajeModel;
use App\Models\ExamenModel;
use App\Models\IntentoExamenModel;
use App\Models\NotaExamenMateriaModel;
use App\Models\OpcionPreguntaModel;
use App\Models\PreguntaModel;
use App\Models\UsuarioModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StudentExamService
{
    public function enabledExamsForStudent(UsuarioModel $user): Collection
    {
        $student = $this->authenticatedStudent($user);

        return ExamenModel::query()
            ->with(['gestionAcademica', 'materiasPorcentaje.materia'])
            ->where('gestion_academica_id', $student->gestion_academica_id)
            ->where('habilitado', true)
            ->where(function (Builder $query): void {
                $now = $this->now();
                $query
                    ->whereNull('fecha_inicio')
                    ->orWhere('fecha_inicio', '<=', $now);
            })
            ->where(function (Builder $query): void {
                $now = $this->now();
                $query
                    ->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>=', $now);
            })
            ->orderBy('numero_parcial')
            ->get()
            ->map(fn (ExamenModel $exam) => [
                ...$this->formatEnabledExam($exam),
                'ya_respondio' => $this->studentAlreadyAnswered($student->id, $exam->id),
            ]);
    }

    public function showExam(UsuarioModel $user, int $examId): array
    {
        $student = $this->authenticatedStudent($user);
        $exam = $this->enabledExamForStudent($student, $examId);

        if ($this->studentAlreadyAnswered($student->id, $exam->id)) {
            throw new RuntimeException('El alumno ya respondio este examen.');
        }

        return $this->formatExamForStudent($exam);
    }

    public function submitAnswers(UsuarioModel $user, int $examId, array $answers): array
    {
        return DB::transaction(function () use ($user, $examId, $answers): array {
            $student = $this->authenticatedStudent($user);
            $exam = $this->enabledExamForStudent($student, $examId);

            if ($this->studentAlreadyAnswered($student->id, $exam->id)) {
                throw new RuntimeException('El alumno ya respondio este examen.');
            }

            $questions = $exam->preguntas->where('activa', true)->values();
            $this->validateAnswers($questions, $answers);

            $attemptId = DB::table('intento_examen')->insertGetId([
                'alumno_id' => $student->id,
                'examen_id' => $exam->id,
                'fecha_inicio' => now(),
                'fecha_fin' => now(),
                'estado' => 'finalizado',
                'nota_total' => 0,
                'creado_en' => now(),
            ]);

            $attempt = $this->findAttempt($attemptId);
            $answersByQuestion = collect($answers)->keyBy('pregunta_id');

            foreach ($questions as $question) {
                $answer = $answersByQuestion->get($question->id);
                $option = $question->opciones->firstWhere('id', (int) $answer['opcion_pregunta_id']);

                DB::table('respuesta_alumno')->insert([
                    'intento_examen_id' => $attempt->id,
                    'pregunta_id' => $question->id,
                    'opcion_pregunta_id' => $option->id,
                    'es_correcta' => (bool) $option->es_correcta,
                    'respondido_en' => now(),
                ]);
            }

            $totalScore = $this->calculateAndStoreScores($attempt->id, $exam);

            DB::table('intento_examen')->where('id', $attempt->id)->update([
                'nota_total' => $totalScore,
            ]);

            DB::table('nota_parcial')->insert([
                'alumno_id' => $student->id,
                'examen_id' => $exam->id,
                'intento_examen_id' => $attempt->id,
                'numero_parcial' => $exam->numero_parcial,
                'nota' => $totalScore,
                'registrado_en' => now(),
            ]);

            return $this->formatResult($this->findAttempt($attempt->id));
        });
    }

    public function result(UsuarioModel $user, int $examId): array
    {
        $student = $this->authenticatedStudent($user);
        $attempt = IntentoExamenModel::query()
            ->with(['examen.gestionAcademica', 'notasMateria.materia', 'notaParcial'])
            ->where('alumno_id', $student->id)
            ->where('examen_id', $examId)
            ->where('estado', 'finalizado')
            ->first();

        if (! $attempt) {
            throw new RuntimeException('El alumno aun no tiene resultado para este examen.');
        }

        if ((int) $attempt->examen->gestion_academica_id !== (int) $student->gestion_academica_id) {
            throw new RuntimeException('El resultado no pertenece a la gestion academica del alumno.');
        }

        return $this->formatResult($attempt);
    }

    private function calculateAndStoreScores(int $attemptId, ExamenModel $exam): float
    {
        $responses = DB::table('respuesta_alumno')
            ->join('pregunta', 'pregunta.id', '=', 'respuesta_alumno.pregunta_id')
            ->where('respuesta_alumno.intento_examen_id', $attemptId)
            ->select('pregunta.materia_id', 'respuesta_alumno.es_correcta')
            ->get()
            ->groupBy('materia_id');

        $totalScore = 0.0;

        foreach ($exam->materiasPorcentaje as $subject) {
            $subjectResponses = $responses->get($subject->materia_id, collect());
            $totalQuestions = max(1, $exam->preguntas->where('activa', true)->where('materia_id', $subject->materia_id)->count());
            $correctAnswers = $subjectResponses->filter(fn ($response): bool => (bool) $response->es_correcta)->count();
            $subjectScore = round(($correctAnswers / $totalQuestions) * 100, 2);
            $weightedScore = round($subjectScore * ((float) $subject->porcentaje / 100), 2);
            $totalScore += $weightedScore;

            DB::table('nota_examen_materia')->insert([
                'intento_examen_id' => $attemptId,
                'materia_id' => $subject->materia_id,
                'nota' => $subjectScore,
                'porcentaje_aplicado' => $subject->porcentaje,
                'nota_ponderada' => $weightedScore,
            ]);
        }

        return round($totalScore, 2);
    }

    private function validateAnswers(Collection $questions, array $answers): void
    {
        if ($questions->isEmpty()) {
            throw new RuntimeException('El examen no tiene preguntas activas.');
        }

        $answersByQuestion = collect($answers)->keyBy('pregunta_id');

        if ($answersByQuestion->count() !== count($answers)) {
            throw new RuntimeException('No se puede responder una pregunta mas de una vez.');
        }

        $missing = $questions->pluck('id')->diff($answersByQuestion->keys());

        if ($missing->isNotEmpty()) {
            throw new RuntimeException('Debe responder todas las preguntas activas del examen.');
        }

        $extra = $answersByQuestion->keys()->diff($questions->pluck('id'));

        if ($extra->isNotEmpty()) {
            throw new RuntimeException('Todas las respuestas deben pertenecer a preguntas activas del examen.');
        }

        foreach ($questions as $question) {
            $answer = $answersByQuestion->get($question->id);
            $option = $question->opciones->firstWhere('id', (int) $answer['opcion_pregunta_id']);

            if (! $option) {
                throw new RuntimeException('Una opcion seleccionada no pertenece a la pregunta indicada.');
            }
        }
    }

    private function enabledExamForStudent(AlumnoModel $student, int $examId): ExamenModel
    {
        $exam = ExamenModel::query()
            ->with(['gestionAcademica', 'materiasPorcentaje.materia', 'preguntas.materia', 'preguntas.opciones'])
            ->where('id', $examId)
            ->where('gestion_academica_id', $student->gestion_academica_id)
            ->where('habilitado', true)
            ->first();

        if (! $exam) {
            throw new RuntimeException('Examen no encontrado o no habilitado para el alumno.');
        }

        $now = $this->now();

        if ($exam->fecha_inicio && $now->lessThan(Carbon::parse($exam->fecha_inicio, config('app.timezone')))) {
            throw new RuntimeException('El examen aun no esta disponible.');
        }

        if ($exam->fecha_fin && $now->greaterThan(Carbon::parse($exam->fecha_fin, config('app.timezone')))) {
            throw new RuntimeException('El examen ya no esta disponible.');
        }

        return $exam;
    }

    private function authenticatedStudent(UsuarioModel $user): AlumnoModel
    {
        if ($user->rol?->nombre !== 'alumno' || ! $user->alumno) {
            throw new RuntimeException('El usuario autenticado debe ser alumno.');
        }

        return $user->alumno;
    }

    private function studentAlreadyAnswered(int $studentId, int $examId): bool
    {
        return DB::table('intento_examen')
            ->where('alumno_id', $studentId)
            ->where('examen_id', $examId)
            ->where('estado', 'finalizado')
            ->exists();
    }

    private function findAttempt(int $id): IntentoExamenModel
    {
        return IntentoExamenModel::query()
            ->with(['examen.gestionAcademica', 'notasMateria.materia', 'notaParcial'])
            ->findOrFail($id);
    }

    private function formatEnabledExam(ExamenModel $exam): array
    {
        return [
            'id' => $exam->id,
            'numero_parcial' => $exam->numero_parcial,
            'titulo' => $exam->titulo,
            'descripcion' => $exam->descripcion,
            'fecha_inicio' => $exam->fecha_inicio,
            'fecha_fin' => $exam->fecha_fin,
            'gestion_academica' => [
                'id' => $exam->gestionAcademica?->id,
                'anio' => $exam->gestionAcademica?->anio,
                'numero_gestion' => $exam->gestionAcademica?->numero_gestion,
                'nombre' => $exam->gestionAcademica?->nombre,
            ],
            'materias' => $exam->materiasPorcentaje
                ->map(fn (ExamenMateriaPorcentajeModel $subject) => [
                    'materia_id' => $subject->materia_id,
                    'materia' => $subject->materia?->nombre,
                    'porcentaje' => $subject->porcentaje,
                ])
                ->values(),
        ];
    }

    private function formatExamForStudent(ExamenModel $exam): array
    {
        return [
            ...$this->formatEnabledExam($exam),
            'preguntas' => $exam->preguntas
                ->where('activa', true)
                ->sortBy('id')
                ->map(fn (PreguntaModel $question) => [
                    'id' => $question->id,
                    'materia' => [
                        'id' => $question->materia?->id,
                        'nombre' => $question->materia?->nombre,
                    ],
                    'enunciado' => $question->enunciado,
                    'tipo_pregunta' => $question->tipo_pregunta,
                    'puntaje' => $question->puntaje,
                    'opciones' => $question->opciones
                        ->sortBy('orden')
                        ->map(fn (OpcionPreguntaModel $option) => [
                            'id' => $option->id,
                            'texto_opcion' => $option->texto_opcion,
                            'orden' => $option->orden,
                        ])
                        ->values(),
                ])
                ->values(),
        ];
    }

    private function formatResult(IntentoExamenModel $attempt): array
    {
        return [
            'intento_id' => $attempt->id,
            'estado' => $attempt->estado,
            'fecha_inicio' => $attempt->fecha_inicio,
            'fecha_fin' => $attempt->fecha_fin,
            'nota_total' => $attempt->nota_total,
            'nota_parcial' => [
                'id' => $attempt->notaParcial?->id,
                'numero_parcial' => $attempt->notaParcial?->numero_parcial,
                'nota' => $attempt->notaParcial?->nota,
                'registrado_en' => $attempt->notaParcial?->registrado_en,
            ],
            'examen' => [
                'id' => $attempt->examen?->id,
                'numero_parcial' => $attempt->examen?->numero_parcial,
                'titulo' => $attempt->examen?->titulo,
                'gestion_academica_id' => $attempt->examen?->gestion_academica_id,
            ],
            'notas_por_materia' => $attempt->notasMateria
                ->map(fn (NotaExamenMateriaModel $score) => [
                    'materia_id' => $score->materia_id,
                    'materia' => $score->materia?->nombre,
                    'nota' => $score->nota,
                    'porcentaje_aplicado' => $score->porcentaje_aplicado,
                    'nota_ponderada' => $score->nota_ponderada,
                ])
                ->values(),
        ];
    }

    private function now(): Carbon
    {
        return now(config('app.timezone'));
    }
}

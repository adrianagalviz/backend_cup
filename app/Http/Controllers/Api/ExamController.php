<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Models\ExamenModel;
use App\Services\Exams\ExamManagementService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

class ExamController extends Controller
{
    public function __construct(
        private readonly ExamManagementService $exams,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'gestion_academica_id' => ['nullable', 'integer', 'exists:gestion_academica,id'],
            'numero_parcial' => ['nullable', 'integer', Rule::in([1, 2, 3])],
            'habilitado' => ['nullable', Rule::in(['true', 'false', '1', '0'])],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $exams = $this->exams->listExams($validator->validated());

        return $this->paginatedResponse('Examenes obtenidos correctamente.', $exams);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'gestion_academica_id' => ['required', 'integer', 'exists:gestion_academica,id'],
            'numero_parcial' => [
                'required',
                'integer',
                Rule::in([1, 2, 3]),
                Rule::unique('examen', 'numero_parcial')->where(fn ($query) => $query
                    ->where('gestion_academica_id', $request->input('gestion_academica_id'))),
            ],
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $exam = $this->exams->createExam(
            $request->attributes->get('usuario_autenticado'),
            $validator->validated()
        );

        return ApiResponse::success('Examen creado correctamente.', [
            'examen' => $this->exams->formatExam($exam),
        ], 201);
    }

    public function subjects(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'materias' => ['required', 'array', 'min:1'],
            'materias.*.materia_id' => ['required', 'integer', 'exists:materia,id', 'distinct'],
            'materias.*.porcentaje' => ['required', 'numeric', 'min:0', 'max:100'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $exam = $this->exams->syncSubjects($id, $validator->validated()['materias']);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Examen no encontrado.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Materias y porcentajes del examen registrados correctamente.', [
            'examen' => $this->exams->formatExam($exam),
        ]);
    }

    public function question(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'materia_id' => ['required', 'integer', 'exists:materia,id'],
            'enunciado' => ['required', 'string'],
            'activa' => ['nullable', 'boolean'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $question = $this->exams->createQuestion($id, $validator->validated());
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Examen no encontrado.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Pregunta creada correctamente.', [
            'pregunta' => $this->exams->formatQuestion($question),
        ], 201);
    }

    public function options(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'opciones' => ['required', 'array', 'min:2'],
            'opciones.*.texto_opcion' => ['required', 'string'],
            'opciones.*.es_correcta' => ['required', 'boolean'],
            'opciones.*.orden' => ['nullable', 'integer', 'min:1', 'distinct'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $question = $this->exams->syncOptions($id, $validator->validated()['opciones']);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Pregunta no encontrada.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Opciones de respuesta registradas correctamente.', [
            'pregunta' => $this->exams->formatQuestion($question),
        ]);
    }

    public function enable(int $id): JsonResponse
    {
        try {
            $exam = $this->exams->enableExam($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Examen no encontrado.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Examen habilitado correctamente.', [
            'examen' => $this->exams->formatExam($exam),
        ]);
    }

    public function disable(int $id): JsonResponse
    {
        try {
            $exam = $this->exams->disableExam($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Examen no encontrado.', [], 404);
        }

        return ApiResponse::success('Examen deshabilitado correctamente.', [
            'examen' => $this->exams->formatExam($exam),
        ]);
    }

    private function paginatedResponse(string $message, $paginator): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'mensaje' => $message,
            'datos' => collect($paginator->items())
                ->map(fn (ExamenModel $exam) => $this->exams->formatExam($exam))
                ->values(),
            'meta' => [
                'pagina_actual' => $paginator->currentPage(),
                'por_pagina' => $paginator->perPage(),
                'total' => $paginator->total(),
                'ultima_pagina' => $paginator->lastPage(),
            ],
        ]);
    }

    private function messages(): array
    {
        return [
            'gestion_academica_id.required' => 'La gestion academica es obligatoria.',
            'gestion_academica_id.exists' => 'La gestion academica indicada no existe.',
            'numero_parcial.required' => 'El numero de parcial es obligatorio.',
            'numero_parcial.in' => 'El parcial debe ser 1, 2 o 3.',
            'numero_parcial.unique' => 'Ya existe un examen para ese parcial en la gestion academica.',
            'titulo.required' => 'El titulo del examen es obligatorio.',
            'fecha_fin.after_or_equal' => 'La fecha fin debe ser mayor o igual a la fecha inicio.',
            'materias.required' => 'Las materias del examen son obligatorias.',
            'materias.*.materia_id.required' => 'La materia es obligatoria.',
            'materias.*.materia_id.exists' => 'Una de las materias indicadas no existe.',
            'materias.*.materia_id.distinct' => 'No se puede duplicar una materia en el mismo examen.',
            'materias.*.porcentaje.required' => 'El porcentaje de la materia es obligatorio.',
            'materias.*.porcentaje.numeric' => 'El porcentaje de la materia debe ser numerico.',
            'materia_id.required' => 'La materia de la pregunta es obligatoria.',
            'enunciado.required' => 'El enunciado de la pregunta es obligatorio.',
            'opciones.required' => 'Las opciones de respuesta son obligatorias.',
            'opciones.min' => 'Cada pregunta debe tener al menos dos opciones.',
            'opciones.*.texto_opcion.required' => 'El texto de la opcion es obligatorio.',
            'opciones.*.es_correcta.required' => 'Debe indicar si la opcion es correcta.',
            'opciones.*.orden.distinct' => 'El orden de las opciones no puede repetirse.',
        ];
    }
}

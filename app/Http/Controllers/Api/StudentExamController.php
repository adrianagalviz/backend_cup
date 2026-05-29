<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Services\Exams\StudentExamService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class StudentExamController extends Controller
{
    public function __construct(
        private readonly StudentExamService $exams,
    ) {
    }

    public function enabled(Request $request): JsonResponse
    {
        try {
            $exams = $this->exams->enabledExamsForStudent($request->attributes->get('usuario_autenticado'));
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Examenes habilitados obtenidos correctamente.', [
            'examenes' => $exams,
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $exam = $this->exams->showExam($request->attributes->get('usuario_autenticado'), $id);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Examen obtenido correctamente.', [
            'examen' => $exam,
        ]);
    }

    public function answer(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'respuestas' => ['required', 'array', 'min:1'],
            'respuestas.*.pregunta_id' => ['required', 'integer', 'exists:pregunta,id', 'distinct'],
            'respuestas.*.opcion_pregunta_id' => ['required', 'integer', 'exists:opcion_pregunta,id'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $result = $this->exams->submitAnswers(
                $request->attributes->get('usuario_autenticado'),
                $id,
                $validator->validated()['respuestas']
            );
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Respuestas del examen registradas correctamente.', [
            'resultado' => $result,
        ], 201);
    }

    public function result(Request $request, int $id): JsonResponse
    {
        try {
            $result = $this->exams->result($request->attributes->get('usuario_autenticado'), $id);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Resultado del examen obtenido correctamente.', [
            'resultado' => $result,
        ]);
    }

    private function messages(): array
    {
        return [
            'respuestas.required' => 'Las respuestas del examen son obligatorias.',
            'respuestas.array' => 'Las respuestas deben enviarse como arreglo.',
            'respuestas.min' => 'Debe enviar al menos una respuesta.',
            'respuestas.*.pregunta_id.required' => 'La pregunta es obligatoria.',
            'respuestas.*.pregunta_id.exists' => 'Una de las preguntas indicadas no existe.',
            'respuestas.*.pregunta_id.distinct' => 'No se puede responder una pregunta mas de una vez.',
            'respuestas.*.opcion_pregunta_id.required' => 'La opcion seleccionada es obligatoria.',
            'respuestas.*.opcion_pregunta_id.exists' => 'Una de las opciones indicadas no existe.',
        ];
    }
}

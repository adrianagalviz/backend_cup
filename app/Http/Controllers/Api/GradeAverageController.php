<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Models\PromedioFinalModel;
use App\Services\Grades\GradeAverageService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

class GradeAverageController extends Controller
{
    public function __construct(
        private readonly GradeAverageService $grades,
    ) {
    }

    public function calculate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'alumno_id' => ['nullable', 'integer', 'exists:alumno,id'],
            'gestion_academica_id' => ['required_without:alumno_id', 'nullable', 'integer', 'exists:gestion_academica,id'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $result = $this->grades->calculate($validator->validated());
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Alumno o gestion academica no encontrada.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Promedio final calculado correctamente.', $result);
    }

    public function notesByStudent(Request $request, int $id): JsonResponse
    {
        try {
            $result = $this->grades->notesForStudent($request->attributes->get('usuario_autenticado'), $id);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Alumno no encontrado.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], $this->runtimeStatus($exception));
        }

        return ApiResponse::success('Notas del alumno obtenidas correctamente.', $result);
    }

    public function averages(Request $request): JsonResponse
    {
        return $this->averageList($request, null, 'Promedios finales obtenidos correctamente.');
    }

    public function approved(Request $request): JsonResponse
    {
        return $this->averageList($request, 'aprobado', 'Promedios aprobados obtenidos correctamente.');
    }

    public function failed(Request $request): JsonResponse
    {
        return $this->averageList($request, 'reprobado', 'Promedios reprobados obtenidos correctamente.');
    }

    private function averageList(Request $request, ?string $state, string $message): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'gestion_academica_id' => ['nullable', 'integer', 'exists:gestion_academica,id'],
            'alumno_id' => ['nullable', 'integer', 'exists:alumno,id'],
            'estado_final' => ['nullable', Rule::in(['aprobado', 'reprobado'])],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $filters = $validator->validated();

        try {
            $averages = match ($state) {
                'aprobado' => $this->grades->approved($request->attributes->get('usuario_autenticado'), $filters),
                'reprobado' => $this->grades->failed($request->attributes->get('usuario_autenticado'), $filters),
                default => $this->grades->listAverages($request->attributes->get('usuario_autenticado'), $filters),
            };
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], $this->runtimeStatus($exception));
        }

        return response()->json([
            'ok' => true,
            'mensaje' => $message,
            'datos' => collect($averages->items())
                ->map(fn (PromedioFinalModel $average) => $this->grades->formatAverage($average))
                ->values(),
            'meta' => [
                'pagina_actual' => $averages->currentPage(),
                'por_pagina' => $averages->perPage(),
                'total' => $averages->total(),
                'ultima_pagina' => $averages->lastPage(),
            ],
        ]);
    }

    private function runtimeStatus(RuntimeException $exception): int
    {
        return $exception->getCode() === 403 ? 403 : 422;
    }

    private function messages(): array
    {
        return [
            'alumno_id.exists' => 'El alumno indicado no existe.',
            'gestion_academica_id.required_without' => 'La gestion academica es obligatoria cuando no se indica alumno.',
            'gestion_academica_id.exists' => 'La gestion academica indicada no existe.',
        ];
    }
}

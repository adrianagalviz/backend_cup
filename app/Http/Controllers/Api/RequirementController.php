<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Models\PostulanteModel;
use App\Services\Documents\ApplicantDocumentService;
use App\Services\Documents\RequirementReviewService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

class RequirementController extends Controller
{
    public function __construct(
        private readonly RequirementReviewService $requirements,
        private readonly ApplicantDocumentService $documents,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'estado_requisitos' => ['nullable', Rule::in(['pendiente', 'aprobado', 'rechazado'])],
            'gestion_academica_id' => ['nullable', 'integer', 'exists:gestion_academica,id'],
            'buscar' => ['nullable', 'string', 'max:150'],
            'documento' => ['nullable', Rule::in(['con_documento', 'sin_documento'])],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $requirements = $this->requirements->listRequirements($validator->validated());

        return response()->json([
            'ok' => true,
            'mensaje' => 'Requisitos obtenidos correctamente.',
            'datos' => collect($requirements->items())
                ->map(fn (PostulanteModel $postulante) => $this->requirements->formatRequirement($postulante))
                ->values(),
            'meta' => [
                'pagina_actual' => $requirements->currentPage(),
                'por_pagina' => $requirements->perPage(),
                'total' => $requirements->total(),
                'ultima_pagina' => $requirements->lastPage(),
            ],
        ]);
    }

    public function validateRequirement(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'estado_revision' => ['required', Rule::in(['aprobado', 'rechazado'])],
            'observacion' => ['nullable', 'string', 'max:500'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $this->documents->validateRequirements(
                $id,
                $validator->validated()['estado_revision'],
                $validator->validated()['observacion'] ?? null
            );

            $postulante = $this->requirements->findRequirement($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Postulante no encontrado.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Requisitos validados correctamente.', [
            'requisito' => $this->requirements->formatRequirement($postulante),
        ]);
    }

    private function messages(): array
    {
        return [
            'estado_requisitos.in' => 'El estado de requisitos indicado no es valido.',
            'gestion_academica_id.exists' => 'La gestion academica indicada no existe.',
            'documento.in' => 'El filtro de documento indicado no es valido.',
            'estado_revision.required' => 'El estado de revision es obligatorio.',
            'estado_revision.in' => 'El estado de revision debe ser aprobado o rechazado.',
        ];
    }
}

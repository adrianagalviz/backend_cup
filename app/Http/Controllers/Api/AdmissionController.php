<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Services\Admissions\CareerAssignmentService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdmissionController extends Controller
{
    public function __construct(
        private readonly CareerAssignmentService $assignments,
    ) {
    }

    public function assignmentSummary(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'gestion_academica_id' => ['required', 'integer', 'exists:gestion_academica,id'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $data = $validator->validated();

        return ApiResponse::success(
            'Asignacion final de carreras obtenida correctamente.',
            $this->assignments->summaryByGestion((int) $data['gestion_academica_id'])
        );
    }

    public function assignCareers(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'gestion_academica_id' => ['required', 'integer', 'exists:gestion_academica,id'],
            'reasignar' => ['nullable', 'boolean'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $data = $validator->validated();
        $result = $this->assignments->assignByGestion(
            (int) $data['gestion_academica_id'],
            (bool) ($data['reasignar'] ?? false)
        );

        return ApiResponse::success('Asignacion final de carreras procesada correctamente.', $result);
    }

    private function messages(): array
    {
        return [
            'gestion_academica_id.required' => 'La gestion academica es obligatoria.',
            'gestion_academica_id.exists' => 'La gestion academica indicada no existe.',
        ];
    }
}

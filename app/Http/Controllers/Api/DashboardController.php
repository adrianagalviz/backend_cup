<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Services\Dashboard\AdminDashboardService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AdminDashboardService $dashboard,
    ) {
    }

    public function summary(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), $this->rules(), $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        return ApiResponse::success('Resumen de dashboard obtenido correctamente.', $this->dashboard->summary($validator->validated()));
    }

    public function attendance(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), $this->rules(), $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        return ApiResponse::success('Indicadores de asistencia obtenidos correctamente.', [
            'asistencia' => $this->dashboard->attendance($validator->validated()),
        ]);
    }

    public function quotas(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), $this->rules(), $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        return ApiResponse::success('Indicadores de cupos obtenidos correctamente.', [
            'cupos' => $this->dashboard->quotas($validator->validated()),
        ]);
    }

    public function exams(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), $this->rules(), $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        return ApiResponse::success('Indicadores de examenes obtenidos correctamente.', [
            'examenes' => $this->dashboard->exams($validator->validated()),
        ]);
    }

    private function rules(): array
    {
        return [
            'gestion_academica_id' => ['nullable', 'integer', 'min:1'],
        ];
    }

    private function messages(): array
    {
        return [
            'gestion_academica_id.integer' => 'La gestion academica debe ser numerica.',
            'gestion_academica_id.min' => 'La gestion academica debe ser valida.',
        ];
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Models\PeriodoModel;
use App\Models\TurnoModel;
use App\Services\Academic\ScheduleCatalogService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

class ScheduleCatalogController extends Controller
{
    public function __construct(
        private readonly ScheduleCatalogService $catalog,
    ) {
    }

    public function days(): JsonResponse
    {
        $days = $this->catalog->listDays();

        return ApiResponse::success('Dias obtenidos correctamente.', [
            'dias' => $days->map(fn ($day) => $this->catalog->formatDay($day))->values(),
        ]);
    }

    public function createShift(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'max:50', 'unique:turno,nombre'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i'],
            'activo' => ['nullable', 'boolean'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $shift = $this->catalog->createShift($validator->validated());
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Turno creado correctamente.', [
            'turno' => $this->catalog->formatShift($shift),
        ], 201);
    }

    public function shifts(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'activo' => ['nullable', Rule::in(['true', 'false', '1', '0'])],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $shifts = $this->catalog->listShifts($validator->validated());

        return $this->paginatedResponse(
            'Turnos obtenidos correctamente.',
            $shifts,
            fn (TurnoModel $shift) => $this->catalog->formatShift($shift)
        );
    }

    public function updateShift(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => ['sometimes', 'string', 'max:50', Rule::unique('turno', 'nombre')->ignore($id)],
            'hora_inicio' => ['sometimes', 'date_format:H:i'],
            'hora_fin' => ['sometimes', 'date_format:H:i'],
            'activo' => ['sometimes', 'boolean'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $shift = $this->catalog->updateShift($id, $validator->validated());
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Turno no encontrado.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Turno actualizado correctamente.', [
            'turno' => $this->catalog->formatShift($shift),
        ]);
    }

    public function createPeriod(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'turno_id' => ['required', 'integer', 'exists:turno,id'],
            'numero_periodo' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('periodo', 'numero_periodo')->where(fn ($query) => $query
                    ->where('turno_id', $request->input('turno_id'))),
            ],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i'],
            'activo' => ['nullable', 'boolean'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $period = $this->catalog->createPeriod($validator->validated());
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Turno no encontrado.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Periodo creado correctamente.', [
            'periodo' => $this->catalog->formatPeriod($period),
        ], 201);
    }

    public function periods(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'turno_id' => ['nullable', 'integer', 'exists:turno,id'],
            'activo' => ['nullable', Rule::in(['true', 'false', '1', '0'])],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $periods = $this->catalog->listPeriods($validator->validated());

        return $this->paginatedResponse(
            'Periodos obtenidos correctamente.',
            $periods,
            fn (PeriodoModel $period) => $this->catalog->formatPeriod($period)
        );
    }

    private function paginatedResponse(string $message, $paginator, callable $formatter): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'mensaje' => $message,
            'datos' => collect($paginator->items())->map($formatter)->values(),
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
            'nombre.required' => 'El nombre del turno es obligatorio.',
            'nombre.unique' => 'El nombre del turno ya esta registrado.',
            'hora_inicio.required' => 'La hora de inicio es obligatoria.',
            'hora_inicio.date_format' => 'La hora de inicio debe tener formato HH:MM.',
            'hora_fin.required' => 'La hora de fin es obligatoria.',
            'hora_fin.date_format' => 'La hora de fin debe tener formato HH:MM.',
            'turno_id.required' => 'El turno es obligatorio.',
            'turno_id.exists' => 'El turno indicado no existe.',
            'numero_periodo.required' => 'El numero de periodo es obligatorio.',
            'numero_periodo.unique' => 'El numero de periodo ya existe para ese turno.',
        ];
    }
}

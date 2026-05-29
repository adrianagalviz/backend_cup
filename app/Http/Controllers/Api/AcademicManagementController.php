<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Models\CarreraModel;
use App\Models\CupoCarreraModel;
use App\Models\GestionAcademicaModel;
use App\Services\Academic\AcademicManagementService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

class AcademicManagementController extends Controller
{
    public function __construct(
        private readonly AcademicManagementService $academic,
    ) {
    }

    public function listGestiones(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), $this->listRules(['activa']));

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $gestiones = $this->academic->listGestiones($validator->validated());

        return $this->paginatedResponse(
            'Gestiones academicas obtenidas correctamente.',
            $gestiones,
            fn (GestionAcademicaModel $gestion) => $this->academic->formatGestion($gestion)
        );
    }

    public function createGestion(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'anio' => ['required', 'integer', 'min:2000', 'max:2100'],
            'numero_gestion' => ['required', 'integer', Rule::in([1, 2])],
            'nombre' => ['required', 'string', 'max:100'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'activa' => ['nullable', 'boolean'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $gestion = $this->academic->createGestion($validator->validated());

        return ApiResponse::success('Gestion academica creada correctamente.', [
            'gestion' => $this->academic->formatGestion($gestion),
        ], 201);
    }

    public function currentGestion(): JsonResponse
    {
        $gestion = $this->academic->currentGestion();

        if (! $gestion) {
            return ApiResponse::error('No existe una gestion academica activa.', [], 404);
        }

        return ApiResponse::success('Gestion academica actual obtenida correctamente.', [
            'gestion' => $this->academic->formatGestion($gestion),
        ]);
    }

    public function listCareers(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), $this->listRules(['activa', 'buscar']));

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $careers = $this->academic->listCareers($validator->validated());

        return $this->paginatedResponse(
            'Carreras obtenidas correctamente.',
            $careers,
            fn (CarreraModel $career) => $this->academic->formatCareer($career)
        );
    }

    public function createCareer(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'max:150', 'unique:carrera,nombre'],
            'descripcion' => ['nullable', 'string'],
            'activa' => ['nullable', 'boolean'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $career = $this->academic->createCareer($validator->validated());

        return ApiResponse::success('Carrera creada correctamente.', [
            'carrera' => $this->academic->formatCareer($career),
        ], 201);
    }

    public function updateCareer(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => ['sometimes', 'string', 'max:150', Rule::unique('carrera', 'nombre')->ignore($id)],
            'descripcion' => ['nullable', 'string'],
            'activa' => ['sometimes', 'boolean'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $career = $this->academic->updateCareer($id, $validator->validated());
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Carrera no encontrada.', [], 404);
        }

        return ApiResponse::success('Carrera actualizada correctamente.', [
            'carrera' => $this->academic->formatCareer($career),
        ]);
    }

    public function listQuotas(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'carrera_id' => ['nullable', 'integer', 'exists:carrera,id'],
            'gestion_academica_id' => ['nullable', 'integer', 'exists:gestion_academica,id'],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $quotas = $this->academic->listQuotas($validator->validated());

        return $this->paginatedResponse(
            'Cupos obtenidos correctamente.',
            $quotas,
            fn (CupoCarreraModel $quota) => $this->academic->formatQuota($quota)
        );
    }

    public function createQuota(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'carrera_id' => [
                'required',
                'integer',
                'exists:carrera,id',
                Rule::unique('cupo_carrera', 'carrera_id')->where(fn ($query) => $query
                    ->where('gestion_academica_id', $request->input('gestion_academica_id'))),
            ],
            'gestion_academica_id' => ['required', 'integer', 'exists:gestion_academica,id'],
            'cantidad_cupos' => ['required', 'integer', 'min:0'],
        ], $this->quotaMessages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $quota = $this->academic->createQuota($validator->validated());
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Cupo creado correctamente.', [
            'cupo' => $this->academic->formatQuota($quota),
        ], 201);
    }

    public function updateQuota(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'carrera_id' => ['sometimes', 'integer', 'exists:carrera,id'],
            'gestion_academica_id' => ['sometimes', 'integer', 'exists:gestion_academica,id'],
            'cantidad_cupos' => ['sometimes', 'integer', 'min:0'],
        ], $this->quotaMessages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $quota = $this->academic->updateQuota($id, $validator->validated());
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Cupo no encontrado.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Cupo actualizado correctamente.', [
            'cupo' => $this->academic->formatQuota($quota),
        ]);
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

    private function listRules(array $allowed): array
    {
        $rules = [
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];

        if (in_array('activa', $allowed, true)) {
            $rules['activa'] = ['nullable', Rule::in(['true', 'false', '1', '0'])];
        }

        if (in_array('buscar', $allowed, true)) {
            $rules['buscar'] = ['nullable', 'string', 'max:150'];
        }

        return $rules;
    }

    private function messages(): array
    {
        return [
            'anio.required' => 'El anio de la gestion academica es obligatorio.',
            'anio.integer' => 'El anio debe ser numerico.',
            'numero_gestion.required' => 'El numero de gestion es obligatorio.',
            'numero_gestion.in' => 'La gestion debe ser 1 o 2.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'El nombre ya esta registrado.',
            'fecha_fin.after_or_equal' => 'La fecha fin debe ser mayor o igual a la fecha inicio.',
        ];
    }

    private function quotaMessages(): array
    {
        return [
            ...$this->messages(),
            'carrera_id.required' => 'La carrera es obligatoria.',
            'carrera_id.exists' => 'La carrera indicada no existe.',
            'carrera_id.unique' => 'La carrera ya tiene cupo registrado para esa gestion academica.',
            'gestion_academica_id.required' => 'La gestion academica es obligatoria.',
            'gestion_academica_id.exists' => 'La gestion academica indicada no existe.',
            'cantidad_cupos.required' => 'La cantidad de cupos es obligatoria.',
            'cantidad_cupos.integer' => 'La cantidad de cupos debe ser numerica.',
            'cantidad_cupos.min' => 'La cantidad de cupos no puede ser negativa.',
        ];
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Models\PostulanteModel;
use App\Services\Applicants\ApplicantService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

class ApplicantController extends Controller
{
    public function __construct(
        private readonly ApplicantService $applicants,
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->storeRules(), $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $postulante = $this->applicants->register($validator->validated());
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success(
            'Postulante registrado correctamente.',
            ['postulante' => $this->applicants->formatDetail($postulante)],
            201
        );
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'gestion_academica_id' => ['nullable', 'integer', 'exists:gestion_academica,id'],
            'estado' => ['nullable', Rule::in(['registrado', 'pendiente_pago', 'pagado', 'habilitado_alumno', 'rechazado'])],
            'ci' => ['nullable', 'string', 'max:20'],
            'nombre' => ['nullable', 'string', 'max:100'],
            'buscar' => ['nullable', 'string', 'max:150'],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $postulantes = $this->applicants->list($validator->validated());

        return response()->json([
            'ok' => true,
            'mensaje' => 'Postulantes obtenidos correctamente.',
            'datos' => collect($postulantes->items())
                ->map(fn (PostulanteModel $postulante) => $this->applicants->formatListItem($postulante))
                ->values(),
            'meta' => [
                'pagina_actual' => $postulantes->currentPage(),
                'por_pagina' => $postulantes->perPage(),
                'total' => $postulantes->total(),
                'ultima_pagina' => $postulantes->lastPage(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $postulante = $this->applicants->findDetailed($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Postulante no encontrado.', [], 404);
        }

        return ApiResponse::success('Postulante obtenido correctamente.', [
            'postulante' => $this->applicants->formatDetail($postulante),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->updateRules($id), $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $postulante = $this->applicants->update($id, $validator->validated());
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Postulante no encontrado.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Postulante actualizado correctamente.', [
            'postulante' => $this->applicants->formatDetail($postulante),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'observacion' => ['nullable', 'string'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $postulante = $this->applicants->logicalDelete($id, $validator->validated()['observacion'] ?? null);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Postulante no encontrado.', [], 404);
        }

        return ApiResponse::success('Postulante eliminado logicamente correctamente.', [
            'postulante' => $this->applicants->formatDetail($postulante),
        ]);
    }

    private function storeRules(): array
    {
        return [
            'cedula_identidad' => ['required', 'string', 'max:20', 'unique:persona,cedula_identidad'],
            'nombres' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['required', 'string', 'max:100'],
            'fecha_nacimiento' => ['required', 'date'],
            'sexo' => ['required', 'string', 'max:20'],
            'direccion' => ['required', 'string'],
            'telefono' => ['required', 'string', 'max:30'],
            'correo' => ['required', 'email', 'max:150', 'unique:persona,correo'],
            'colegio_procedencia' => ['required', 'string', 'max:150'],
            'ciudad' => ['required', 'string', 'max:100'],
            'gestion_academica_id' => ['nullable', 'integer', 'exists:gestion_academica,id'],
            'primera_carrera_id' => ['required', 'integer', 'exists:carrera,id', 'different:segunda_carrera_id'],
            'segunda_carrera_id' => ['required', 'integer', 'exists:carrera,id', 'different:primera_carrera_id'],
        ];
    }

    private function updateRules(int $id): array
    {
        $postulante = PostulanteModel::query()->find($id);
        $personaId = $postulante?->persona_id ?? 0;

        return [
            'cedula_identidad' => ['sometimes', 'string', 'max:20', Rule::unique('persona', 'cedula_identidad')->ignore($personaId)],
            'nombres' => ['sometimes', 'string', 'max:100'],
            'apellido_paterno' => ['sometimes', 'string', 'max:100'],
            'apellido_materno' => ['sometimes', 'string', 'max:100'],
            'fecha_nacimiento' => ['sometimes', 'date'],
            'sexo' => ['sometimes', 'string', 'max:20'],
            'direccion' => ['sometimes', 'string'],
            'telefono' => ['sometimes', 'string', 'max:30'],
            'correo' => ['sometimes', 'email', 'max:150', Rule::unique('persona', 'correo')->ignore($personaId)],
            'colegio_procedencia' => ['sometimes', 'string', 'max:150'],
            'ciudad' => ['sometimes', 'string', 'max:100'],
            'gestion_academica_id' => ['sometimes', 'integer', 'exists:gestion_academica,id'],
            'estado_requisitos' => ['sometimes', Rule::in(['pendiente', 'aprobado', 'rechazado'])],
            'estado_pago' => ['sometimes', Rule::in(['pendiente', 'pagado', 'fallido'])],
            'estado_postulante' => ['sometimes', Rule::in(['registrado', 'pendiente_pago', 'pagado', 'habilitado_alumno', 'rechazado'])],
            'observacion' => ['nullable', 'string'],
            'primera_carrera_id' => ['sometimes', 'integer', 'exists:carrera,id'],
            'segunda_carrera_id' => ['sometimes', 'integer', 'exists:carrera,id'],
        ];
    }

    private function messages(): array
    {
        return [
            'cedula_identidad.required' => 'La cedula de identidad es obligatoria.',
            'cedula_identidad.unique' => 'La cedula de identidad ya esta registrada.',
            'nombres.required' => 'Los nombres son obligatorios.',
            'apellido_paterno.required' => 'El apellido paterno es obligatorio.',
            'apellido_materno.required' => 'El apellido materno es obligatorio.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento debe ser valida.',
            'sexo.required' => 'El sexo es obligatorio.',
            'direccion.required' => 'La direccion es obligatoria.',
            'telefono.required' => 'El telefono es obligatorio.',
            'correo.required' => 'El correo electronico es obligatorio.',
            'correo.email' => 'El correo electronico debe ser valido.',
            'correo.unique' => 'El correo electronico ya esta registrado.',
            'colegio_procedencia.required' => 'El colegio de procedencia es obligatorio.',
            'ciudad.required' => 'La ciudad es obligatoria.',
            'gestion_academica_id.exists' => 'La gestion academica indicada no existe.',
            'primera_carrera_id.required' => 'La primera opcion de carrera es obligatoria.',
            'primera_carrera_id.exists' => 'La primera opcion de carrera no existe.',
            'primera_carrera_id.different' => 'La primera y segunda opcion de carrera deben ser diferentes.',
            'segunda_carrera_id.required' => 'La segunda opcion de carrera es obligatoria.',
            'segunda_carrera_id.exists' => 'La segunda opcion de carrera no existe.',
            'segunda_carrera_id.different' => 'La primera y segunda opcion de carrera deben ser diferentes.',
            'estado.in' => 'El estado indicado no es valido.',
        ];
    }
}

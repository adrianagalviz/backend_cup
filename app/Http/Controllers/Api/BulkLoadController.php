<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Models\CargaMasivaModel;
use App\Services\BulkLoads\BulkLoadService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BulkLoadController extends Controller
{
    public function __construct(
        private readonly BulkLoadService $bulkLoads,
    ) {
    }

    public function csv(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->uploadRules(['csv', 'txt']), $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $load = $this->bulkLoads->processCsv(
            $request->attributes->get('usuario_autenticado'),
            $request->file('archivo'),
            $validator->validated()
        );

        return ApiResponse::success('Carga CSV procesada correctamente.', [
            'carga' => $this->bulkLoads->formatLoad($load, true),
        ], 201);
    }

    public function excel(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->uploadRules(['xlsx', 'xls']), $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $load = $this->bulkLoads->processExcel(
            $request->attributes->get('usuario_autenticado'),
            $request->file('archivo'),
            $validator->validated()
        );

        return ApiResponse::success('Carga Excel procesada correctamente.', [
            'carga' => $this->bulkLoads->formatLoad($load, true),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'formato_archivo' => ['nullable', Rule::in(['csv', 'excel'])],
            'estado' => ['nullable', Rule::in(['procesando', 'finalizado', 'con_errores', 'fallido'])],
            'tipo_carga' => ['nullable', 'string', 'max:50'],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $loads = $this->bulkLoads->listLoads($validator->validated());

        return response()->json([
            'ok' => true,
            'mensaje' => 'Cargas masivas obtenidas correctamente.',
            'datos' => collect($loads->items())
                ->map(fn (CargaMasivaModel $load): array => $this->bulkLoads->formatLoad($load))
                ->values(),
            'meta' => [
                'pagina_actual' => $loads->currentPage(),
                'por_pagina' => $loads->perPage(),
                'total' => $loads->total(),
                'ultima_pagina' => $loads->lastPage(),
            ],
        ]);
    }

    public function detail(int $id): JsonResponse
    {
        try {
            $load = $this->bulkLoads->findLoad($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Carga masiva no encontrada.', [], 404);
        }

        return ApiResponse::success('Detalle de carga masiva obtenido correctamente.', [
            'carga' => $this->bulkLoads->formatLoad($load, true),
        ]);
    }

    private function uploadRules(array $extensions): array
    {
        return [
            'archivo' => ['required', 'file', 'max:5120', 'extensions:'.implode(',', $extensions)],
            'tipo_carga' => ['nullable', 'string', 'max:50'],
        ];
    }

    private function messages(): array
    {
        return [
            'archivo.required' => 'El archivo es obligatorio.',
            'archivo.file' => 'Debe enviar un archivo valido.',
            'archivo.max' => 'El archivo no debe superar 5 MB.',
            'archivo.extensions' => 'La extension del archivo no es valida.',
            'formato_archivo.in' => 'El formato de archivo no es valido.',
            'estado.in' => 'El estado de la carga no es valido.',
        ];
    }
}

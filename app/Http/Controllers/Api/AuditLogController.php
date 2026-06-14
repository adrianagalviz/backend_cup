<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Models\BitacoraSistemaModel;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuditLogController extends Controller
{
    public function __construct(
        private readonly AuditLogService $audit,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'usuario_id' => ['nullable', 'integer', Rule::exists('usuario', 'id')],
            'modulo' => ['nullable', 'string', 'max:80'],
            'metodo_http' => ['nullable', Rule::in(['POST', 'PUT', 'PATCH', 'DELETE'])],
            'buscar' => ['nullable', 'string', 'max:150'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            'pagina' => ['nullable', 'integer', 'min:1'],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $logs = $this->audit->list($validator->validated());

        return response()->json([
            'ok' => true,
            'mensaje' => 'Bitacora obtenida correctamente.',
            'datos' => collect($logs->items())->map(
                fn (BitacoraSistemaModel $log) => $this->audit->format($log)
            )->values(),
            'meta' => [
                'pagina_actual' => $logs->currentPage(),
                'por_pagina' => $logs->perPage(),
                'total' => $logs->total(),
                'ultima_pagina' => $logs->lastPage(),
            ],
        ]);
    }
}

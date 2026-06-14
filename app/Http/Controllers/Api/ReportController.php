<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Models\GrupoModel;
use App\Models\PostulanteModel;
use App\Models\PromedioFinalModel;
use App\Services\Reports\AdministrativeReportService;
use App\Services\Reports\ReportExportService;
use App\Services\Reports\VoiceReportService;
use App\Support\ApiResponse;
use InvalidArgumentException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function __construct(
        private readonly AdministrativeReportService $reports,
        private readonly ReportExportService $exports,
        private readonly VoiceReportService $voiceReports,
    ) {
    }

    public function applicants(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'gestion_academica_id' => ['nullable', 'integer', 'exists:gestion_academica,id'],
            'estado_postulante' => ['nullable', Rule::in(['registrado', 'pendiente_pago', 'pagado', 'habilitado_alumno', 'rechazado'])],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $this->register($request, 'postulantes', $validator->validated());

        return $this->paginatedResponse(
            'Reporte de postulantes obtenido correctamente.',
            $this->reports->applicants($validator->validated()),
            fn (PostulanteModel $applicant) => $this->reports->formatApplicant($applicant)
        );
    }

    public function approved(Request $request): JsonResponse
    {
        return $this->averageReport($request, 'approved', 'Reporte de alumnos aprobados obtenido correctamente.');
    }

    public function failed(Request $request): JsonResponse
    {
        return $this->averageReport($request, 'failed', 'Reporte de alumnos reprobados obtenido correctamente.');
    }

    public function averages(Request $request): JsonResponse
    {
        return $this->averageReport($request, 'averages', 'Reporte de promedios generales obtenido correctamente.');
    }

    public function groups(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'gestion_academica_id' => ['nullable', 'integer', 'exists:gestion_academica,id'],
            'activo' => ['nullable', Rule::in(['true', 'false', '1', '0'])],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $this->register($request, 'grupos', $validator->validated());

        return $this->paginatedResponse(
            'Reporte de grupos obtenido correctamente.',
            $this->reports->groups($validator->validated()),
            fn (GrupoModel $group) => $this->reports->formatGroup($group)
        );
    }

    public function subjectStatistics(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'gestion_academica_id' => ['nullable', 'integer', 'exists:gestion_academica,id'],
        ]);

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $filters = $validator->validated();
        $this->register($request, 'estadisticas_materia', $filters);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Reporte de estadisticas por materia obtenido correctamente.',
            'datos' => $this->reports->subjectStatistics($filters),
        ]);
    }

    public function teachersGroups(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'gestion_academica_id' => ['nullable', 'integer', 'exists:gestion_academica,id'],
            'docente_id' => ['nullable', 'integer', 'exists:docente,id'],
        ]);

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $filters = $validator->validated();
        $this->register($request, 'docentes_grupos', $filters);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Reporte de docentes por grupos obtenido correctamente.',
            'datos' => $this->reports->teachersGroups($filters),
        ]);
    }

    public function groupsMostApproved(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'gestion_academica_id' => ['nullable', 'integer', 'exists:gestion_academica,id'],
        ]);

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $filters = $validator->validated();
        $this->register($request, 'grupos_mayor_aprobados', $filters);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Reporte de grupos con mayor cantidad de aprobados obtenido correctamente.',
            'datos' => $this->reports->groupsMostApproved($filters),
        ]);
    }

    public function teacherAttendance(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), $this->attendanceRules([
            'docente_id' => ['nullable', 'integer', 'exists:docente,id'],
        ]));

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $filters = $validator->validated();
        $this->register($request, 'asistencia_docentes', $filters);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Reporte de asistencia de docentes obtenido correctamente.',
            'datos' => $this->reports->teacherAttendance($filters),
        ]);
    }

    public function studentAttendance(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), $this->attendanceRules([
            'alumno_id' => ['nullable', 'integer', 'exists:alumno,id'],
            'grupo_id' => ['nullable', 'integer', 'exists:grupo,id'],
        ]));

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $filters = $validator->validated();
        $this->register($request, 'asistencia_alumnos', $filters);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Reporte de asistencia de alumnos obtenido correctamente.',
            'datos' => $this->reports->studentAttendance($filters),
        ]);
    }

    public function export(Request $request, string $tipo): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'formato' => ['required', Rule::in(['pdf', 'excel'])],
            'gestion_academica_id' => ['nullable', 'integer', 'exists:gestion_academica,id'],
            'estado_postulante' => ['nullable', Rule::in(['registrado', 'pendiente_pago', 'pagado', 'habilitado_alumno', 'rechazado'])],
            'activo' => ['nullable', Rule::in(['true', 'false', '1', '0'])],
            'docente_id' => ['nullable', 'integer', 'exists:docente,id'],
            'alumno_id' => ['nullable', 'integer', 'exists:alumno,id'],
            'grupo_id' => ['nullable', 'integer', 'exists:grupo,id'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:5000'],
        ]);

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $data = $validator->validated();
        $format = $data['formato'];
        unset($data['formato']);

        try {
            $result = $this->exports->export(
                $request->attributes->get('usuario_autenticado'),
                $tipo,
                $format,
                $data
            );
        } catch (InvalidArgumentException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Reporte exportado correctamente.', $result);
    }

    public function downloadFile(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $path = $request->query('ruta');

        if (!$path || !str_starts_with($path, 'storage/reports/') || str_contains($path, '..')) {
            return ApiResponse::error('Ruta de archivo no permitida o incorrecta.', [], 403);
        }

        $diskPath = str_replace('storage/', '', $path);
        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        if (!$disk->exists($diskPath)) {
            return ApiResponse::error('El archivo no existe en el almacenamiento.', [], 404);
        }

        return response()->download($disk->path($diskPath));
    }

    public function voiceCommand(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'texto' => ['required', 'string'],
            'formato' => ['nullable', Rule::in(['pdf', 'excel'])],
            'filtros' => ['nullable', 'array'],
            'filtros.gestion_academica_id' => ['nullable', 'integer', 'exists:gestion_academica,id'],
            'filtros.fecha_desde' => ['nullable', 'date'],
            'filtros.fecha_hasta' => ['nullable', 'date', 'after_or_equal:filtros.fecha_desde'],
            'filtros.por_pagina' => ['nullable', 'integer', 'min:1', 'max:500'],
        ], [
            'texto.required' => 'El texto interpretado del comando de voz es obligatorio.',
            'formato.in' => 'El formato debe ser pdf o excel.',
        ]);

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $data = $validator->validated();

        try {
            $result = $this->voiceReports->handle(
                $request->attributes->get('usuario_autenticado'),
                $data['texto'],
                $data['filtros'] ?? [],
                $data['formato'] ?? null
            );
        } catch (InvalidArgumentException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        $status = ($result['reconocido'] ?? false) ? 200 : 422;

        return response()->json([
            'ok' => $result['reconocido'] ?? false,
            'mensaje' => ($result['reconocido'] ?? false)
                ? 'Comando de voz interpretado correctamente.'
                : 'Comando de voz no reconocido.',
            'datos' => $result,
        ], $status);
    }

    private function averageReport(Request $request, string $type, string $message): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'gestion_academica_id' => ['nullable', 'integer', 'exists:gestion_academica,id'],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $records = match ($type) {
            'approved' => $this->reports->approved($validator->validated()),
            'failed' => $this->reports->failed($validator->validated()),
            default => $this->reports->averages($validator->validated()),
        };

        $filters = $validator->validated();
        $this->register($request, $type, $filters);

        return $this->paginatedResponse(
            $message,
            $records,
            fn (PromedioFinalModel $average) => $this->reports->formatAverage($average)
        );
    }

    private function attendanceRules(array $extra): array
    {
        return [
            'gestion_academica_id' => ['nullable', 'integer', 'exists:gestion_academica,id'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            ...$extra,
        ];
    }

    private function register(Request $request, string $type, array $parameters): void
    {
        $this->reports->registerGeneratedReport(
            $request->attributes->get('usuario_autenticado'),
            $type,
            $parameters
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
}

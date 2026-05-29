<?php

namespace App\Services\Reports;

use App\Models\ComandoVozReporteModel;
use App\Models\GrupoModel;
use App\Models\PostulanteModel;
use App\Models\PromedioFinalModel;
use App\Models\UsuarioModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class VoiceReportService
{
    private const COMMANDS = [
        'listar alumnos aprobados' => 'aprobados',
        'listar alumnos reprobados' => 'reprobados',
        'listar alumnos reprobados y aprobados' => 'promedios',
        'listar postulantes' => 'postulantes',
        'listar grupos habilitados' => 'grupos',
        'listar asistencia docentes' => 'asistencia-docentes',
        'listar asistencia alumnos' => 'asistencia-alumnos',
        'listar promedios generales' => 'promedios',
    ];

    public function __construct(
        private readonly AdministrativeReportService $reports,
        private readonly ReportExportService $exports,
    ) {
    }

    public function handle(UsuarioModel $user, string $text, array $filters = [], ?string $format = null): array
    {
        $cleanText = $this->normalize($text);
        $reportType = self::COMMANDS[$cleanText] ?? null;

        if (! $reportType) {
            $command = $this->registerCommand($user, $text, null, null);

            return [
                'comando' => $this->formatCommand($command),
                'reconocido' => false,
                'texto_limpio' => $cleanText,
                'comandos_permitidos' => array_keys(self::COMMANDS),
            ];
        }

        if ($format !== null) {
            if (! in_array($format, ['pdf', 'excel'], true)) {
                throw new InvalidArgumentException('El formato debe ser pdf o excel.');
            }

            $export = $this->exports->export($user, $reportType, $format, $filters);
            $command = $this->registerCommand($user, $text, $reportType, $export['reporte']['id'] ?? null);

            return [
                'comando' => $this->formatCommand($command),
                'reconocido' => true,
                'texto_limpio' => $cleanText,
                'tipo_reporte' => $reportType,
                'formato' => $format,
                'exportacion' => $export,
            ];
        }

        $report = $this->reportData($reportType, $filters);
        $generated = $this->reports->registerGeneratedReport($user, $reportType, $filters);
        $command = $this->registerCommand($user, $text, $reportType, $generated->id);

        return [
            'comando' => $this->formatCommand($command),
            'reconocido' => true,
            'texto_limpio' => $cleanText,
            'tipo_reporte' => $reportType,
            'formato' => null,
            'reporte_generado' => $this->reports->formatGeneratedReport($generated),
            'datos' => $report,
        ];
    }

    public function allowedCommands(): array
    {
        return array_keys(self::COMMANDS);
    }

    private function reportData(string $type, array $filters): array|Collection
    {
        return match ($type) {
            'postulantes' => $this->fromPaginator(
                $this->reports->applicants($this->withDefaultPagination($filters)),
                fn (PostulanteModel $applicant) => $this->reports->formatApplicant($applicant)
            ),
            'aprobados' => $this->fromPaginator(
                $this->reports->approved($this->withDefaultPagination($filters)),
                fn (PromedioFinalModel $average) => $this->reports->formatAverage($average)
            ),
            'reprobados' => $this->fromPaginator(
                $this->reports->failed($this->withDefaultPagination($filters)),
                fn (PromedioFinalModel $average) => $this->reports->formatAverage($average)
            ),
            'promedios' => $this->fromPaginator(
                $this->reports->averages($this->withDefaultPagination($filters)),
                fn (PromedioFinalModel $average) => $this->reports->formatAverage($average)
            ),
            'grupos' => $this->fromPaginator(
                $this->reports->groups([...$this->withDefaultPagination($filters), 'activo' => 'true']),
                fn (GrupoModel $group) => $this->reports->formatGroup($group)
            ),
            'asistencia-docentes' => $this->reports->teacherAttendance($filters),
            'asistencia-alumnos' => $this->reports->studentAttendance($filters),
            default => [],
        };
    }

    private function fromPaginator(LengthAwarePaginator $paginator, callable $formatter): array
    {
        return [
            'datos' => collect($paginator->items())->map($formatter)->values(),
            'meta' => [
                'pagina_actual' => $paginator->currentPage(),
                'por_pagina' => $paginator->perPage(),
                'total' => $paginator->total(),
                'ultima_pagina' => $paginator->lastPage(),
            ],
        ];
    }

    private function withDefaultPagination(array $filters): array
    {
        $filters['por_pagina'] = min((int) ($filters['por_pagina'] ?? 100), 500);

        return $filters;
    }

    private function registerCommand(UsuarioModel $user, string $text, ?string $intent, ?int $reportId): ComandoVozReporteModel
    {
        $id = DB::table('comando_voz_reporte')->insertGetId([
            'usuario_id' => $user->id,
            'texto_detectado' => $text,
            'intencion_detectada' => $intent,
            'reporte_generado_id' => $reportId,
            'creado_en' => now(),
        ]);

        return ComandoVozReporteModel::query()
            ->with('reporteGenerado')
            ->findOrFail($id);
    }

    private function formatCommand(ComandoVozReporteModel $command): array
    {
        return [
            'id' => $command->id,
            'texto_detectado' => $command->texto_detectado,
            'intencion_detectada' => $command->intencion_detectada,
            'reporte_generado_id' => $command->reporte_generado_id,
            'creado_en' => $command->creado_en,
        ];
    }

    private function normalize(string $text): string
    {
        $text = trim(mb_strtolower($text));
        $text = preg_replace('/\s+/', ' ', $text) ?? '';

        return trim($text);
    }
}

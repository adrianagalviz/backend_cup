<?php

namespace App\Services\Reports;

use App\Models\GrupoModel;
use App\Models\PostulanteModel;
use App\Models\PromedioFinalModel;
use App\Models\UsuarioModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class ReportExportService
{
    private const TYPES = [
        'postulantes' => 'Reporte de postulantes',
        'aprobados' => 'Reporte de alumnos aprobados',
        'reprobados' => 'Reporte de alumnos reprobados',
        'promedios' => 'Reporte de promedios generales',
        'grupos' => 'Reporte de grupos',
        'estadisticas-materia' => 'Reporte de estadisticas por materia',
        'docentes-grupos' => 'Reporte de docentes por grupos',
        'grupos-mayor-aprobados' => 'Reporte de grupos con mayor cantidad de aprobados',
        'asistencia-docentes' => 'Reporte de asistencia de docentes',
        'asistencia-alumnos' => 'Reporte de asistencia de alumnos',
    ];

    public function __construct(
        private readonly AdministrativeReportService $reports,
        private readonly PdfExportService $pdf,
        private readonly ExcelExportService $excel,
    ) {
    }

    public function export(UsuarioModel $user, string $type, string $format, array $filters): array
    {
        if (! array_key_exists($type, self::TYPES)) {
            throw new InvalidArgumentException('Tipo de reporte no permitido.');
        }

        if (! in_array($format, ['pdf', 'excel'], true)) {
            throw new InvalidArgumentException('El formato debe ser pdf o excel.');
        }

        $rows = $this->rows($type, $filters);
        $extension = $format === 'pdf' ? 'pdf' : 'xlsx';
        $filename = $this->filename($type, $extension);
        $disk = Storage::disk('public');
        $directory = 'reports';
        $relativeDiskPath = $directory.'/'.$filename;

        if (! $disk->exists($directory)) {
            $disk->makeDirectory($directory);
        }

        $filePath = $disk->path($relativeDiskPath);

        if ($format === 'pdf') {
            $this->pdf->export(self::TYPES[$type], $rows, $filePath);
        } else {
            $this->excel->export(self::TYPES[$type], $rows, $filePath);
        }

        $relativePath = 'storage/reports/'.$filename;
        $publicUrl = $disk->url($relativeDiskPath);
        $report = $this->reports->registerGeneratedReport(
            $user,
            $type,
            $filters,
            $format,
            $publicUrl
        );

        return [
            'reporte' => $this->reports->formatGeneratedReport($report),
            'tipo_reporte' => $type,
            'formato' => $format,
            'archivo' => [
                'nombre' => $filename,
                'ruta' => $relativePath,
                'url' => $publicUrl,
                'ruta_absoluta' => $filePath,
            ],
            'total_filas' => count($rows),
        ];
    }

    private function rows(string $type, array $filters): array
    {
        return match ($type) {
            'postulantes' => $this->fromPaginator(
                $this->reports->applicants($this->withExportPagination($filters)),
                fn (PostulanteModel $applicant) => $this->reports->formatApplicant($applicant)
            ),
            'aprobados' => $this->fromPaginator(
                $this->reports->approved($this->withExportPagination($filters)),
                fn (PromedioFinalModel $average) => $this->reports->formatAverage($average)
            ),
            'reprobados' => $this->fromPaginator(
                $this->reports->failed($this->withExportPagination($filters)),
                fn (PromedioFinalModel $average) => $this->reports->formatAverage($average)
            ),
            'promedios' => $this->fromPaginator(
                $this->reports->averages($this->withExportPagination($filters)),
                fn (PromedioFinalModel $average) => $this->reports->formatAverage($average)
            ),
            'grupos' => $this->fromPaginator(
                $this->reports->groups($this->withExportPagination($filters)),
                fn (GrupoModel $group) => $this->reports->formatGroup($group)
            ),
            'estadisticas-materia' => $this->fromCollection($this->reports->subjectStatistics($filters)),
            'docentes-grupos' => $this->fromCollection($this->reports->teachersGroups($filters)),
            'grupos-mayor-aprobados' => $this->fromCollection($this->reports->groupsMostApproved($filters)),
            'asistencia-docentes' => $this->fromCollection($this->reports->teacherAttendance($filters)),
            'asistencia-alumnos' => $this->fromCollection($this->reports->studentAttendance($filters)),
        };
    }

    private function fromPaginator(LengthAwarePaginator $paginator, callable $formatter): array
    {
        return collect($paginator->items())
            ->map($formatter)
            ->map(fn (array $row) => $this->flatten($row))
            ->values()
            ->all();
    }

    private function fromCollection(Collection $collection): array
    {
        return $collection
            ->map(fn (array $row) => $this->flatten($row))
            ->values()
            ->all();
    }

    private function flatten(array $row, string $prefix = ''): array
    {
        $flat = [];

        foreach ($row as $key => $value) {
            $name = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            if (is_array($value)) {
                $flat += $this->flatten($value, $name);
                continue;
            }

            if ($value instanceof Collection) {
                $flat[$name] = $value->toJson(JSON_UNESCAPED_UNICODE);
                continue;
            }

            if (is_object($value) && method_exists($value, '__toString')) {
                $flat[$name] = (string) $value;
                continue;
            }

            $flat[$name] = $value === null ? '' : (string) $value;
        }

        return $flat;
    }

    private function withExportPagination(array $filters): array
    {
        $filters['por_pagina'] = min((int) ($filters['por_pagina'] ?? 1000), 5000);

        return $filters;
    }

    private function filename(string $type, string $extension): string
    {
        return $type.'_'.now(config('app.timezone'))->format('Ymd_His').'.'.$extension;
    }
}

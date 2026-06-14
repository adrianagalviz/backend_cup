<?php

namespace App\Services\Reports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExportService
{
    public function export(string $title, array $rows, string $filePath): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($this->sanitizeSheetTitle($title), 0, 31));

        $headers = $this->headers($rows);

        foreach ($headers as $index => $header) {
            $sheet->setCellValue([$index + 1, 1], $this->label($header));
        }

        if ($rows === []) {
            $sheet->setCellValue([1, 2], 'Sin datos para el reporte.');
        }

        foreach (array_values($rows) as $rowIndex => $row) {
            foreach ($headers as $columnIndex => $header) {
                $sheet->setCellValue([$columnIndex + 1, $rowIndex + 2], $row[$header] ?? '');
            }
        }

        foreach (range(1, count($headers)) as $column) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }

        (new Xlsx($spreadsheet))->save($filePath);
    }

    private function sanitizeSheetTitle(string $title): string
    {
        return preg_replace('/[\\\\\\/\\?\\*\\[\\]:]/', ' ', $title) ?: 'Reporte';
    }

    private function headers(array $rows): array
    {
        if ($rows === []) {
            return ['mensaje'];
        }

        return collect($rows)
            ->flatMap(fn (array $row): array => array_keys($row))
            ->unique()
            ->values()
            ->all();
    }

    private function label(string $header): string
    {
        return str($header)
            ->replace(['_', '.'], ' ')
            ->headline()
            ->toString();
    }
}

<?php

namespace App\Services\Reports;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfExportService
{
    public function export(string $title, array $rows, string $filePath): void
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        $pdf = new Dompdf($options);
        $pdf->loadHtml($this->html($title, $rows), 'UTF-8');
        $pdf->setPaper('A4', 'landscape');
        $pdf->render();

        file_put_contents($filePath, $pdf->output());
    }

    private function html(string $title, array $rows): string
    {
        $headers = $this->headers($rows);
        $body = $rows === []
            ? '<tr><td colspan="1">Sin datos para el reporte.</td></tr>'
            : collect($rows)->map(function (array $row) use ($headers): string {
                $cells = collect($headers)
                    ->map(fn (string $header): string => '<td>'.$this->escape((string) ($row[$header] ?? '')).'</td>')
                    ->implode('');

                return '<tr>'.$cells.'</tr>';
            })->implode('');

        $head = collect($headers)
            ->map(fn (string $header): string => '<th>'.$this->escape($header).'</th>')
            ->implode('');

        return '<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; }
h1 { font-size: 18px; margin: 0 0 10px; }
p { margin: 0 0 12px; color: #4b5563; }
table { width: 100%; border-collapse: collapse; }
th { background: #e5e7eb; font-weight: bold; }
th, td { border: 1px solid #d1d5db; padding: 5px; vertical-align: top; }
</style>
</head>
<body>
<h1>'.$this->escape($title).'</h1>
<p>Generado: '.date('Y-m-d H:i:s').'</p>
<table>
<thead><tr>'.$head.'</tr></thead>
<tbody>'.$body.'</tbody>
</table>
</body>
</html>';
    }

    private function headers(array $rows): array
    {
        return $rows === [] ? ['mensaje'] : array_keys($rows[0]);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

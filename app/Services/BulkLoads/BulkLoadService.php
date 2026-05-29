<?php

namespace App\Services\BulkLoads;

use App\Models\AlumnoModel;
use App\Models\CargaMasivaModel;
use App\Models\DetalleCargaMasivaModel;
use App\Models\PostulanteModel;
use App\Models\UsuarioModel;
use App\Services\Students\ApplicantConversionService;
use App\Services\Teachers\TeacherManagementService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;
use Throwable;

class BulkLoadService
{
    private const ALLOWED_ROLES = ['docente', 'alumno'];

    public function __construct(
        private readonly TeacherManagementService $teachers,
        private readonly ApplicantConversionService $students,
    ) {
    }

    public function processCsv(UsuarioModel $administrator, UploadedFile $file, array $data = []): CargaMasivaModel
    {
        return $this->process($administrator, $file, 'csv', $data);
    }

    public function processExcel(UsuarioModel $administrator, UploadedFile $file, array $data = []): CargaMasivaModel
    {
        return $this->process($administrator, $file, 'excel', $data);
    }

    public function listLoads(array $filters = []): LengthAwarePaginator
    {
        return CargaMasivaModel::query()
            ->with('usuario.persona')
            ->withCount('detalles')
            ->when($filters['formato_archivo'] ?? null, fn ($query, string $format) => $query->where('formato_archivo', $format))
            ->when($filters['estado'] ?? null, fn ($query, string $status) => $query->where('estado', $status))
            ->when($filters['tipo_carga'] ?? null, fn ($query, string $type) => $query->where('tipo_carga', $type))
            ->orderByDesc('id')
            ->paginate((int) ($filters['por_pagina'] ?? 15));
    }

    public function findLoad(int $id): CargaMasivaModel
    {
        return CargaMasivaModel::query()
            ->with(['usuario.persona', 'detalles' => fn ($query) => $query->orderBy('numero_fila')])
            ->findOrFail($id);
    }

    public function formatLoad(CargaMasivaModel $load, bool $includeDetails = false): array
    {
        $data = [
            'id' => $load->id,
            'usuario_id' => $load->usuario_id,
            'tipo_carga' => $load->tipo_carga,
            'nombre_archivo' => $load->nombre_archivo,
            'formato_archivo' => $load->formato_archivo,
            'total_registros' => $load->total_registros,
            'registros_exitosos' => $load->registros_exitosos,
            'registros_error' => $load->registros_error,
            'estado' => $load->estado,
            'creado_en' => $load->creado_en,
            'finalizado_en' => $load->finalizado_en,
            'usuario' => [
                'id' => $load->usuario?->id,
                'nombre_usuario' => $load->usuario?->nombre_usuario,
                'persona' => [
                    'id' => $load->usuario?->persona?->id,
                    'nombres' => $load->usuario?->persona?->nombres,
                    'apellido_paterno' => $load->usuario?->persona?->apellido_paterno,
                ],
            ],
        ];

        if ($includeDetails) {
            $data['detalles'] = $load->detalles
                ->map(fn (DetalleCargaMasivaModel $detail): array => [
                    'id' => $detail->id,
                    'numero_fila' => $detail->numero_fila,
                    'estado' => $detail->estado,
                    'mensaje_error' => $detail->mensaje_error,
                    'datos_fila' => $detail->datos_fila,
                ])
                ->values();
        }

        return $data;
    }

    private function process(UsuarioModel $administrator, UploadedFile $file, string $format, array $data): CargaMasivaModel
    {
        $path = $this->storeTemporaryFile($file);

        $load = CargaMasivaModel::query()->create([
            'usuario_id' => $administrator->id,
            'tipo_carga' => $data['tipo_carga'] ?? 'usuarios',
            'nombre_archivo' => $file->getClientOriginalName(),
            'formato_archivo' => $format,
            'estado' => 'procesando',
        ]);

        try {
            $rows = $format === 'csv' ? $this->readCsv($path) : $this->readExcel($path);
            $summary = $this->processRows($load, $administrator, $rows);

            $load->update([
                'total_registros' => $summary['total'],
                'registros_exitosos' => $summary['success'],
                'registros_error' => $summary['errors'],
                'estado' => $summary['errors'] > 0 ? 'con_errores' : 'finalizado',
                'finalizado_en' => now(),
            ]);
        } catch (Throwable $exception) {
            $load->update([
                'estado' => 'fallido',
                'registros_error' => max(1, (int) $load->registros_error),
                'finalizado_en' => now(),
            ]);

            DetalleCargaMasivaModel::query()->create([
                'carga_masiva_id' => $load->id,
                'numero_fila' => 0,
                'estado' => 'error',
                'mensaje_error' => $exception->getMessage(),
                'datos_fila' => null,
            ]);
        }

        return $this->findLoad($load->id);
    }

    private function processRows(CargaMasivaModel $load, UsuarioModel $administrator, array $rows): array
    {
        $summary = ['total' => 0, 'success' => 0, 'errors' => 0];
        $seen = ['ci' => [], 'correo' => [], 'postulante_id' => []];

        foreach ($rows as $row) {
            $summary['total']++;
            $validation = $this->validateRow($row['datos'], $seen);

            if ($validation !== []) {
                $summary['errors']++;
                $this->registerDetail($load, $row['numero_fila'], 'error', implode(' ', $validation), $row['datos']);
                continue;
            }

            try {
                DB::transaction(function () use ($row, $administrator): void {
                    $role = $this->normalizeText($row['datos']['rol']);

                    if ($role === 'docente') {
                        $this->teachers->createTeacher($this->teacherPayload($row['datos']), $administrator);
                        return;
                    }

                    $this->students->convertToStudent((int) $row['datos']['postulante_id'], $administrator);
                });

                $summary['success']++;
                $this->registerDetail($load, $row['numero_fila'], 'exitoso', null, $row['datos']);
            } catch (Throwable $exception) {
                $summary['errors']++;
                $this->registerDetail($load, $row['numero_fila'], 'error', $exception->getMessage(), $row['datos']);
            }
        }

        return $summary;
    }

    private function validateRow(array $row, array &$seen): array
    {
        $errors = [];
        $role = $this->normalizeText($row['rol'] ?? '');

        if ($role === '') {
            $errors[] = 'El campo rol es obligatorio.';
            return $errors;
        }

        if (! in_array($role, self::ALLOWED_ROLES, true)) {
            $errors[] = 'El rol permitido para carga masiva debe ser docente o alumno.';
            return $errors;
        }

        if ($role === 'docente') {
            foreach (['cedula_identidad', 'nombres', 'apellido_paterno', 'correo', 'celular'] as $field) {
                if ($this->isBlank($row[$field] ?? null)) {
                    $errors[] = "El campo {$field} es obligatorio para docentes.";
                }
            }

            $ci = trim((string) ($row['cedula_identidad'] ?? ''));
            $email = strtolower(trim((string) ($row['correo'] ?? '')));

            if ($ci !== '' && (isset($seen['ci'][$ci]) || DB::table('persona')->where('cedula_identidad', $ci)->exists())) {
                $errors[] = 'La cedula de identidad ya esta registrada.';
            }

            if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'El correo debe ser valido.';
            }

            if ($email !== '' && (isset($seen['correo'][$email]) || DB::table('persona')->where('correo', $email)->exists())) {
                $errors[] = 'El correo ya esta registrado.';
            }

            $username = trim((string) ($row['nombre_usuario'] ?? ''));
            if ($username !== '' && DB::table('usuario')->where('nombre_usuario', $username)->exists()) {
                $errors[] = 'El nombre de usuario ya esta registrado.';
            }

            if ($errors === []) {
                $seen['ci'][$ci] = true;
                $seen['correo'][$email] = true;
            }

            return $errors;
        }

        if ($this->isBlank($row['postulante_id'] ?? null)) {
            $errors[] = 'El campo postulante_id es obligatorio para alumnos.';
            return $errors;
        }

        $applicantId = (int) $row['postulante_id'];

        if ($applicantId < 1) {
            $errors[] = 'El postulante_id debe ser valido.';
        } elseif (isset($seen['postulante_id'][$applicantId])) {
            $errors[] = 'El postulante esta duplicado dentro del archivo.';
        } elseif (! PostulanteModel::query()->whereKey($applicantId)->exists()) {
            $errors[] = 'El postulante no existe.';
        } elseif (AlumnoModel::query()->where('postulante_id', $applicantId)->exists()) {
            $errors[] = 'El postulante ya fue convertido en alumno.';
        }

        if ($errors === []) {
            $seen['postulante_id'][$applicantId] = true;
        }

        return $errors;
    }

    private function teacherPayload(array $row): array
    {
        return [
            'cedula_identidad' => trim((string) $row['cedula_identidad']),
            'nombres' => trim((string) $row['nombres']),
            'apellido_paterno' => trim((string) $row['apellido_paterno']),
            'apellido_materno' => $this->nullableString($row['apellido_materno'] ?? null),
            'fecha_nacimiento' => $this->nullableString($row['fecha_nacimiento'] ?? null),
            'sexo' => $this->nullableString($row['sexo'] ?? null),
            'direccion' => $this->nullableString($row['direccion'] ?? null),
            'telefono' => $this->nullableString($row['telefono'] ?? null),
            'celular' => trim((string) $row['celular']),
            'correo' => strtolower(trim((string) $row['correo'])),
            'ciudad' => $this->nullableString($row['ciudad'] ?? null),
            'nombre_usuario' => $this->nullableString($row['nombre_usuario'] ?? null),
            'password' => $this->nullableString($row['password'] ?? null),
            'correo_verificado' => $this->toBoolean($row['correo_verificado'] ?? false),
        ];
    }

    private function registerDetail(CargaMasivaModel $load, int $rowNumber, string $status, ?string $message, ?array $data): void
    {
        DetalleCargaMasivaModel::query()->create([
            'carga_masiva_id' => $load->id,
            'numero_fila' => $rowNumber,
            'estado' => $status,
            'mensaje_error' => $message,
            'datos_fila' => $data,
        ]);
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('No se pudo leer el archivo CSV.');
        }

        $headers = fgetcsv($handle);

        if ($headers === false) {
            fclose($handle);
            throw new RuntimeException('El archivo CSV no contiene encabezados.');
        }

        $headers = $this->normalizeHeaders($headers);
        $rows = [];
        $line = 1;

        while (($cells = fgetcsv($handle)) !== false) {
            $line++;

            if ($this->isEmptyRow($cells)) {
                continue;
            }

            $rows[] = [
                'numero_fila' => $line,
                'datos' => $this->combineRow($headers, $cells),
            ];
        }

        fclose($handle);

        return $rows;
    }

    private function readExcel(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheetRows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        if ($sheetRows === [] || ! isset($sheetRows[0])) {
            throw new RuntimeException('El archivo Excel no contiene encabezados.');
        }

        $headers = $this->normalizeHeaders($sheetRows[0]);
        $rows = [];

        foreach (array_slice($sheetRows, 1) as $index => $cells) {
            if ($this->isEmptyRow($cells)) {
                continue;
            }

            $rows[] = [
                'numero_fila' => $index + 2,
                'datos' => $this->combineRow($headers, $cells),
            ];
        }

        return $rows;
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(fn ($header): string => $this->normalizeKey((string) $header), $headers);
    }

    private function combineRow(array $headers, array $cells): array
    {
        $row = [];

        foreach ($headers as $index => $header) {
            if ($header === '') {
                continue;
            }

            $row[$header] = $this->nullableString($cells[$index] ?? null) ?? '';
        }

        return $row;
    }

    private function normalizeKey(string $value): string
    {
        $value = strtolower(trim($this->removeAccents($value)));
        $value = preg_replace('/[^a-z0-9]+/', '_', $value) ?? '';

        return trim($value, '_');
    }

    private function normalizeText(mixed $value): string
    {
        return strtolower(trim($this->removeAccents((string) $value)));
    }

    private function removeAccents(string $value): string
    {
        return strtr($value, [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ñ' => 'n',
            'Á' => 'A',
            'É' => 'E',
            'Í' => 'I',
            'Ó' => 'O',
            'Ú' => 'U',
            'Ñ' => 'N',
        ]);
    }

    private function isEmptyRow(array $cells): bool
    {
        foreach ($cells as $cell) {
            if (! $this->isBlank($cell)) {
                return false;
            }
        }

        return true;
    }

    private function isBlank(mixed $value): bool
    {
        return trim((string) $value) === '';
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'si', 'sí', 'yes'], true);
    }

    private function storeTemporaryFile(UploadedFile $file): string
    {
        $directory = storage_path('temp');

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException('No se pudo crear el directorio temporal.');
        }

        $extension = $file->getClientOriginalExtension() ?: $file->extension();
        $filename = (string) Str::uuid().($extension ? ".{$extension}" : '');
        $file->move($directory, $filename);

        return $directory.DIRECTORY_SEPARATOR.$filename;
    }
}

<?php

namespace App\Services\Teachers;

use App\Services\Documents\CloudinaryService;
use App\Models\DocenteModel;
use App\Models\UsuarioModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TeacherManagementService
{
    public function __construct(
        private readonly CloudinaryService $cloudinary,
    ) {
    }

    public function createTeacher(array $data, UsuarioModel $creator): DocenteModel
    {
        return DB::transaction(function () use ($data, $creator): DocenteModel {
            $cvPdf = $data['cv_pdf'] ?? null;
            unset($data['cv_pdf']);

            $teacherRoleId = DB::table('rol')->where('nombre', 'docente')->where('activo', true)->value('id');

            if (! $teacherRoleId) {
                throw new RuntimeException('No existe un rol docente activo.');
            }

            $ci = $data['cedula_identidad'];
            $username = $data['nombre_usuario'] ?? 'docente_'.$ci;

            $personaId = DB::table('persona')->insertGetId([
                'cedula_identidad' => $ci,
                'nombres' => $data['nombres'],
                'apellido_paterno' => $data['apellido_paterno'],
                'apellido_materno' => $data['apellido_materno'],
                'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
                'sexo' => $data['sexo'] ?? null,
                'direccion' => $data['direccion'] ?? null,
                'telefono' => $data['telefono'] ?? null,
                'celular' => $data['celular'],
                'correo' => $data['correo'],
                'ciudad' => $data['ciudad'] ?? null,
                'creado_en' => now(),
            ]);

            $usuarioId = DB::table('usuario')->insertGetId([
                'persona_id' => $personaId,
                'rol_id' => $teacherRoleId,
                'nombre_usuario' => $username,
                'correo_verificado' => (bool) ($data['correo_verificado'] ?? false),
                'password_hash' => isset($data['password']) ? password_hash($data['password'], PASSWORD_DEFAULT) : null,
                'activo' => true,
                'creado_por_usuario_id' => $creator->id,
                'creado_en' => now(),
            ]);

            $teacherId = DB::table('docente')->insertGetId([
                'persona_id' => $personaId,
                'usuario_id' => $usuarioId,
                'es_profesional_area' => true,
                'tiene_maestria' => true,
                'tiene_diplomado_educacion_superior' => true,
                'contratado' => true,
                'activo' => true,
                'creado_en' => now(),
            ]);

            if ($cvPdf instanceof UploadedFile) {
                $this->storeCvPdf($teacherId, $cvPdf);
            }

            return $this->findTeacher($teacherId);
        });
    }

    public function listTeachers(array $filters): LengthAwarePaginator
    {
        return DocenteModel::query()
            ->with(['persona', 'usuario.rol'])
            ->when(array_key_exists('activo', $filters), function (Builder $query) use ($filters): void {
                $query->where('activo', filter_var($filters['activo'], FILTER_VALIDATE_BOOLEAN));
            })
            ->when($filters['ci'] ?? null, function (Builder $query, string $ci): void {
                $query->whereHas('persona', fn (Builder $personQuery) => $personQuery
                    ->where('cedula_identidad', 'ILIKE', "%{$ci}%"));
            })
            ->when($filters['nombre'] ?? null, function (Builder $query, string $name): void {
                $query->whereHas('persona', function (Builder $personQuery) use ($name): void {
                    $personQuery->where('nombres', 'ILIKE', "%{$name}%")
                        ->orWhere('apellido_paterno', 'ILIKE', "%{$name}%")
                        ->orWhere('apellido_materno', 'ILIKE', "%{$name}%");
                });
            })
            ->when($filters['buscar'] ?? null, function (Builder $query, string $search): void {
                $query->whereHas('persona', function (Builder $personQuery) use ($search): void {
                    $personQuery->where('cedula_identidad', 'ILIKE', "%{$search}%")
                        ->orWhere('nombres', 'ILIKE', "%{$search}%")
                        ->orWhere('apellido_paterno', 'ILIKE', "%{$search}%")
                        ->orWhere('apellido_materno', 'ILIKE', "%{$search}%")
                        ->orWhere('correo', 'ILIKE', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate((int) ($filters['por_pagina'] ?? 15));
    }

    public function findTeacher(int $id): DocenteModel
    {
        return DocenteModel::query()
            ->with(['persona', 'usuario.rol'])
            ->findOrFail($id);
    }

    public function updateTeacher(int $id, array $data): DocenteModel
    {
        return DB::transaction(function () use ($id, $data): DocenteModel {
            $cvPdf = $data['cv_pdf'] ?? null;
            unset($data['cv_pdf']);

            $teacher = $this->findTeacher($id);

            $personData = array_intersect_key($data, array_flip([
                'cedula_identidad',
                'nombres',
                'apellido_paterno',
                'apellido_materno',
                'fecha_nacimiento',
                'sexo',
                'direccion',
                'telefono',
                'celular',
                'correo',
                'ciudad',
            ]));

            if ($personData !== []) {
                $personData['actualizado_en'] = now();
                DB::table('persona')->where('id', $teacher->persona_id)->update($personData);
            }

            $teacherData = array_intersect_key($data, array_flip([
                'es_profesional_area',
                'tiene_maestria',
                'tiene_diplomado_educacion_superior',
                'activo',
            ]));

            if ($teacherData !== []) {
                $teacherData['contratado'] = ($teacherData['es_profesional_area'] ?? $teacher->es_profesional_area)
                    && ($teacherData['tiene_maestria'] ?? $teacher->tiene_maestria)
                    && ($teacherData['tiene_diplomado_educacion_superior'] ?? $teacher->tiene_diplomado_educacion_superior);
                $teacherData['actualizado_en'] = now();
                DB::table('docente')->where('id', $teacher->id)->update($teacherData);
            }

            $userData = array_intersect_key($data, array_flip([
                'nombre_usuario',
                'correo_verificado',
            ]));

            if (isset($data['password'])) {
                $userData['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            if (array_key_exists('activo', $data)) {
                $userData['activo'] = (bool) $data['activo'];
            }

            if ($userData !== [] && $teacher->usuario_id) {
                $userData['actualizado_en'] = now();
                DB::table('usuario')->where('id', $teacher->usuario_id)->update($userData);
            }

            if ($cvPdf instanceof UploadedFile) {
                $this->storeCvPdf($teacher->id, $cvPdf);
            }

            return $this->findTeacher($id);
        });
    }

    public function deactivateTeacher(int $id): DocenteModel
    {
        $teacher = $this->findTeacher($id);

        DB::transaction(function () use ($teacher): void {
            DB::table('docente')
                ->where('id', $teacher->id)
                ->update([
                    'activo' => false,
                    'actualizado_en' => now(),
                ]);

            if ($teacher->usuario_id) {
                DB::table('usuario')
                    ->where('id', $teacher->usuario_id)
                    ->update([
                        'activo' => false,
                        'actualizado_en' => now(),
                    ]);
            }
        });

        return $this->findTeacher($id);
    }

    public function assignmentSummary(int $teacherId): array
    {
        $rows = DB::table('asignacion_docente')
            ->join('materia', 'materia.id', '=', 'asignacion_docente.materia_id')
            ->join('grupo', 'grupo.id', '=', 'asignacion_docente.grupo_id')
            ->join('gestion_academica', 'gestion_academica.id', '=', 'asignacion_docente.gestion_academica_id')
            ->join('horario_clase', 'horario_clase.id', '=', 'asignacion_docente.horario_clase_id')
            ->join('aula', 'aula.id', '=', 'horario_clase.aula_id')
            ->join('dia', 'dia.id', '=', 'horario_clase.dia_id')
            ->join('turno', 'turno.id', '=', 'horario_clase.turno_id')
            ->join('periodo', 'periodo.id', '=', 'horario_clase.periodo_id')
            ->where('asignacion_docente.docente_id', $teacherId)
            ->select([
                'asignacion_docente.id',
                'asignacion_docente.activo',
                'asignacion_docente.asignado_en',
                'materia.id as materia_id',
                'materia.nombre as materia_nombre',
                'grupo.id as grupo_id',
                'grupo.nombre as grupo_nombre',
                'gestion_academica.id as gestion_id',
                'gestion_academica.nombre as gestion_nombre',
                'horario_clase.id as horario_id',
                'horario_clase.hora_inicio',
                'horario_clase.hora_fin',
                'aula.ubicacion as aula',
                'dia.nombre as dia',
                'turno.nombre as turno',
                'periodo.numero_periodo',
            ])
            ->orderBy('asignacion_docente.id')
            ->get();

        return [
            'materias' => $rows->unique('materia_id')->map(fn ($row): array => [
                'id' => $row->materia_id,
                'nombre' => $row->materia_nombre,
            ])->values(),
            'grupos' => $rows->unique('grupo_id')->map(fn ($row): array => [
                'id' => $row->grupo_id,
                'nombre' => $row->grupo_nombre,
            ])->values(),
            'horarios' => $rows->map(fn ($row): array => [
                'id' => $row->horario_id,
                'dia' => $row->dia,
                'turno' => $row->turno,
                'periodo' => $row->numero_periodo,
                'hora_inicio' => $row->hora_inicio,
                'hora_fin' => $row->hora_fin,
                'aula' => $row->aula,
                'materia' => [
                    'id' => $row->materia_id,
                    'nombre' => $row->materia_nombre,
                ],
                'grupo' => [
                    'id' => $row->grupo_id,
                    'nombre' => $row->grupo_nombre,
                ],
                'gestion' => [
                    'id' => $row->gestion_id,
                    'nombre' => $row->gestion_nombre,
                ],
                'activo' => (bool) $row->activo,
            ])->values(),
        ];
    }

    public function formatTeacher(DocenteModel $teacher, bool $includeAssignments = false): array
    {
        $data = [
            'id' => $teacher->id,
            'activo' => $teacher->activo,
            'contratado' => $teacher->contratado,
            'es_profesional_area' => $teacher->es_profesional_area,
            'tiene_maestria' => $teacher->tiene_maestria,
            'tiene_diplomado_educacion_superior' => $teacher->tiene_diplomado_educacion_superior,
            'cv_pdf' => [
                'tiene_pdf' => filled($teacher->cv_pdf_cloudinary_url),
                'nombre_original' => $teacher->cv_pdf_nombre_original,
                'url' => $teacher->cv_pdf_cloudinary_url,
            ],
            'persona' => [
                'id' => $teacher->persona?->id,
                'cedula_identidad' => $teacher->persona?->cedula_identidad,
                'nombres' => $teacher->persona?->nombres,
                'apellido_paterno' => $teacher->persona?->apellido_paterno,
                'apellido_materno' => $teacher->persona?->apellido_materno,
                'fecha_nacimiento' => $teacher->persona?->fecha_nacimiento,
                'sexo' => $teacher->persona?->sexo,
                'direccion' => $teacher->persona?->direccion,
                'telefono' => $teacher->persona?->telefono,
                'celular' => $teacher->persona?->celular,
                'correo' => $teacher->persona?->correo,
                'ciudad' => $teacher->persona?->ciudad,
            ],
            'usuario' => [
                'id' => $teacher->usuario?->id,
                'nombre_usuario' => $teacher->usuario?->nombre_usuario,
                'rol' => $teacher->usuario?->rol?->nombre,
                'activo' => $teacher->usuario?->activo,
                'correo_verificado' => $teacher->usuario?->correo_verificado,
            ],
            'creado_en' => $teacher->creado_en,
            'actualizado_en' => $teacher->actualizado_en,
        ];

        if ($includeAssignments) {
            $data['asignaciones'] = $this->assignmentSummary($teacher->id);
        }

        return $data;
    }

    private function storeCvPdf(int $teacherId, UploadedFile $file): void
    {
        $publicId = 'docente_'.$teacherId.'_cv_'.now()->format('YmdHis').'.pdf';
        $cloudinary = $this->cloudinary->uploadRawFile($file, $publicId, config('cloudinary.teachers_cv_folder'));

        DB::table('docente')->where('id', $teacherId)->update([
            'cv_pdf_cloudinary_public_id' => $cloudinary['public_id'],
            'cv_pdf_cloudinary_url' => $cloudinary['secure_url'],
            'cv_pdf_nombre_original' => $file->getClientOriginalName(),
            'actualizado_en' => now(),
        ]);
    }
}

<?php

namespace App\Services\Applicants;

use App\Models\GestionAcademicaModel;
use App\Models\PostulanteModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ApplicantService
{
    public function register(array $data): PostulanteModel
    {
        return DB::transaction(function () use ($data): PostulanteModel {
            $gestionId = $this->resolveGestionAcademicaId($data['gestion_academica_id'] ?? null);

            $personaId = DB::table('persona')->insertGetId([
                'cedula_identidad' => $data['cedula_identidad'],
                'nombres' => $data['nombres'],
                'apellido_paterno' => $data['apellido_paterno'],
                'apellido_materno' => $data['apellido_materno'],
                'fecha_nacimiento' => $data['fecha_nacimiento'],
                'sexo' => $data['sexo'],
                'direccion' => $data['direccion'],
                'telefono' => $data['telefono'],
                'correo' => $data['correo'],
                'ciudad' => $data['ciudad'],
                'creado_en' => now(),
            ]);

            $postulanteId = DB::table('postulante')->insertGetId([
                'persona_id' => $personaId,
                'gestion_academica_id' => $gestionId,
                'colegio_procedencia' => $data['colegio_procedencia'],
                'estado_requisitos' => 'pendiente',
                'estado_pago' => 'pendiente',
                'estado_postulante' => 'registrado',
                'creado_en' => now(),
            ]);

            DB::table('postulacion')->insert([
                'postulante_id' => $postulanteId,
                'primera_carrera_id' => $data['primera_carrera_id'],
                'segunda_carrera_id' => $data['segunda_carrera_id'],
            ]);

            return $this->findDetailed($postulanteId);
        });
    }

    public function list(array $filters): LengthAwarePaginator
    {
        return PostulanteModel::query()
            ->with([
                'persona',
                'gestionAcademica',
                'postulacion.primeraCarrera',
                'postulacion.segundaCarrera',
                'postulacion.carreraAsignada',
            ])
            ->when($filters['gestion_academica_id'] ?? null, function (Builder $query, int|string $gestionId): void {
                $query->where('gestion_academica_id', (int) $gestionId);
            })
            ->when($filters['estado'] ?? null, function (Builder $query, string $estado): void {
                $query->where('estado_postulante', $estado);
            })
            ->when($filters['ci'] ?? null, function (Builder $query, string $ci): void {
                $query->whereHas('persona', fn (Builder $personQuery) => $personQuery
                    ->where('cedula_identidad', 'ILIKE', "%{$ci}%"));
            })
            ->when($filters['nombre'] ?? null, function (Builder $query, string $nombre): void {
                $query->whereHas('persona', function (Builder $personQuery) use ($nombre): void {
                    $personQuery->where('nombres', 'ILIKE', "%{$nombre}%")
                        ->orWhere('apellido_paterno', 'ILIKE', "%{$nombre}%")
                        ->orWhere('apellido_materno', 'ILIKE', "%{$nombre}%");
                });
            })
            ->when($filters['buscar'] ?? null, function (Builder $query, string $search): void {
                $query->whereHas('persona', function (Builder $personQuery) use ($search): void {
                    $personQuery->where('cedula_identidad', 'ILIKE', "%{$search}%")
                        ->orWhere('nombres', 'ILIKE', "%{$search}%")
                        ->orWhere('apellido_paterno', 'ILIKE', "%{$search}%")
                        ->orWhere('apellido_materno', 'ILIKE', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate((int) ($filters['por_pagina'] ?? 15));
    }

    public function findDetailed(int $id): PostulanteModel
    {
        return PostulanteModel::query()
            ->with([
                'persona',
                'gestionAcademica',
                'documentos',
                'pagoStripe',
                'postulacion.primeraCarrera',
                'postulacion.segundaCarrera',
                'postulacion.carreraAsignada',
            ])
            ->findOrFail($id);
    }

    public function update(int $id, array $data): PostulanteModel
    {
        return DB::transaction(function () use ($id, $data): PostulanteModel {
            $postulante = $this->findDetailed($id);

            $personData = array_intersect_key($data, array_flip([
                'cedula_identidad',
                'nombres',
                'apellido_paterno',
                'apellido_materno',
                'fecha_nacimiento',
                'sexo',
                'direccion',
                'telefono',
                'correo',
                'ciudad',
            ]));

            if ($personData !== []) {
                $personData['actualizado_en'] = now();
                DB::table('persona')->where('id', $postulante->persona_id)->update($personData);
            }

            $applicantData = array_intersect_key($data, array_flip([
                'gestion_academica_id',
                'colegio_procedencia',
                'estado_requisitos',
                'estado_pago',
                'estado_postulante',
                'observacion',
            ]));

            if (array_key_exists('gestion_academica_id', $applicantData)) {
                $applicantData['gestion_academica_id'] = $this->resolveGestionAcademicaId($applicantData['gestion_academica_id']);
            }

            if ($applicantData !== []) {
                $applicantData['actualizado_en'] = now();
                DB::table('postulante')->where('id', $postulante->id)->update($applicantData);
            }

            $firstCareerId = $data['primera_carrera_id'] ?? $postulante->postulacion?->primera_carrera_id;
            $secondCareerId = $data['segunda_carrera_id'] ?? $postulante->postulacion?->segunda_carrera_id;

            if ($firstCareerId !== null && $secondCareerId !== null && (int) $firstCareerId === (int) $secondCareerId) {
                throw new RuntimeException('La primera y segunda opcion de carrera deben ser diferentes.');
            }

            $applicationData = array_intersect_key($data, array_flip([
                'primera_carrera_id',
                'segunda_carrera_id',
            ]));

            if ($applicationData !== []) {
                DB::table('postulacion')->where('postulante_id', $postulante->id)->update($applicationData);
            }

            return $this->findDetailed($id);
        });
    }

    public function logicalDelete(int $id, ?string $observacion): PostulanteModel
    {
        $postulante = $this->findDetailed($id);

        DB::table('postulante')
            ->where('id', $postulante->id)
            ->update([
                'estado_postulante' => 'rechazado',
                'observacion' => $observacion ?: 'Eliminacion logica de postulante.',
                'actualizado_en' => now(),
            ]);

        return $this->findDetailed($id);
    }

    public function formatListItem(PostulanteModel $postulante): array
    {
        return [
            'id' => $postulante->id,
            'cedula_identidad' => $postulante->persona?->cedula_identidad,
            'nombres' => $postulante->persona?->nombres,
            'apellido_paterno' => $postulante->persona?->apellido_paterno,
            'apellido_materno' => $postulante->persona?->apellido_materno,
            'correo' => $postulante->persona?->correo,
            'telefono' => $postulante->persona?->telefono,
            'ciudad' => $postulante->persona?->ciudad,
            'colegio_procedencia' => $postulante->colegio_procedencia,
            'estado_requisitos' => $postulante->estado_requisitos,
            'estado_pago' => $postulante->estado_pago,
            'estado_postulante' => $postulante->estado_postulante,
            'gestion_academica' => $this->formatGestionAcademica($postulante),
            'postulacion' => $this->formatPostulacion($postulante),
        ];
    }

    public function formatDetail(PostulanteModel $postulante): array
    {
        return [
            ...$this->formatListItem($postulante),
            'persona' => [
                'id' => $postulante->persona?->id,
                'cedula_identidad' => $postulante->persona?->cedula_identidad,
                'nombres' => $postulante->persona?->nombres,
                'apellido_paterno' => $postulante->persona?->apellido_paterno,
                'apellido_materno' => $postulante->persona?->apellido_materno,
                'fecha_nacimiento' => $postulante->persona?->fecha_nacimiento,
                'sexo' => $postulante->persona?->sexo,
                'direccion' => $postulante->persona?->direccion,
                'telefono' => $postulante->persona?->telefono,
                'correo' => $postulante->persona?->correo,
                'ciudad' => $postulante->persona?->ciudad,
            ],
            'requisitos' => [
                'estado_requisitos' => $postulante->estado_requisitos,
                'observacion' => $postulante->observacion,
            ],
            'documentos' => $postulante->documentos->map(fn ($documento): array => [
                'id' => $documento->id,
                'tipo_documento' => $documento->tipo_documento,
                'cloudinary_public_id' => $documento->cloudinary_public_id,
                'cloudinary_url' => $documento->cloudinary_url,
                'formato_archivo' => $documento->formato_archivo,
                'estado_revision' => $documento->estado_revision,
                'observacion' => $documento->observacion,
                'subido_en' => $documento->subido_en,
            ])->values(),
            'pago' => $postulante->pagoStripe ? [
                'id' => $postulante->pagoStripe->id,
                'monto' => $postulante->pagoStripe->monto,
                'moneda' => $postulante->pagoStripe->moneda,
                'estado_pago' => $postulante->pagoStripe->estado_pago,
                'fecha_pago' => $postulante->pagoStripe->fecha_pago,
                'validado_por_usuario_id' => $postulante->pagoStripe->validado_por_usuario_id,
                'validado_en' => $postulante->pagoStripe->validado_en,
            ] : null,
            'creado_en' => $postulante->creado_en,
            'actualizado_en' => $postulante->actualizado_en,
        ];
    }

    private function resolveGestionAcademicaId(int|string|null $gestionId): int
    {
        if ($gestionId !== null) {
            $exists = GestionAcademicaModel::query()->whereKey((int) $gestionId)->exists();

            if (! $exists) {
                throw new RuntimeException('La gestion academica indicada no existe.');
            }

            return (int) $gestionId;
        }

        $activeGestionId = GestionAcademicaModel::query()
            ->where('activa', true)
            ->orderByDesc('anio')
            ->orderByDesc('numero_gestion')
            ->value('id');

        if (! $activeGestionId) {
            throw new RuntimeException('No existe una gestion academica activa para registrar postulantes.');
        }

        return (int) $activeGestionId;
    }

    private function formatGestionAcademica(PostulanteModel $postulante): ?array
    {
        if (! $postulante->gestionAcademica) {
            return null;
        }

        return [
            'id' => $postulante->gestionAcademica->id,
            'anio' => $postulante->gestionAcademica->anio,
            'numero_gestion' => $postulante->gestionAcademica->numero_gestion,
            'nombre' => $postulante->gestionAcademica->nombre,
            'activa' => $postulante->gestionAcademica->activa,
        ];
    }

    private function formatPostulacion(PostulanteModel $postulante): ?array
    {
        if (! $postulante->postulacion) {
            return null;
        }

        return [
            'id' => $postulante->postulacion->id,
            'primera_carrera' => [
                'id' => $postulante->postulacion->primeraCarrera?->id,
                'nombre' => $postulante->postulacion->primeraCarrera?->nombre,
            ],
            'segunda_carrera' => [
                'id' => $postulante->postulacion->segundaCarrera?->id,
                'nombre' => $postulante->postulacion->segundaCarrera?->nombre,
            ],
            'carrera_asignada' => $postulante->postulacion->carreraAsignada ? [
                'id' => $postulante->postulacion->carreraAsignada->id,
                'nombre' => $postulante->postulacion->carreraAsignada->nombre,
            ] : null,
            'promedio_final' => $postulante->postulacion->promedio_final,
            'estado_final' => $postulante->postulacion->estado_final,
        ];
    }
}

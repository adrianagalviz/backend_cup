<?php

namespace App\Services\Documents;

use App\Models\DocumentoPostulanteModel;
use App\Models\PostulanteModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class RequirementReviewService
{
    public function listRequirements(array $filters): LengthAwarePaginator
    {
        return $this->requirementQuery()
            ->when($filters['estado_requisitos'] ?? null, fn (Builder $query, string $state) => $query->where('estado_requisitos', $state))
            ->when($filters['gestion_academica_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('gestion_academica_id', (int) $id))
            ->when($filters['buscar'] ?? null, function (Builder $query, string $search): void {
                $query->whereHas('persona', function (Builder $personQuery) use ($search): void {
                    $personQuery->where('cedula_identidad', 'ILIKE', "%{$search}%")
                        ->orWhere('nombres', 'ILIKE', "%{$search}%")
                        ->orWhere('apellido_paterno', 'ILIKE', "%{$search}%")
                        ->orWhere('apellido_materno', 'ILIKE', "%{$search}%");
                });
            })
            ->when($filters['documento'] ?? null, function (Builder $query, string $document): void {
                if ($document === 'con_documento') {
                    $query->whereHas('documentos', fn (Builder $documentQuery) => $documentQuery->where('tipo_documento', 'titulo_bachiller'));

                    return;
                }

                $query->whereDoesntHave('documentos', fn (Builder $documentQuery) => $documentQuery->where('tipo_documento', 'titulo_bachiller'));
            })
            ->orderByRaw("CASE estado_requisitos WHEN 'pendiente' THEN 0 WHEN 'rechazado' THEN 1 ELSE 2 END")
            ->orderByDesc('id')
            ->paginate((int) ($filters['por_pagina'] ?? 15));
    }

    public function findRequirement(int $postulanteId): PostulanteModel
    {
        return $this->requirementQuery()->findOrFail($postulanteId);
    }

    public function formatRequirement(PostulanteModel $postulante): array
    {
        $document = $this->latestBachelorTitle($postulante);

        return [
            'id' => $postulante->id,
            'estado_requisitos' => $postulante->estado_requisitos,
            'estado_pago' => $postulante->estado_pago,
            'estado_postulante' => $postulante->estado_postulante,
            'observacion' => $postulante->observacion,
            'creado_en' => $postulante->creado_en,
            'persona' => [
                'id' => $postulante->persona?->id,
                'cedula_identidad' => $postulante->persona?->cedula_identidad,
                'nombres' => $postulante->persona?->nombres,
                'apellido_paterno' => $postulante->persona?->apellido_paterno,
                'apellido_materno' => $postulante->persona?->apellido_materno,
                'correo' => $postulante->persona?->correo,
            ],
            'gestion_academica' => [
                'id' => $postulante->gestionAcademica?->id,
                'anio' => $postulante->gestionAcademica?->anio,
                'numero_gestion' => $postulante->gestionAcademica?->numero_gestion,
                'nombre' => $postulante->gestionAcademica?->nombre,
            ],
            'documento' => $document ? [
                'id' => $document->id,
                'tipo_documento' => $document->tipo_documento,
                'cloudinary_public_id' => $document->cloudinary_public_id,
                'cloudinary_url' => $document->cloudinary_url,
                'formato_archivo' => $document->formato_archivo,
                'estado_revision' => $document->estado_revision,
                'observacion' => $document->observacion,
                'subido_en' => $document->subido_en,
            ] : null,
        ];
    }

    private function requirementQuery(): Builder
    {
        return PostulanteModel::query()
            ->with([
                'persona',
                'gestionAcademica',
                'documentos' => fn ($query) => $query
                    ->where('tipo_documento', 'titulo_bachiller')
                    ->orderByDesc('id'),
            ]);
    }

    private function latestBachelorTitle(PostulanteModel $postulante): ?DocumentoPostulanteModel
    {
        return $postulante->documentos
            ->first(fn (DocumentoPostulanteModel $document) => $document->tipo_documento === 'titulo_bachiller');
    }
}

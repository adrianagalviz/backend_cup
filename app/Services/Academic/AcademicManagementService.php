<?php

namespace App\Services\Academic;

use App\Models\CarreraModel;
use App\Models\CupoCarreraModel;
use App\Models\GestionAcademicaModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AcademicManagementService
{
    public function createGestion(array $data): GestionAcademicaModel
    {
        $id = DB::table('gestion_academica')->insertGetId([
            'anio' => $data['anio'],
            'numero_gestion' => $data['numero_gestion'],
            'nombre' => $data['nombre'],
            'fecha_inicio' => $data['fecha_inicio'] ?? null,
            'fecha_fin' => $data['fecha_fin'] ?? null,
            'activa' => (bool) ($data['activa'] ?? true),
            'creado_en' => now(),
        ]);

        return $this->findGestion($id);
    }

    public function listGestiones(array $filters): LengthAwarePaginator
    {
        return GestionAcademicaModel::query()
            ->when(array_key_exists('activa', $filters), function (Builder $query) use ($filters): void {
                $query->where('activa', filter_var($filters['activa'], FILTER_VALIDATE_BOOLEAN));
            })
            ->orderByDesc('anio')
            ->orderByDesc('numero_gestion')
            ->paginate((int) ($filters['por_pagina'] ?? 15));
    }

    public function currentGestion(): ?GestionAcademicaModel
    {
        return GestionAcademicaModel::query()
            ->where('activa', true)
            ->orderByDesc('anio')
            ->orderByDesc('numero_gestion')
            ->first();
    }

    public function createCareer(array $data): CarreraModel
    {
        $id = DB::table('carrera')->insertGetId([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'activa' => (bool) ($data['activa'] ?? true),
            'creado_en' => now(),
        ]);

        return $this->findCareer($id);
    }

    public function updateCareer(int $id, array $data): CarreraModel
    {
        $career = $this->findCareer($id);

        $careerData = array_intersect_key($data, array_flip([
            'nombre',
            'descripcion',
            'activa',
        ]));

        if ($careerData !== []) {
            DB::table('carrera')->where('id', $career->id)->update($careerData);
        }

        return $this->findCareer($id);
    }

    public function listCareers(array $filters): LengthAwarePaginator
    {
        return CarreraModel::query()
            ->when(array_key_exists('activa', $filters), function (Builder $query) use ($filters): void {
                $query->where('activa', filter_var($filters['activa'], FILTER_VALIDATE_BOOLEAN));
            })
            ->when($filters['buscar'] ?? null, function (Builder $query, string $search): void {
                $query->where('nombre', 'ILIKE', "%{$search}%");
            })
            ->orderBy('nombre')
            ->paginate((int) ($filters['por_pagina'] ?? 15));
    }

    public function createQuota(array $data): CupoCarreraModel
    {
        $this->ensureUniqueQuota((int) $data['carrera_id'], (int) $data['gestion_academica_id']);

        $id = DB::table('cupo_carrera')->insertGetId([
            'carrera_id' => $data['carrera_id'],
            'gestion_academica_id' => $data['gestion_academica_id'],
            'cantidad_cupos' => $data['cantidad_cupos'],
            'creado_en' => now(),
        ]);

        return $this->findQuota($id);
    }

    public function updateQuota(int $id, array $data): CupoCarreraModel
    {
        $quota = $this->findQuota($id);

        $careerId = (int) ($data['carrera_id'] ?? $quota->carrera_id);
        $gestionId = (int) ($data['gestion_academica_id'] ?? $quota->gestion_academica_id);
        $this->ensureUniqueQuota($careerId, $gestionId, $quota->id);

        $quotaData = array_intersect_key($data, array_flip([
            'carrera_id',
            'gestion_academica_id',
            'cantidad_cupos',
        ]));

        if ($quotaData !== []) {
            $quotaData['actualizado_en'] = now();
            DB::table('cupo_carrera')->where('id', $quota->id)->update($quotaData);
        }

        return $this->findQuota($id);
    }

    public function listQuotas(array $filters): LengthAwarePaginator
    {
        return CupoCarreraModel::query()
            ->with(['carrera', 'gestionAcademica'])
            ->when($filters['carrera_id'] ?? null, function (Builder $query, int|string $careerId): void {
                $query->where('carrera_id', (int) $careerId);
            })
            ->when($filters['gestion_academica_id'] ?? null, function (Builder $query, int|string $gestionId): void {
                $query->where('gestion_academica_id', (int) $gestionId);
            })
            ->orderByDesc('gestion_academica_id')
            ->orderBy('carrera_id')
            ->paginate((int) ($filters['por_pagina'] ?? 15));
    }

    public function findGestion(int $id): GestionAcademicaModel
    {
        return GestionAcademicaModel::query()->findOrFail($id);
    }

    public function findCareer(int $id): CarreraModel
    {
        return CarreraModel::query()->findOrFail($id);
    }

    public function findQuota(int $id): CupoCarreraModel
    {
        return CupoCarreraModel::query()
            ->with(['carrera', 'gestionAcademica'])
            ->findOrFail($id);
    }

    public function formatGestion(GestionAcademicaModel $gestion): array
    {
        return [
            'id' => $gestion->id,
            'anio' => $gestion->anio,
            'numero_gestion' => $gestion->numero_gestion,
            'nombre' => $gestion->nombre,
            'fecha_inicio' => $gestion->fecha_inicio,
            'fecha_fin' => $gestion->fecha_fin,
            'activa' => $gestion->activa,
            'creado_en' => $gestion->creado_en,
        ];
    }

    public function formatCareer(CarreraModel $career): array
    {
        return [
            'id' => $career->id,
            'nombre' => $career->nombre,
            'descripcion' => $career->descripcion,
            'activa' => $career->activa,
            'creado_en' => $career->creado_en,
        ];
    }

    public function formatQuota(CupoCarreraModel $quota): array
    {
        $occupied = $this->occupiedQuotas($quota);
        $available = max(0, (int) $quota->cantidad_cupos - $occupied);

        return [
            'id' => $quota->id,
            'cantidad_cupos' => $quota->cantidad_cupos,
            'cupos_ocupados' => $occupied,
            'cupos_disponibles' => $available,
            'carrera' => [
                'id' => $quota->carrera?->id,
                'nombre' => $quota->carrera?->nombre,
                'activa' => $quota->carrera?->activa,
            ],
            'gestion_academica' => [
                'id' => $quota->gestionAcademica?->id,
                'anio' => $quota->gestionAcademica?->anio,
                'numero_gestion' => $quota->gestionAcademica?->numero_gestion,
                'nombre' => $quota->gestionAcademica?->nombre,
                'activa' => $quota->gestionAcademica?->activa,
            ],
            'creado_en' => $quota->creado_en,
            'actualizado_en' => $quota->actualizado_en,
        ];
    }

    private function occupiedQuotas(CupoCarreraModel $quota): int
    {
        return DB::table('postulacion')
            ->join('postulante', 'postulante.id', '=', 'postulacion.postulante_id')
            ->where('postulante.gestion_academica_id', $quota->gestion_academica_id)
            ->where('postulacion.carrera_asignada_id', $quota->carrera_id)
            ->count();
    }

    private function ensureUniqueQuota(int $careerId, int $gestionId, ?int $ignoreId = null): void
    {
        $exists = DB::table('cupo_carrera')
            ->where('carrera_id', $careerId)
            ->where('gestion_academica_id', $gestionId)
            ->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))
            ->exists();

        if ($exists) {
            throw new RuntimeException('La carrera ya tiene cupo registrado para esa gestion academica.');
        }
    }
}

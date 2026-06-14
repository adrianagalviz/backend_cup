<?php

namespace App\Services\Audit;

use App\Models\BitacoraSistemaModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AuditLogService
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'password_hash',
        'token',
        'firebase_token',
        'authorization',
    ];

    public function list(array $filters): LengthAwarePaginator
    {
        return BitacoraSistemaModel::query()
            ->with(['usuario.rol', 'usuario.persona'])
            ->when($filters['usuario_id'] ?? null, fn (Builder $query, int|string $userId) => $query->where('usuario_id', $userId))
            ->when($filters['modulo'] ?? null, fn (Builder $query, string $module) => $query->where('modulo', $module))
            ->when($filters['metodo_http'] ?? null, fn (Builder $query, string $method) => $query->where('metodo_http', strtoupper($method)))
            ->when($filters['buscar'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('ruta', 'ILIKE', "%{$search}%")
                        ->orWhere('accion', 'ILIKE', "%{$search}%")
                        ->orWhere('modulo', 'ILIKE', "%{$search}%")
                        ->orWhereHas('usuario', function (Builder $userQuery) use ($search): void {
                            $userQuery->where('nombre_usuario', 'ILIKE', "%{$search}%")
                                ->orWhereHas('persona', function (Builder $personQuery) use ($search): void {
                                    $personQuery->where('nombres', 'ILIKE', "%{$search}%")
                                        ->orWhere('apellido_paterno', 'ILIKE', "%{$search}%")
                                        ->orWhere('correo', 'ILIKE', "%{$search}%");
                                });
                        });
                });
            })
            ->when($filters['fecha_desde'] ?? null, fn (Builder $query, string $date) => $query->whereDate('creado_en', '>=', $date))
            ->when($filters['fecha_hasta'] ?? null, fn (Builder $query, string $date) => $query->whereDate('creado_en', '<=', $date))
            ->orderByDesc('creado_en')
            ->orderByDesc('id')
            ->paginate(
                (int) ($filters['por_pagina'] ?? 15),
                ['*'],
                'page',
                (int) ($filters['pagina'] ?? 1)
            );
    }

    public function record(Request $request, int $statusCode): void
    {
        $usuario = $request->attributes->get('usuario_autenticado');

        if (!$usuario || !$this->shouldRecord($request)) {
            return;
        }

        BitacoraSistemaModel::query()->create([
            'usuario_id' => $usuario->id,
            'modulo' => $this->moduleFromPath($request),
            'accion' => $this->actionFromMethod($request->method()),
            'metodo_http' => $request->method(),
            'ruta' => '/'.$request->path(),
            'estado_http' => $statusCode,
            'direccion_ip' => $request->ip(),
            'agente_usuario' => Str::limit((string) $request->userAgent(), 500, ''),
            'datos' => $this->safePayload($request),
            'creado_en' => now(),
        ]);
    }

    public function format(BitacoraSistemaModel $log): array
    {
        return [
            'id' => $log->id,
            'modulo' => $log->modulo,
            'accion' => $log->accion,
            'metodo_http' => $log->metodo_http,
            'ruta' => $log->ruta,
            'estado_http' => $log->estado_http,
            'direccion_ip' => $log->direccion_ip,
            'agente_usuario' => $log->agente_usuario,
            'datos' => $log->datos,
            'creado_en' => optional($log->creado_en)->toISOString(),
            'usuario' => [
                'id' => $log->usuario?->id,
                'nombre_usuario' => $log->usuario?->nombre_usuario,
                'rol' => $log->usuario?->rol?->nombre,
                'persona' => [
                    'nombres' => $log->usuario?->persona?->nombres,
                    'apellido_paterno' => $log->usuario?->persona?->apellido_paterno,
                    'apellido_materno' => $log->usuario?->persona?->apellido_materno,
                    'correo' => $log->usuario?->persona?->correo,
                ],
            ],
        ];
    }

    private function shouldRecord(Request $request): bool
    {
        return in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)
            && !$request->is('api/v1/bitacora*');
    }

    private function moduleFromPath(Request $request): string
    {
        $segments = $request->segments();
        $module = $segments[2] ?? 'sistema';

        return Str::of($module)->replace('-', ' ')->title()->toString();
    }

    private function actionFromMethod(string $method): string
    {
        return match ($method) {
            'POST' => 'crear',
            'PUT', 'PATCH' => 'actualizar',
            'DELETE' => 'eliminar',
            default => strtolower($method),
        };
    }

    private function safePayload(Request $request): array
    {
        $payload = $request->except(self::SENSITIVE_KEYS);

        foreach (self::SENSITIVE_KEYS as $key) {
            Arr::forget($payload, $key);
        }

        return $payload;
    }
}

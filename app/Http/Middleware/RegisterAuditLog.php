<?php

namespace App\Http\Middleware;

use App\Services\Audit\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RegisterAuditLog
{
    public function __construct(
        private readonly AuditLogService $audit,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $this->audit->record($request, $response->getStatusCode());
        } catch (Throwable) {
            // La bitacora no debe interrumpir la operacion principal.
        }

        return $response;
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\UsuarioModel;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentPaymentCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->attributes->get('usuario_autenticado');

        if (! $usuario instanceof UsuarioModel || $usuario->rol?->nombre !== 'alumno') {
            return $next($request);
        }

        $usuario->loadMissing('alumno.postulante');
        $postulante = $usuario->alumno?->postulante;

        if ($postulante?->estado_pago === 'pagado') {
            return $next($request);
        }

        return ApiResponse::error('Debes completar el pago de postulacion para acceder a este recurso.', [
            'estado_pago' => $postulante?->estado_pago ?? 'pendiente',
            'postulante_id' => $postulante?->id,
        ], 403);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Students\ApplicantConversionService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ApplicantConversionController extends Controller
{
    public function __construct(
        private readonly ApplicantConversionService $conversion,
    ) {
    }

    public function convertToStudent(Request $request, int $id): JsonResponse
    {
        $administrator = $request->attributes->get('usuario_autenticado');

        try {
            $student = $this->conversion->convertToStudent($id, $administrator);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Postulante no encontrado.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Postulante convertido a alumno correctamente.', [
            'alumno' => $this->conversion->formatStudent($student),
        ], 201);
    }
}

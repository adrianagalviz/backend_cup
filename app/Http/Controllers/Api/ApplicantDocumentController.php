<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Services\Applicants\ApplicantService;
use App\Services\Documents\ApplicantDocumentService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

class ApplicantDocumentController extends Controller
{
    public function __construct(
        private readonly ApplicantDocumentService $documents,
        private readonly ApplicantService $applicants,
    ) {
    }

    public function store(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'titulo_bachiller' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $document = $this->documents->uploadBachelorTitle($id, $request->file('titulo_bachiller'));
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Postulante no encontrado.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success(
            'Titulo de bachiller subido correctamente.',
            ['documento' => $this->documents->formatDocument($document)],
            201
        );
    }

    public function index(int $id): JsonResponse
    {
        try {
            $documents = $this->documents->listByApplicant($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Postulante no encontrado.', [], 404);
        }

        return ApiResponse::success('Documentos del postulante obtenidos correctamente.', [
            'documentos' => $documents
                ->map(fn ($document) => $this->documents->formatDocument($document))
                ->values(),
        ]);
    }

    public function validateRequirements(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'estado_revision' => ['required', Rule::in(['aprobado', 'rechazado'])],
            'observacion' => ['nullable', 'string'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $postulante = $this->documents->validateRequirements(
                $id,
                $validator->validated()['estado_revision'],
                $validator->validated()['observacion'] ?? null
            );
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Postulante no encontrado.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Requisitos del postulante validados correctamente.', [
            'postulante' => $this->applicants->formatDetail($postulante),
        ]);
    }

    private function messages(): array
    {
        return [
            'titulo_bachiller.required' => 'La imagen del titulo de bachiller es obligatoria.',
            'titulo_bachiller.image' => 'El archivo del titulo de bachiller debe ser una imagen.',
            'titulo_bachiller.mimes' => 'La imagen del titulo de bachiller debe ser JPG, JPEG, PNG o WEBP.',
            'titulo_bachiller.max' => 'La imagen del titulo de bachiller no debe superar 5 MB.',
            'estado_revision.required' => 'El estado de revision es obligatorio.',
            'estado_revision.in' => 'El estado de revision debe ser aprobado o rechazado.',
        ];
    }
}

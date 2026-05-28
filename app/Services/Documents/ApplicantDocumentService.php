<?php

namespace App\Services\Documents;

use App\Models\DocumentoPostulanteModel;
use App\Models\PostulanteModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ApplicantDocumentService
{
    public function __construct(
        private readonly CloudinaryService $cloudinary,
    ) {
    }

    public function uploadBachelorTitle(int $postulanteId, UploadedFile $file): DocumentoPostulanteModel
    {
        $postulante = PostulanteModel::query()->findOrFail($postulanteId);

        $publicId = "postulante_{$postulante->id}_titulo_bachiller_".now()->format('YmdHis');
        $cloudinary = $this->cloudinary->uploadImage($file, $publicId);

        $documentId = DB::table('documento_postulante')->insertGetId([
            'postulante_id' => $postulante->id,
            'tipo_documento' => 'titulo_bachiller',
            'cloudinary_public_id' => $cloudinary['public_id'],
            'cloudinary_url' => $cloudinary['secure_url'],
            'formato_archivo' => $cloudinary['format'],
            'estado_revision' => 'pendiente',
            'subido_en' => now(),
        ]);

        DB::table('postulante')
            ->where('id', $postulante->id)
            ->update([
                'estado_requisitos' => 'pendiente',
                'actualizado_en' => now(),
            ]);

        return $this->findDocument($documentId);
    }

    public function listByApplicant(int $postulanteId): Collection
    {
        PostulanteModel::query()->findOrFail($postulanteId);

        return DocumentoPostulanteModel::query()
            ->where('postulante_id', $postulanteId)
            ->orderByDesc('id')
            ->get();
    }

    public function validateRequirements(int $postulanteId, string $status, ?string $observation): PostulanteModel
    {
        return DB::transaction(function () use ($postulanteId, $status, $observation): PostulanteModel {
            $postulante = PostulanteModel::query()->findOrFail($postulanteId);

            $document = DocumentoPostulanteModel::query()
                ->where('postulante_id', $postulante->id)
                ->where('tipo_documento', 'titulo_bachiller')
                ->orderByDesc('id')
                ->first();

            if (! $document) {
                throw new RuntimeException('El postulante no tiene imagen del titulo de bachiller registrada.');
            }

            DB::table('documento_postulante')
                ->where('id', $document->id)
                ->update([
                    'estado_revision' => $status,
                    'observacion' => $observation,
                ]);

            DB::table('postulante')
                ->where('id', $postulante->id)
                ->update([
                    'estado_requisitos' => $status,
                    'estado_postulante' => $status === 'aprobado' ? 'pendiente_pago' : 'rechazado',
                    'observacion' => $observation,
                    'actualizado_en' => now(),
                ]);

            return PostulanteModel::query()
                ->with(['persona', 'gestionAcademica', 'documentos', 'pagoStripe', 'postulacion.primeraCarrera', 'postulacion.segundaCarrera'])
                ->findOrFail($postulante->id);
        });
    }

    public function formatDocument(DocumentoPostulanteModel $document): array
    {
        return [
            'id' => $document->id,
            'postulante_id' => $document->postulante_id,
            'tipo_documento' => $document->tipo_documento,
            'cloudinary_public_id' => $document->cloudinary_public_id,
            'cloudinary_url' => $document->cloudinary_url,
            'formato_archivo' => $document->formato_archivo,
            'estado_revision' => $document->estado_revision,
            'observacion' => $document->observacion,
            'subido_en' => $document->subido_en,
        ];
    }

    private function findDocument(int $id): DocumentoPostulanteModel
    {
        return DocumentoPostulanteModel::query()->findOrFail($id);
    }
}

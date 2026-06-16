<?php

namespace App\Services\Documents;

use Illuminate\Http\UploadedFile;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class CloudinaryService
{
    public function uploadImage(UploadedFile $file, string $publicId): array
    {
        return $this->upload($file, $publicId, config('cloudinary.folder'), 'image', 'No se pudo subir la imagen a Cloudinary.');
    }

    public function uploadRawFile(UploadedFile $file, string $publicId, string $folder): array
    {
        return $this->upload($file, $publicId, $folder, 'raw', 'No se pudo subir el archivo a Cloudinary.');
    }

    private function signature(array $params, string $apiSecret): string
    {
        ksort($params);

        $payload = collect($params)
            ->map(fn ($value, string $key): string => "{$key}={$value}")
            ->implode('&');

        return sha1($payload.$apiSecret);
    }

    private function upload(UploadedFile $file, string $publicId, string $folder, string $resourceType, string $errorMessage): array
    {
        $cloudName = config('cloudinary.cloud_name');
        $apiKey = config('cloudinary.api_key');
        $apiSecret = config('cloudinary.api_secret');

        if (! $cloudName || ! $apiKey || ! $apiSecret) {
            throw new RuntimeException('Cloudinary no esta configurado correctamente.');
        }

        $timestamp = time();
        $params = [
            'folder' => $folder,
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ];

        try {
            $response = Http::timeout(90)
                ->connectTimeout(30)
                ->retry(2, 1500, throw: true)
                ->withOptions([
                    'force_ip_resolve' => 'v4',
                ])
                ->asMultipart()
                ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post("https://api.cloudinary.com/v1_1/{$cloudName}/{$resourceType}/upload", [
                    'api_key' => $apiKey,
                    'timestamp' => (string) $timestamp,
                    'folder' => $folder,
                    'public_id' => $publicId,
                    'signature' => $this->signature($params, $apiSecret),
                ]);
        } catch (ConnectionException) {
            throw new RuntimeException('No se pudo conectar con Cloudinary. Verifica tu conexion a internet e intenta nuevamente.');
        } catch (Throwable) {
            throw new RuntimeException($errorMessage);
        }

        if (! $response->successful()) {
            throw new RuntimeException($errorMessage);
        }

        $data = $response->json();

        if (empty($data['secure_url']) || empty($data['public_id'])) {
            throw new RuntimeException('Cloudinary no devolvio una URL segura valida.');
        }

        return [
            'public_id' => $data['public_id'],
            'secure_url' => $data['secure_url'],
            'format' => $data['format'] ?? $file->extension(),
        ];
    }
}

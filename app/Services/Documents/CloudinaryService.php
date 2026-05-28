<?php

namespace App\Services\Documents;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloudinaryService
{
    public function uploadImage(UploadedFile $file, string $publicId): array
    {
        $cloudName = config('cloudinary.cloud_name');
        $apiKey = config('cloudinary.api_key');
        $apiSecret = config('cloudinary.api_secret');
        $folder = config('cloudinary.folder');

        if (! $cloudName || ! $apiKey || ! $apiSecret) {
            throw new RuntimeException('Cloudinary no esta configurado correctamente.');
        }

        $timestamp = time();
        $params = [
            'folder' => $folder,
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ];

        $response = Http::asMultipart()
            ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
            ->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload", [
                'api_key' => $apiKey,
                'timestamp' => (string) $timestamp,
                'folder' => $folder,
                'public_id' => $publicId,
                'signature' => $this->signature($params, $apiSecret),
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('No se pudo subir la imagen a Cloudinary.');
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

    private function signature(array $params, string $apiSecret): string
    {
        ksort($params);

        $payload = collect($params)
            ->map(fn ($value, string $key): string => "{$key}={$value}")
            ->implode('&');

        return sha1($payload.$apiSecret);
    }
}

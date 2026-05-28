<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class FirebaseTokenVerifier
{
    private const CERTS_URL = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';

    public function verify(string $token): array
    {
        $projectId = (string) config('services.firebase.project_id');

        if ($projectId === '') {
            throw new RuntimeException('FIREBASE_PROJECT_ID no esta configurado.');
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $this->splitToken($token);
        $header = $this->decodeJson($encodedHeader);
        $payload = $this->decodeJson($encodedPayload);

        $this->validatePayload($payload, $projectId);

        $kid = $header['kid'] ?? null;
        $algorithm = $header['alg'] ?? null;

        if (!$kid || $algorithm !== 'RS256') {
            throw new RuntimeException('Token de Firebase con encabezado invalido.');
        }

        try {
            $certificates = Http::timeout(5)->get(self::CERTS_URL)->throw()->json();
        } catch (Throwable) {
            throw new RuntimeException('No se pudo obtener certificados publicos de Firebase.');
        }

        $certificate = $certificates[$kid] ?? null;

        if (!$certificate) {
            throw new RuntimeException('No se encontro certificado para validar el token de Firebase.');
        }

        $signature = $this->base64UrlDecode($encodedSignature);
        $signedContent = $encodedHeader.'.'.$encodedPayload;
        $verified = openssl_verify($signedContent, $signature, $certificate, OPENSSL_ALGO_SHA256);

        if ($verified !== 1) {
            throw new RuntimeException('Token de Firebase invalido.');
        }

        return $payload;
    }

    private function splitToken(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new RuntimeException('Formato de token de Firebase invalido.');
        }

        return $parts;
    }

    private function decodeJson(string $encoded): array
    {
        $decoded = $this->base64UrlDecode($encoded);
        $json = json_decode($decoded, true);

        if (!is_array($json)) {
            throw new RuntimeException('No se pudo decodificar el token de Firebase.');
        }

        return $json;
    }

    private function validatePayload(array $payload, string $projectId): void
    {
        $now = time();
        $issuer = 'https://securetoken.google.com/'.$projectId;

        if (($payload['aud'] ?? null) !== $projectId) {
            throw new RuntimeException('Audiencia de Firebase invalida.');
        }

        if (($payload['iss'] ?? null) !== $issuer) {
            throw new RuntimeException('Emisor de Firebase invalido.');
        }

        if (($payload['exp'] ?? 0) < $now) {
            throw new RuntimeException('Token de Firebase expirado.');
        }

        if (($payload['iat'] ?? $now) > $now) {
            throw new RuntimeException('Fecha de emision de Firebase invalida.');
        }
    }

    private function base64UrlDecode(string $value): string
    {
        $remainder = strlen($value) % 4;

        if ($remainder) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($value, '-_', '+/')) ?: '';
    }
}

<?php

namespace App\Services\Auth;

use App\Models\UsuarioModel;
use Illuminate\Support\Facades\Cache;

class InternalTokenService
{
    public function generate(UsuarioModel $usuario): string
    {
        $payload = [
            'sub' => $usuario->id,
            'rol' => $usuario->rol?->nombre,
            'iat' => time(),
            'exp' => time() + (60 * 60 * 8),
        ];

        $encodedPayload = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = hash_hmac('sha256', $encodedPayload, (string) config('app.key'), true);

        return $encodedPayload.'.'.$this->base64UrlEncode($signature);
    }

    public function userIdFromToken(?string $token): ?int
    {
        if (!$token || $this->isRevoked($token) || !str_contains($token, '.')) {
            return null;
        }

        $payload = $this->payloadFromToken($token);

        return isset($payload['sub']) ? (int) $payload['sub'] : null;
    }

    public function revoke(?string $token): void
    {
        $payload = $this->payloadFromToken($token);
        $expiresAt = (int) ($payload['exp'] ?? time());
        $seconds = max(1, $expiresAt - time());

        Cache::put($this->revokedKey((string) $token), true, $seconds);
    }

    public function isRevoked(?string $token): bool
    {
        if (!$token) {
            return false;
        }

        return Cache::has($this->revokedKey($token));
    }

    public function payloadFromToken(?string $token): array
    {
        if (!$token || !str_contains($token, '.')) {
            return [];
        }

        [$encodedPayload, $encodedSignature] = explode('.', $token, 2);
        $expected = $this->base64UrlEncode(hash_hmac('sha256', $encodedPayload, (string) config('app.key'), true));

        if (!hash_equals($expected, $encodedSignature)) {
            return [];
        }

        $payload = json_decode($this->base64UrlDecode($encodedPayload), true);

        if (!is_array($payload) || ($payload['exp'] ?? 0) < time()) {
            return [];
        }

        return $payload;
    }

    private function revokedKey(string $token): string
    {
        return 'auth:revoked-token:'.hash('sha256', $token);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        return base64_decode(strtr($value, '-_', '+/')) ?: '';
    }
}

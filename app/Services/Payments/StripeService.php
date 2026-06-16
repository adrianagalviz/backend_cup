<?php

namespace App\Services\Payments;

use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeService
{
    public function createCheckoutSession(array $data): Session
    {
        $stripe = $this->client();

        return $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower($data['moneda']),
                    'unit_amount' => $this->amountInMinorUnits((float) $data['monto']),
                    'product_data' => [
                        'name' => 'Pago de admision CUP FICCT',
                        'description' => 'Pago obligatorio para postulante '.$data['postulante_id'],
                    ],
                ],
            ]],
            'metadata' => [
                'postulante_id' => (string) $data['postulante_id'],
            ],
            'success_url' => $this->urlWithApplicantId($data['success_url'] ?? config('stripe.success_url'), (int) $data['postulante_id']),
            'cancel_url' => $this->urlWithApplicantId($data['cancel_url'] ?? config('stripe.cancel_url'), (int) $data['postulante_id']),
        ]);
    }

    public function constructWebhookEvent(string $payload, ?string $signature): object
    {
        $webhookSecret = config('stripe.webhook_secret');

        if (! $webhookSecret) {
            throw new RuntimeException('El webhook secret de Stripe no esta configurado.');
        }

        if (! $signature) {
            throw new RuntimeException('Firma de Stripe no enviada.');
        }

        try {
            return Webhook::constructEvent($payload, $signature, $webhookSecret);
        } catch (UnexpectedValueException|SignatureVerificationException) {
            throw new RuntimeException('Firma o payload de Stripe invalido.');
        }
    }

    private function client(): StripeClient
    {
        $secretKey = config('stripe.secret_key');

        if (! $secretKey) {
            throw new RuntimeException('La clave secreta de Stripe no esta configurada.');
        }

        return new StripeClient($secretKey);
    }

    private function amountInMinorUnits(float $amount): int
    {
        return (int) round($amount * 100);
    }

    private function urlWithApplicantId(?string $url, int $postulanteId): string
    {
        $baseUrl = $url ?: config('stripe.success_url');
        $separator = str_contains($baseUrl, '?') ? '&' : '?';

        return $baseUrl.$separator.'postulante_id='.$postulanteId;
    }
}

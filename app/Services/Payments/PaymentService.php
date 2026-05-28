<?php

namespace App\Services\Payments;

use App\Models\AlumnoModel;
use App\Models\PagoStripeModel;
use App\Models\PostulanteModel;
use App\Models\UsuarioModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentService
{
    public function __construct(
        private readonly StripeService $stripe,
    ) {
    }

    public function createCheckoutSession(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $postulante = PostulanteModel::query()->findOrFail($data['postulante_id']);

            if ($postulante->estado_requisitos !== 'aprobado') {
                throw new RuntimeException('El postulante debe tener requisitos aprobados antes de iniciar el pago.');
            }

            if (AlumnoModel::query()->where('postulante_id', $postulante->id)->exists()) {
                throw new RuntimeException('El postulante ya fue convertido en alumno.');
            }

            $existingPayment = PagoStripeModel::query()
                ->where('postulante_id', $postulante->id)
                ->first();

            if ($existingPayment?->estado_pago === 'pagado') {
                throw new RuntimeException('El postulante ya tiene un pago confirmado por Stripe.');
            }

            $session = $this->stripe->createCheckoutSession([
                'postulante_id' => $postulante->id,
                'monto' => $data['monto'],
                'moneda' => $data['moneda'],
                'success_url' => $data['success_url'] ?? null,
                'cancel_url' => $data['cancel_url'] ?? null,
            ]);

            $paymentData = [
                'postulante_id' => $postulante->id,
                'stripe_payment_intent_id' => is_string($session->payment_intent) ? $session->payment_intent : null,
                'stripe_checkout_session_id' => $session->id,
                'monto' => $data['monto'],
                'moneda' => $data['moneda'],
                'estado_pago' => 'pendiente',
                'respuesta_stripe' => json_encode($session->toArray()),
            ];

            if ($existingPayment) {
                DB::table('pago_stripe')->where('id', $existingPayment->id)->update($paymentData);
                $paymentId = $existingPayment->id;
            } else {
                $paymentData['creado_en'] = now();
                $paymentId = DB::table('pago_stripe')->insertGetId($paymentData);
            }

            DB::table('postulante')
                ->where('id', $postulante->id)
                ->update([
                    'estado_pago' => 'pendiente',
                    'estado_postulante' => 'pendiente_pago',
                    'actualizado_en' => now(),
                ]);

            return [
                'pago' => $this->findPayment($paymentId),
                'checkout_url' => $session->url,
            ];
        });
    }

    public function handleWebhook(string $payload, ?string $signature): array
    {
        $event = $this->stripe->constructWebhookEvent($payload, $signature);
        $type = $event->type ?? null;
        $object = $event->data->object ?? null;

        if (! $object || ! isset($object->id)) {
            throw new RuntimeException('Evento de Stripe sin objeto procesable.');
        }

        $payment = PagoStripeModel::query()
            ->where('stripe_checkout_session_id', $object->id)
            ->orWhere('stripe_payment_intent_id', $object->id)
            ->first();

        if (! $payment) {
            return [
                'procesado' => false,
                'mensaje' => 'No existe pago local asociado al evento Stripe.',
            ];
        }

        if ($payment->estado_pago === 'pagado' && $type === 'checkout.session.completed') {
            return [
                'procesado' => false,
                'mensaje' => 'Evento ya procesado previamente.',
                'pago' => $this->formatPayment($payment),
            ];
        }

        $newStatus = match ($type) {
            'checkout.session.completed' => 'pagado',
            'checkout.session.expired' => 'fallido',
            'payment_intent.payment_failed' => 'fallido',
            default => null,
        };

        if (! $newStatus) {
            return [
                'procesado' => false,
                'mensaje' => 'Evento Stripe recibido sin cambios locales requeridos.',
                'pago' => $this->formatPayment($payment),
            ];
        }

        DB::table('pago_stripe')
            ->where('id', $payment->id)
            ->update([
                'stripe_payment_intent_id' => is_string($object->payment_intent ?? null) ? $object->payment_intent : $payment->stripe_payment_intent_id,
                'estado_pago' => $newStatus,
                'fecha_pago' => $newStatus === 'pagado' ? now() : null,
                'respuesta_stripe' => json_encode($event->toArray()),
            ]);

        DB::table('postulante')
            ->where('id', $payment->postulante_id)
            ->update([
                'estado_pago' => $newStatus === 'pagado' ? 'pagado' : 'rechazado',
                'estado_postulante' => $newStatus === 'pagado' ? 'pagado' : 'rechazado',
                'actualizado_en' => now(),
            ]);

        $updatedPayment = $this->findPayment($payment->id);

        return [
            'procesado' => true,
            'mensaje' => 'Evento Stripe procesado correctamente.',
            'pago' => $this->formatPayment($updatedPayment),
        ];
    }

    public function listByApplicant(int $postulanteId): Collection
    {
        PostulanteModel::query()->findOrFail($postulanteId);

        return PagoStripeModel::query()
            ->with('validador.persona')
            ->where('postulante_id', $postulanteId)
            ->orderByDesc('id')
            ->get();
    }

    public function validateByAdministrator(int $paymentId, UsuarioModel $administrator): PagoStripeModel
    {
        return DB::transaction(function () use ($paymentId, $administrator): PagoStripeModel {
            $payment = $this->findPayment($paymentId);

            if ($payment->estado_pago !== 'pagado') {
                throw new RuntimeException('Stripe aun no confirmo este pago.');
            }

            DB::table('pago_stripe')
                ->where('id', $payment->id)
                ->update([
                    'validado_por_usuario_id' => $administrator->id,
                    'validado_en' => now(),
                ]);

            DB::table('postulante')
                ->where('id', $payment->postulante_id)
                ->update([
                    'estado_pago' => 'pagado',
                    'estado_postulante' => 'pagado',
                    'actualizado_en' => now(),
                ]);

            return $this->findPayment($payment->id);
        });
    }

    public function findPayment(int $id): PagoStripeModel
    {
        return PagoStripeModel::query()
            ->with(['postulante.persona', 'validador.persona'])
            ->findOrFail($id);
    }

    public function formatPayment(PagoStripeModel $payment): array
    {
        return [
            'id' => $payment->id,
            'postulante_id' => $payment->postulante_id,
            'stripe_payment_intent_id' => $payment->stripe_payment_intent_id,
            'stripe_checkout_session_id' => $payment->stripe_checkout_session_id,
            'monto' => $payment->monto,
            'moneda' => $payment->moneda,
            'estado_pago' => $payment->estado_pago,
            'fecha_pago' => $payment->fecha_pago,
            'validado_admin' => $payment->validado_por_usuario_id !== null && $payment->validado_en !== null,
            'validado_por_usuario_id' => $payment->validado_por_usuario_id,
            'validado_en' => $payment->validado_en,
            'creado_en' => $payment->creado_en,
        ];
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Models\PagoStripeModel;
use App\Services\Payments\PaymentService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $payments,
    ) {
    }

    public function createStripeSession(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'postulante_id' => ['required', 'integer', 'exists:postulante,id'],
            'monto' => ['nullable', 'numeric', 'min:1'],
            'moneda' => ['nullable', 'string', 'max:10'],
            'success_url' => ['nullable', 'url'],
            'cancel_url' => ['nullable', 'url'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $data = $validator->validated();
        $data['monto'] = (float) config('stripe.payment_amount', 250.00);
        $data['moneda'] = strtoupper($data['moneda'] ?? config('stripe.currency', 'BOB'));

        try {
            $result = $this->payments->createCheckoutSession($data);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Postulante no encontrado.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Sesion de pago Stripe creada correctamente.', [
            'pago' => $this->payments->formatPayment($result['pago']),
            'checkout_url' => $result['checkout_url'],
        ], 201);
    }

    public function stripeWebhook(Request $request): JsonResponse
    {
        try {
            $result = $this->payments->handleWebhook(
                $request->getContent(),
                $request->header('Stripe-Signature')
            );
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 400);
        }

        return ApiResponse::success($result['mensaje'], [
            'procesado' => $result['procesado'],
            'pago' => $result['pago'] ?? null,
        ]);
    }

    public function byApplicant(int $id): JsonResponse
    {
        try {
            $payments = $this->payments->listByApplicant($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Postulante no encontrado.', [], 404);
        }

        $formattedPayments = $payments
            ->map(fn (PagoStripeModel $payment) => $this->payments->formatPayment($payment))
            ->values();

        return ApiResponse::success('Pagos del postulante obtenidos correctamente.', [
            'pagos' => $formattedPayments,
            'existe_pago_pagado' => $payments->contains(fn (PagoStripeModel $payment) => $payment->estado_pago === 'pagado'),
            'existe_pago_validado_admin' => $payments->contains(fn (PagoStripeModel $payment) => $payment->validado_por_usuario_id !== null && $payment->validado_en !== null),
        ]);
    }

    public function publicStatusByApplicant(int $id): JsonResponse
    {
        try {
            $status = $this->payments->publicStatusByApplicant($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Postulante no encontrado.', [], 404);
        }

        return ApiResponse::success('Estado de pago del postulante obtenido correctamente.', [
            'estado_pago_postulante' => $status,
        ]);
    }

    public function temporaryAutomaticPayment(int $id): JsonResponse
    {
        try {
            $payment = $this->payments->registerTemporaryAutomaticPayment($id);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Postulante no encontrado.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Pago temporal registrado automaticamente correctamente.', [
            'pago' => $this->payments->formatPayment($payment),
            'estado_pago_postulante' => $this->payments->publicStatusByApplicant($id),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'estado_pago' => ['nullable', Rule::in(['pendiente', 'pagado', 'fallido'])],
            'buscar' => ['nullable', 'string', 'max:150'],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ], $this->messages());

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $payments = $this->payments->listPayments($validator->validated());

        return response()->json([
            'ok' => true,
            'mensaje' => 'Pagos obtenidos correctamente.',
            'datos' => collect($payments->items())
                ->map(fn (PagoStripeModel $payment) => $this->payments->formatPayment($payment))
                ->values(),
            'meta' => [
                'pagina_actual' => $payments->currentPage(),
                'por_pagina' => $payments->perPage(),
                'total' => $payments->total(),
                'ultima_pagina' => $payments->lastPage(),
            ],
        ]);
    }

    public function validateAdmin(Request $request, int $id): JsonResponse
    {
        $administrator = $request->attributes->get('usuario_autenticado');

        try {
            $payment = $this->payments->validateByAdministrator($id, $administrator);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('Pago no encontrado.', [], 404);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        return ApiResponse::success('Pago validado administrativamente correctamente.', [
            'pago' => $this->payments->formatPayment($payment),
        ]);
    }

    private function messages(): array
    {
        return [
            'postulante_id.required' => 'El postulante es obligatorio.',
            'postulante_id.exists' => 'El postulante indicado no existe.',
            'monto.required' => 'El monto del pago es obligatorio.',
            'monto.numeric' => 'El monto del pago debe ser numerico.',
            'monto.min' => 'El monto del pago debe ser mayor a cero.',
            'success_url.url' => 'La URL de exito debe ser valida.',
            'cancel_url.url' => 'La URL de cancelacion debe ser valida.',
        ];
    }
}

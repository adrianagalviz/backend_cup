<?php

return [
    'secret_key' => env('STRIPE_SECRET_KEY'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    'currency' => env('STRIPE_CURRENCY', 'BOB'),
    'payment_amount' => (float) env('STRIPE_PAYMENT_AMOUNT', 250.00),
    'success_url' => env('STRIPE_SUCCESS_URL', rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/').'/pagos/exitoso'),
    'cancel_url' => env('STRIPE_CANCEL_URL', rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/').'/pagos/cancelado'),
];

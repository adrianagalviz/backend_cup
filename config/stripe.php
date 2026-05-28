<?php

return [
    'secret_key' => env('STRIPE_SECRET_KEY'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    'currency' => env('STRIPE_CURRENCY', 'BOB'),
    'success_url' => env('STRIPE_SUCCESS_URL', rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/').'/pagos/exito'),
    'cancel_url' => env('STRIPE_CANCEL_URL', rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/').'/pagos/cancelado'),
];

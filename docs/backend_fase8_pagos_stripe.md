# Fase 8 - Pagos con Stripe

## Estado

Fase 8 implementada con Laravel, `stripe/stripe-php`, API REST versionada en `/api/v1` y persistencia en PostgreSQL 16 usando la tabla real `pago_stripe`.

## Subfase 8.1 - Configuracion de Stripe

Dependencia instalada:

```bash
composer require stripe/stripe-php
```

Archivos creados:

- `config/stripe.php`
- `app/Services/Payments/StripeService.php`

Variables usadas desde `.env`:

- `STRIPE_SECRET_KEY`
- `STRIPE_WEBHOOK_SECRET`
- `STRIPE_CURRENCY`
- `STRIPE_SUCCESS_URL`
- `STRIPE_CANCEL_URL`

## Subfase 8.2 - Creacion de sesion de pago

Endpoint implementado:

```text
POST /api/v1/pagos/stripe/crear-sesion
```

Datos esperados:

- `postulante_id`
- `monto`
- `moneda` opcional, por defecto `BOB`
- `success_url` opcional
- `cancel_url` opcional

Validaciones implementadas:

- El postulante debe existir.
- Los requisitos del postulante deben estar aprobados.
- El postulante no debe estar convertido en alumno.
- No permite crear otra sesion si ya existe pago confirmado.
- Crea sesion Stripe Checkout.
- Guarda `stripe_checkout_session_id`.
- Guarda `stripe_payment_intent_id` si Stripe lo devuelve.
- Guarda registro en `pago_stripe` con estado `pendiente`.
- Actualiza el postulante a `estado_postulante = pendiente_pago`.
- Devuelve URL de pago al frontend.

## Subfase 8.3 - Webhook de Stripe

Endpoint implementado:

```text
POST /api/v1/pagos/stripe/webhook
```

Validaciones y comportamiento:

- Recibe payload original.
- Valida firma usando `STRIPE_WEBHOOK_SECRET`.
- Identifica la sesion Stripe en `pago_stripe`.
- Procesa `checkout.session.completed` como `pagado`.
- Procesa `checkout.session.expired` y `payment_intent.payment_failed` como `fallido`.
- Guarda fecha de confirmacion cuando el pago queda `pagado`.
- Guarda respuesta Stripe en `respuesta_stripe`.
- Evita reprocesar pagos ya marcados como `pagado`.

## Subfase 8.4 - Consulta de pagos por postulante

Endpoint implementado:

```text
GET /api/v1/pagos/postulante/{id}
```

Proteccion:

- Requiere token interno.
- Requiere rol `administrador`.

Devuelve:

- Pagos registrados.
- Estado de cada pago.
- Si existe pago pagado.
- Si existe pago validado por administrador.

## Subfase 8.5 - Validacion administrativa del pago

Endpoint implementado:

```text
PATCH /api/v1/pagos/{id}/validar-admin
```

Proteccion:

- Requiere token interno.
- Requiere rol `administrador`.

Validaciones y comportamiento:

- El pago debe existir.
- Stripe debe haber confirmado el pago con `estado_pago = pagado`.
- Guarda `validado_por_usuario_id`.
- Guarda `validado_en`.
- Actualiza el postulante con `estado_pago = pagado` y `estado_postulante = pagado`.

## Aclaraciones de esquema

`backend_subfases.md` menciona tabla `pago`, pero `base_de_datos.md` y la migracion implementada definen la tabla `pago_stripe`. Por eso la Fase 8 usa `pago_stripe`.

El documento menciona marcar pago como `validado_admin`, pero el esquema no tiene ese valor en `estado_pago`. La validacion administrativa se registra correctamente con:

- `validado_por_usuario_id`
- `validado_en`

No se agregaron tablas ni estados fuera del esquema definido.

## Archivos creados o modificados

- `composer.json`
- `composer.lock`
- `config/stripe.php`
- `.env`
- `.env.example`
- `app/Models/PagoStripeModel.php`
- `app/Services/Payments/StripeService.php`
- `app/Services/Payments/PaymentService.php`
- `app/Http/Controllers/Api/PaymentController.php`
- `routes/api.php`
- `tests/Feature/ExampleTest.php`
- `docs/backend_fase8_pagos_stripe.md`

## Verificacion

Comandos:

```bash
php artisan route:list --path=api/v1/pagos
php artisan test
```

Prueba manual de validacion:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/pagos/stripe/crear-sesion \
  -H "Content-Type: application/json" \
  -d "{}"
```

Debe responder `422` porque faltan los datos obligatorios.

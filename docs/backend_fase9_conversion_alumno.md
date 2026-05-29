# Fase 9 - Conversion de postulante a alumno y generacion de codigo

## Estado

Fase 9 implementada con Laravel, API REST versionada en `/api/v1` y persistencia en PostgreSQL 16.

## Subfase 9.1 - Validaciones previas

Endpoint protegido:

```text
POST /api/v1/postulantes/{id}/convertir-alumno
```

Proteccion:

- Requiere token interno.
- Requiere rol `administrador`.

Validaciones implementadas:

- El solicitante debe ser administrador por middleware `role:administrador`.
- El postulante debe existir.
- `postulante.estado_requisitos` debe ser `aprobado`.
- Debe existir pago en `pago_stripe`.
- `pago_stripe.estado_pago` debe ser `pagado`.
- El pago debe tener `validado_por_usuario_id`.
- El pago debe tener `validado_en`.
- El postulante no debe existir previamente en `alumno`.
- La persona del postulante no debe tener ya un usuario asociado.

## Subfase 9.2 - Generacion automatica del codigo

Formato implementado:

```text
ANIO + GESTION + CEDULA
```

Ejemplo:

```text
Anio: 2026
Gestion: 1
CI: 13541539
Codigo: 2026113541539
```

Reglas implementadas:

- Se obtiene el anio desde `gestion_academica.anio` asociada al postulante.
- Se obtiene la gestion desde `gestion_academica.numero_gestion`.
- `numero_gestion` debe ser `1` o `2`.
- Se obtiene la cedula desde `persona.cedula_identidad`.
- Se eliminan caracteres no numericos de la cedula antes de concatenar.
- Se valida que el codigo no exista en `alumno.codigo_alumno`.
- Se valida que el codigo no exista en `usuario.codigo_acceso`.

## Subfase 9.3 - Creacion de alumno y usuario

Endpoint implementado:

```text
POST /api/v1/postulantes/{id}/convertir-alumno
```

Comportamiento:

- Crea usuario con rol `alumno`.
- Asocia el usuario con la misma `persona` del postulante.
- Guarda `codigo_acceso` con el codigo generado.
- Crea registro en `alumno`.
- Asocia alumno con `persona`.
- Asocia alumno con `usuario`.
- Asocia alumno con `postulante`.
- Asocia alumno con `gestion_academica`.
- Guarda `codigo_alumno`.
- Guarda fecha de conversion en `alumno.creado_en`.
- Actualiza `postulante.estado_postulante` a `habilitado_alumno`.
- Actualiza `postulante.actualizado_en`.

## Aclaracion de esquema

`backend_subfases.md` indica actualizar el postulante a `convertido_alumno`, pero `base_de_datos.md` y la migracion solo permiten:

- `registrado`
- `pendiente_pago`
- `pagado`
- `habilitado_alumno`
- `rechazado`

Por eso se usa `habilitado_alumno`, que representa correctamente que el postulante ya recibio acceso como alumno y respeta la restriccion de PostgreSQL.

## Archivos creados o modificados

- `app/Models/AlumnoModel.php`
- `app/Services/Students/ApplicantConversionService.php`
- `app/Http/Controllers/Api/ApplicantConversionController.php`
- `routes/api.php`
- `tests/Feature/ExampleTest.php`
- `docs/backend_fase9_conversion_alumno.md`

## Verificacion

Comandos:

```bash
php artisan route:list --path=api/v1/postulantes
php artisan test
```

Prueba manual:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/postulantes/1/convertir-alumno
```

Debe responder `401` si no se envia token de administrador.

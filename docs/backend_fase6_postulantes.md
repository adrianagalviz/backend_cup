# Fase 6 - Modulo de postulantes

## Estado

Fase 6 implementada con Laravel, API REST versionada en `/api/v1` y PostgreSQL 16.

## Subfase 6.1 - Registro de postulante

Endpoint implementado:

```text
POST /api/v1/postulantes
```

Datos obligatorios:

- `cedula_identidad`
- `nombres`
- `apellido_paterno`
- `apellido_materno`
- `fecha_nacimiento`
- `sexo`
- `direccion`
- `telefono`
- `correo`
- `colegio_procedencia`
- `ciudad`
- `primera_carrera_id`
- `segunda_carrera_id`

Dato opcional:

- `gestion_academica_id`

Si no se envia `gestion_academica_id`, Laravel usa la gestion academica activa mas reciente. Si no existe una gestion activa, responde error claro sin crear registros incompletos.

Validaciones implementadas:

- Campos obligatorios.
- CI unico en `persona`.
- Correo valido y unico en `persona`.
- Primera opcion de carrera obligatoria y existente.
- Segunda opcion de carrera obligatoria y existente.
- Primera y segunda opcion deben ser diferentes.
- Creacion transaccional de `persona`, `postulante` y `postulacion`.
- Estado inicial:
  - `estado_requisitos`: `pendiente`
  - `estado_pago`: `pendiente`
  - `estado_postulante`: `registrado`

## Subfase 6.2 - Listado de postulantes

Endpoint implementado:

```text
GET /api/v1/postulantes
```

Proteccion:

- Requiere token interno.
- Requiere rol `administrador`.

Filtros disponibles:

- `gestion_academica_id`
- `estado`
- `ci`
- `nombre`
- `buscar`
- `por_pagina`

La respuesta incluye paginacion con `pagina_actual`, `por_pagina`, `total` y `ultima_pagina`.

## Subfase 6.3 - Consulta individual de postulante

Endpoint implementado:

```text
GET /api/v1/postulantes/{id}
```

Proteccion:

- Requiere token interno.
- Requiere rol `administrador`.

Incluye:

- Datos personales.
- Requisitos representados por `estado_requisitos` y `observacion`, segun el esquema actual.
- Documentos de `documento_postulante`.
- Pago de `pago_stripe`.
- Postulacion con primera opcion, segunda opcion y carrera asignada si existe.

## Subfase 6.4 - Edicion de postulante

Endpoint implementado:

```text
PUT /api/v1/postulantes/{id}
```

Proteccion:

- Requiere token interno.
- Requiere rol `administrador`.

Permite actualizar:

- Datos personales.
- Colegio de procedencia.
- Gestion academica.
- Estados definidos por el esquema.
- Observacion.
- Primera y segunda opcion de carrera.

Validaciones implementadas:

- El postulante debe existir.
- CI sin duplicar.
- Correo valido y sin duplicar.
- Carreras existentes.
- Primera y segunda opcion diferentes, incluso si solo se modifica una de ellas.

## Subfase 6.5 - Eliminacion logica de postulante

Endpoint implementado:

```text
DELETE /api/v1/postulantes/{id}
```

Proteccion:

- Requiere token interno.
- Requiere rol `administrador`.

Comportamiento:

- No elimina fisicamente la informacion.
- Cambia `estado_postulante` a `rechazado`, porque es el estado compatible con la estructura definida.
- Actualiza `actualizado_en`.
- Registra `observacion` enviada o el texto por defecto `Eliminacion logica de postulante.`

## Archivos creados o modificados

- `app/Http/Controllers/Api/ApplicantController.php`
- `app/Services/Applicants/ApplicantService.php`
- `routes/api.php`
- `tests/Feature/ExampleTest.php`
- `docs/backend_fase6_postulantes.md`

## Verificacion

Comandos recomendados:

```bash
php artisan route:list --path=api/v1/postulantes
php artisan test
```

Pruebas manuales recomendadas:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/postulantes \
  -H "Content-Type: application/json" \
  -d "{}"
```

Debe responder `422` porque faltan los campos obligatorios.

```bash
curl http://127.0.0.1:8000/api/v1/postulantes
```

Debe responder `401` si no se envia token de administrador.

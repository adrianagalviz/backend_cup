# Fase 10 - Gestion academica, carreras y cupos

## Estado

Fase 10 implementada con Laravel, API REST versionada en `/api/v1` y PostgreSQL 16.

## Subfase 10.1 - Gestion academica

Endpoints implementados:

```text
GET  /api/v1/gestiones
POST /api/v1/gestiones
GET  /api/v1/gestiones/actual
```

Proteccion:

- Requiere token interno.
- Requiere rol `administrador`.

Validaciones:

- `anio` obligatorio, numerico, entre 2000 y 2100.
- `numero_gestion` obligatorio y solo permite `1` o `2`.
- `nombre` obligatorio.
- `fecha_fin` debe ser mayor o igual a `fecha_inicio`.
- `activa` permite definir si la gestion esta activa.

La gestion actual se obtiene como la gestion activa mas reciente por `anio` y `numero_gestion`.

## Subfase 10.2 - Carreras

Endpoints implementados:

```text
GET  /api/v1/carreras
POST /api/v1/carreras
PUT  /api/v1/carreras/{id}
```

Proteccion:

- Requiere token interno.
- Requiere rol `administrador`.

Validaciones:

- `nombre` obligatorio.
- `nombre` unico.
- `descripcion` opcional.
- `activa` permite activar o desactivar la carrera.
- Listado con filtro `activa`, busqueda `buscar` y paginacion `por_pagina`.

## Subfase 10.3 - Cupos por carrera y gestion

Endpoints implementados:

```text
GET  /api/v1/cupos
POST /api/v1/cupos
PUT  /api/v1/cupos/{id}
```

Proteccion:

- Requiere token interno.
- Requiere rol `administrador`.

Validaciones:

- `carrera_id` obligatorio y existente.
- `gestion_academica_id` obligatorio y existente.
- `cantidad_cupos` obligatorio, numerico y no negativo.
- No permite repetir la misma combinacion `carrera_id` + `gestion_academica_id`.

El listado devuelve:

- Cupos totales.
- Cupos ocupados.
- Cupos disponibles.

Los cupos ocupados se calculan desde `postulacion.carrera_asignada_id` filtrando por la gestion academica del postulante.

## Subfase 10.4 - Postulacion con dos carreras

Esta subfase queda cubierta por el registro y edicion de postulantes implementados en Fase 6:

- `POST /api/v1/postulantes`
- `PUT /api/v1/postulantes/{id}`

Validaciones ya existentes:

- Primera opcion obligatoria.
- Segunda opcion obligatoria.
- Ambas carreras deben existir.
- Ambas carreras deben ser diferentes.
- Se registra `primera_carrera_id` y `segunda_carrera_id` en `postulacion`.
- La postulacion queda vinculada al `postulante`, y el postulante mantiene la relacion con `gestion_academica`.

No se creo un endpoint nuevo para esta subfase porque el flujo definido registra la postulacion junto con el postulante.

## Archivos creados o modificados

- `app/Models/CupoCarreraModel.php`
- `app/Models/GestionAcademicaModel.php`
- `app/Models/CarreraModel.php`
- `app/Services/Academic/AcademicManagementService.php`
- `app/Http/Controllers/Api/AcademicManagementController.php`
- `routes/api.php`
- `tests/Feature/ExampleTest.php`
- `docs/backend_fase10_gestiones_carreras_cupos.md`

## Verificacion

Comandos:

```bash
php artisan route:list --path=api/v1/gestiones
php artisan route:list --path=api/v1/carreras
php artisan route:list --path=api/v1/cupos
php artisan test
```

Prueba manual:

```bash
curl http://127.0.0.1:8000/api/v1/gestiones
```

Debe responder `401` si no se envia token de administrador.

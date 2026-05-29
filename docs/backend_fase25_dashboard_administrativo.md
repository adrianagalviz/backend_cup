# Backend - Fase 25: Dashboard administrativo

## Objetivo

Crear endpoints de solo lectura para alimentar el dashboard administrativo con indicadores principales del sistema.

Todos los endpoints requieren autenticacion interna y rol `administrador`.

## Endpoints

```text
GET /api/v1/dashboard/resumen
GET /api/v1/dashboard/asistencia
GET /api/v1/dashboard/cupos
GET /api/v1/dashboard/examenes
```

Todos aceptan el filtro opcional:

```text
gestion_academica_id
```

## Subfase 25.1: Resumen general

Endpoint:

```text
GET /api/v1/dashboard/resumen
```

Indicadores implementados:

- `total_inscritos`: total de registros en `postulante`.
- `total_aprobados`: total de registros en `promedio_final` con `estado_final = aprobado`.
- `total_reprobados`: total de registros en `promedio_final` con `estado_final = reprobado`.
- `total_grupos_habilitados`: total de grupos en `grupo` con `activo = true`.

## Subfase 25.2: Indicadores de pagos

Los indicadores de pagos se devuelven dentro del endpoint de resumen, porque la subfase no define un endpoint propio.

Indicadores implementados:

- `total_pagos_pendientes`: total de `pago_stripe` con `estado_pago = pendiente`.
- `total_pagos_validados`: total de `pago_stripe` con `estado_pago = pagado`, `validado_por_usuario_id` y `validado_en`.
- `total_pagos_fallidos`: total de `pago_stripe` con `estado_pago = fallido`.
- `total_postulantes_listos_para_convertirse_en_alumnos`: postulantes con requisitos aprobados, pago Stripe pagado y validado, y sin registro en `alumno`.

## Subfase 25.3: Indicadores de asistencia

Endpoint:

```text
GET /api/v1/dashboard/asistencia
```

Indicadores implementados:

- `total_asistencias_docentes`: `asistencia_docente.estado_entrada = presente`.
- `total_faltas_docentes`: `asistencia_docente.estado_entrada = falta`.
- `total_retrasos_docentes`: `asistencia_docente.estado_entrada = retraso`.
- `total_asistencias_alumnos`: `asistencia_alumno.estado_asistencia = presente`.
- `total_faltas_alumnos`: `asistencia_alumno.estado_asistencia = falta`.
- `total_retrasos_alumnos`: `asistencia_alumno.estado_asistencia = retraso`.

## Subfase 25.4: Indicadores de cupos

Endpoint:

```text
GET /api/v1/dashboard/cupos
```

Indicadores por carrera y gestion:

- `cupos_por_carrera`: valor de `cupo_carrera.cantidad_cupos`.
- `cupos_ocupados`: postulaciones aprobadas con `carrera_asignada_id` para la carrera y gestion correspondiente.
- `cupos_disponibles`: cupos por carrera menos cupos ocupados.

## Subfase 25.5: Indicadores de examenes

Endpoint:

```text
GET /api/v1/dashboard/examenes
```

Indicadores implementados:

- `examenes_creados`: total de registros en `examen`.
- `examenes_habilitados`: total de `examen` con `habilitado = true`.
- `alumnos_que_rindieron`: total distinto de alumnos con registros en `intento_examen`.
- `alumnos_pendientes`: alumnos sin intento registrado para examenes habilitados. Si no hay examenes habilitados, el valor es `0`.

## Archivos creados o modificados

- `app/Services/Dashboard/AdminDashboardService.php`
- `app/Http/Controllers/Api/DashboardController.php`
- `routes/api.php`
- `tests/Feature/ExampleTest.php`

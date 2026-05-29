# Fase 15 - Subfases 15.4 a 15.5

## Estado

Subfases 15.4 y 15.5 implementadas con Laravel, API REST versionada en `/api/v1` y PostgreSQL 16.

## Subfase 15.4 - Falta automatica docente

Endpoint implementado:

```text
POST /api/v1/asistencia-docente/generar-faltas
```

Proteccion:

- Requiere token interno.
- Requiere rol `administrador`.

Dato opcional:

- `fecha`

Comportamiento:

- Revisa horarios vencidos de docentes.
- Busca asignaciones activas en `asignacion_docente`.
- Valida horarios activos en `horario_clase`.
- Si no existe asistencia del docente para ese horario y fecha, crea una asistencia con `estado_entrada = falta`.
- Si existe asistencia pendiente sin entrada, la actualiza a `falta`.
- No modifica asistencias que ya tienen `hora_entrada`.
- Evita duplicar faltas usando la combinacion real unica: docente, horario y fecha.

## Subfase 15.5 - Consulta visual de asistencia docente

Endpoints implementados:

```text
GET /api/v1/asistencia-docente
GET /api/v1/asistencia-docente/docente/{id}
```

Proteccion:

- Requiere token interno.
- Requiere rol `administrador`.

Filtros disponibles:

- `docente_id`
- `fecha`
- `grupo_id`
- `materia_id`
- `estado`
- `por_pagina`

Estados permitidos:

- `pendiente`
- `presente`
- `retraso`
- `falta`

La respuesta incluye datos del docente, horario, materia, grupo, aula, entrada, salida y estado.

## Archivos creados o modificados

- `app/Services/Attendance/TeacherAttendanceService.php`
- `app/Http/Controllers/Api/TeacherAttendanceController.php`
- `routes/api.php`
- `tests/Feature/ExampleTest.php`
- `docs/backend_fase15_4_15_5_asistencia_docente.md`

## Verificacion

Comandos:

```bash
php artisan route:list --path=api/v1/asistencia-docente
php artisan test
```

Prueba manual:

```bash
curl http://127.0.0.1:8000/api/v1/asistencia-docente
```

Debe responder `401` si no se envia token de administrador.

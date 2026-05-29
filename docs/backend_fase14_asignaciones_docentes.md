# Fase 14 - Asignacion de docentes a materias y grupos

## Estado

Fase 14 implementada con Laravel, API REST versionada en `/api/v1` y PostgreSQL 16.

## Subfase 14.1 - Crear asignacion docente-materia-grupo

Endpoint implementado:

```text
POST /api/v1/asignaciones/docente-materia-grupo
```

Proteccion:

- Requiere token interno.
- Requiere rol `administrador`.

Datos requeridos:

- `docente_id`
- `materia_id`
- `grupo_id`
- `gestion_academica_id`
- `horario_clase_id`

El campo `horario_clase_id` se solicita porque la tabla real `asignacion_docente` lo requiere como llave foranea obligatoria.

Validaciones:

- Docente existente, activo y contratado.
- Materia existente y activa.
- Grupo existente y activo.
- Gestion existente.
- Horario existente y activo.
- El grupo debe corresponder a la gestion.
- El horario debe corresponder a la gestion, grupo y materia indicados.

## Subfase 14.2 - Validar maximo 4 grupos por docente

Regla implementada:

- Antes de crear una asignacion se cuentan los grupos distintos activos del docente.
- Se incluye el grupo nuevo en el conteo.
- Si supera 4, se bloquea la asignacion.

## Subfase 14.3 - Validar maximo 4 materias por docente

Regla implementada:

- Antes de crear una asignacion se cuentan las materias distintas activas del docente.
- Se incluye la materia nueva en el conteo.
- Si supera 4, se bloquea la asignacion.

## Subfase 14.4 - Consultas de asignaciones

Endpoints implementados:

```text
GET    /api/v1/asignaciones/docente/{id}
GET    /api/v1/asignaciones/grupo/{id}
GET    /api/v1/asignaciones/materia/{id}
DELETE /api/v1/asignaciones/{id}
```

Comportamiento:

- Lista asignaciones por docente.
- Lista asignaciones por grupo.
- Lista asignaciones por materia.
- Desactiva asignacion con `activo = false`.
- No elimina fisicamente datos ni historial.

## Archivos creados o modificados

- `app/Services/Academic/TeacherAssignmentService.php`
- `app/Http/Controllers/Api/TeacherAssignmentController.php`
- `routes/api.php`
- `tests/Feature/ExampleTest.php`
- `docs/backend_fase14_asignaciones_docentes.md`

## Verificacion

Comandos:

```bash
php artisan route:list --path=api/v1/asignaciones
php artisan test
```

Prueba manual:

```bash
curl http://127.0.0.1:8000/api/v1/asignaciones/docente/1
```

Debe responder `401` si no se envia token de administrador.

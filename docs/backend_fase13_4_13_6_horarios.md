# Fase 13 - Subfases 13.4 a 13.6

## Estado

Subfases 13.4, 13.5 y 13.6 implementadas con Laravel, API REST versionada en `/api/v1` y PostgreSQL 16.

## Subfase 13.4 - Creacion de horario de clase

Endpoints implementados:

```text
POST /api/v1/horarios
GET  /api/v1/horarios
```

Proteccion:

- Requiere token interno.
- Requiere rol `administrador`.

Datos requeridos para crear:

- `gestion_academica_id`
- `grupo_id`
- `materia_id`
- `aula_id`
- `dia_id`
- `turno_id`
- `periodo_id`
- `docente_id`

Comportamiento:

- Valida dia, turno, periodo, aula, grupo, materia y docente.
- Valida que el grupo corresponda a la gestion academica.
- Valida que el periodo corresponda al turno.
- Toma `hora_inicio` y `hora_fin` desde el periodo.
- Crea `horario_clase`.
- Crea en la misma transaccion la relacion `asignacion_docente`, porque el esquema real relaciona docente con horario mediante esa tabla.

## Subfase 13.5 - Validacion de choques de horario

Validaciones implementadas:

- Evita que un docente tenga dos clases en el mismo dia, periodo y gestion.
- Evita que un grupo tenga dos clases en el mismo dia, periodo y gestion.
- Evita que un aula tenga dos clases en el mismo dia, periodo y gestion.
- Solo considera horarios y asignaciones activas.

## Subfase 13.6 - Consulta de horarios por usuario

Endpoints implementados:

```text
GET /api/v1/horarios/docente/{id}
GET /api/v1/horarios/alumno/{id}
```

Permisos:

- Administrador puede consultar horarios generales, de docentes y de alumnos.
- Docente puede consultar solo su propia carga horaria.
- Alumno puede consultar solo sus propios horarios.

Consulta de alumno:

- Obtiene los grupos activos del alumno desde `grupo_alumno`.
- Devuelve los horarios activos de esos grupos en su gestion academica.

## Archivos creados o modificados

- `app/Models/HorarioClaseModel.php`
- `app/Models/AsignacionDocenteModel.php`
- `app/Services/Academic/ClassScheduleService.php`
- `app/Http/Controllers/Api/ClassScheduleController.php`
- `routes/api.php`
- `tests/Feature/ExampleTest.php`
- `docs/backend_fase13_4_13_6_horarios.md`

## Verificacion

Comandos:

```bash
php artisan route:list --path=api/v1/horarios
php artisan test
```

Prueba manual:

```bash
curl http://127.0.0.1:8000/api/v1/horarios
```

Debe responder `401` si no se envia token de administrador.

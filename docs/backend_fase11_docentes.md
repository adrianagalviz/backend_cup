# Fase 11 - Modulo de docentes

## Estado

Fase 11 implementada con Laravel, API REST versionada en `/api/v1` y PostgreSQL 16.

## Subfase 11.1 - Registro de docente

Endpoint implementado:

```text
POST /api/v1/docentes
```

Proteccion:

- Requiere token interno.
- Requiere rol `administrador`.

Datos obligatorios:

- `nombres`
- `apellido_paterno`
- `apellido_materno`
- `cedula_identidad`
- `celular`
- `correo`
- `es_profesional_area`
- `tiene_maestria`
- `tiene_diplomado_educacion_superior`

Datos opcionales:

- `fecha_nacimiento`
- `sexo`
- `direccion`
- `telefono`
- `ciudad`
- `nombre_usuario`
- `password`
- `correo_verificado`

Validaciones:

- Campos obligatorios.
- CI unico en `persona`.
- Correo valido y unico en `persona`.
- El docente debe ser profesional en el area.
- El docente debe tener maestria.
- El docente debe tener diplomado en educacion superior.
- Crea `persona`.
- Crea `usuario` con rol `docente`.
- Crea `docente`.
- Si no se envia `nombre_usuario`, se genera `docente_{cedula_identidad}`.
- `password` es opcional porque el esquema permite `password_hash` nulo.

## Subfase 11.2 - Listado, busqueda y consulta de docentes

Endpoints implementados:

```text
GET /api/v1/docentes
GET /api/v1/docentes/{id}
GET /api/v1/docentes/buscar?ci=...
```

Filtros disponibles:

- `ci`
- `nombre`
- `buscar`
- `activo`
- `por_pagina`

El detalle incluye:

- Datos personales.
- Datos del usuario.
- Estado del docente.
- Requisitos de contratacion.
- Materias asignadas.
- Grupos asignados.
- Horarios asignados.

Las asignaciones se consultan desde `asignacion_docente` junto con `materia`, `grupo`, `horario_clase`, `aula`, `dia`, `turno`, `periodo` y `gestion_academica`.

## Subfase 11.3 - Edicion y desactivacion de docentes

Endpoints implementados:

```text
PUT    /api/v1/docentes/{id}
DELETE /api/v1/docentes/{id}
```

Validaciones de edicion:

- El docente debe existir.
- CI no debe duplicarse.
- Correo no debe duplicarse.
- Nombre de usuario no debe duplicarse.
- Permite actualizar datos personales.
- Permite actualizar datos del docente.
- Permite actualizar usuario docente.

Desactivacion:

- No elimina fisicamente el docente.
- Marca `docente.activo = false`.
- Marca `usuario.activo = false`.
- No borra asistencias.
- No borra asignaciones docente-materia-grupo-horario.

## Archivos creados o modificados

- `app/Models/DocenteModel.php`
- `app/Models/AsignacionDocenteModel.php`
- `app/Services/Teachers/TeacherManagementService.php`
- `app/Http/Controllers/Api/TeacherController.php`
- `routes/api.php`
- `tests/Feature/ExampleTest.php`
- `docs/backend_fase11_docentes.md`

## Verificacion

Comandos:

```bash
php artisan route:list --path=api/v1/docentes
php artisan test
```

Prueba manual:

```bash
curl http://127.0.0.1:8000/api/v1/docentes
```

Debe responder `401` si no se envia token de administrador.

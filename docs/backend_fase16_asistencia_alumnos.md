# Fase 16: Asistencia de alumnos

## Alcance implementado

Se implementaron las subfases 16.1 a 16.5 usando Laravel, API REST y la tabla `asistencia_alumno` definida en `base_de_datos.md`.

Las rutas reales quedan versionadas bajo `/api/v1`, manteniendo el alcance solicitado en `backend_subfases.md`.

## Subfase 16.1: Validar horario del alumno

### Endpoint de apoyo

```text
GET /api/v1/asistencia-alumno/horario-activo
```

### Implementacion

1. Obtiene el usuario autenticado desde el middleware `auth.internal`.
2. Valida que el usuario tenga rol `alumno`.
3. Obtiene el alumno asociado al usuario.
4. Consulta los grupos activos del alumno desde `grupo_alumno`.
5. Busca el horario activo del dia actual, validando dia, periodo, materia y grupo.
6. Aplica la ventana permitida desde 30 minutos antes del inicio hasta la hora fin del horario.
7. Devuelve si el alumno puede marcar asistencia y, si existe, la asistencia ya registrada.

### Archivos relacionados

- `app/Services/Attendance/StudentAttendanceService.php`
- `app/Http/Controllers/Api/StudentAttendanceController.php`
- `routes/api.php`

### Verificacion

```bash
php artisan route:list --path=api/v1/asistencia-alumno
```

Con token de alumno valido:

```bash
curl -H "Authorization: Bearer TOKEN" http://localhost:8000/api/v1/asistencia-alumno/horario-activo
```

## Subfase 16.2: Alumno marca asistencia

### Endpoint

```text
POST /api/v1/asistencia-alumno/marcar
```

### Implementacion

1. Valida alumno autenticado.
2. Valida horario activo para el alumno.
3. Valida margen de 30 minutos.
4. Registra `presente` cuando corresponde.
5. Registra `retraso` si pasaron mas de 30 minutos desde el inicio de la clase.
6. Evita doble asistencia del alumno para el mismo horario y fecha.

### Verificacion

```bash
curl -X POST \
  -H "Authorization: Bearer TOKEN_ALUMNO" \
  http://localhost:8000/api/v1/asistencia-alumno/marcar
```

## Subfase 16.3: Docente toma asistencia a sus alumnos

### Endpoint

```text
POST /api/v1/asistencia-alumno/docente/registrar
```

### Implementacion

1. Valida docente autenticado.
2. Valida que el docente este activo.
3. Valida que el docente tenga asignado el grupo.
4. Valida que el docente tenga asignada la materia.
5. Valida que el horario este activo.
6. Recibe una lista de alumnos.
7. Valida que cada alumno pertenezca al grupo del horario.
8. Registra `presente`, `retraso` o `falta`.
9. Guarda el docente y el usuario que registro la asistencia.

### Cuerpo esperado

```json
{
  "horario_clase_id": 1,
  "asistencias": [
    {
      "alumno_id": 1,
      "estado_asistencia": "presente",
      "observacion": "Registro en aula"
    }
  ]
}
```

### Verificacion

```bash
curl -X POST \
  -H "Authorization: Bearer TOKEN_DOCENTE" \
  -H "Content-Type: application/json" \
  -d "{\"horario_clase_id\":1,\"asistencias\":[{\"alumno_id\":1,\"estado_asistencia\":\"presente\"}]}" \
  http://localhost:8000/api/v1/asistencia-alumno/docente/registrar
```

## Subfase 16.4: Falta automatica de alumnos

### Endpoint administrativo

```text
POST /api/v1/asistencia-alumno/generar-faltas
```

### Implementacion

1. Consulta horarios activos vencidos de la fecha indicada o del dia actual.
2. Obtiene alumnos activos del grupo.
3. Verifica quienes no tienen asistencia registrada.
4. Registra `falta`.
5. Evita duplicados verificando alumno, horario y fecha antes de insertar.

### Cuerpo opcional

```json
{
  "fecha": "2026-05-29"
}
```

### Verificacion

```bash
curl -X POST \
  -H "Authorization: Bearer TOKEN_ADMIN" \
  -H "Content-Type: application/json" \
  -d "{\"fecha\":\"2026-05-29\"}" \
  http://localhost:8000/api/v1/asistencia-alumno/generar-faltas
```

## Subfase 16.5: Consultas por rol

### Endpoints

```text
GET /api/v1/asistencia-alumno/mis-asistencias
GET /api/v1/asistencia-alumno/docente/mis-alumnos
GET /api/v1/asistencia-alumno
```

### Reglas aplicadas

1. Administrador ve toda la asistencia.
2. Docente ve solo asistencias de grupos asignados a el.
3. Alumno ve solo su propia asistencia.

### Filtros disponibles

```text
alumno_id
docente_id
fecha
grupo_id
materia_id
estado
por_pagina
```

### Verificacion

```bash
curl -H "Authorization: Bearer TOKEN_ALUMNO" http://localhost:8000/api/v1/asistencia-alumno/mis-asistencias
curl -H "Authorization: Bearer TOKEN_DOCENTE" http://localhost:8000/api/v1/asistencia-alumno/docente/mis-alumnos
curl -H "Authorization: Bearer TOKEN_ADMIN" http://localhost:8000/api/v1/asistencia-alumno
```

## Validaciones tecnicas

Comandos usados para verificar:

```bash
php -l app/Models/AsistenciaAlumnoModel.php
php -l app/Services/Attendance/StudentAttendanceService.php
php -l app/Http/Controllers/Api/StudentAttendanceController.php
php artisan route:list --path=api/v1/asistencia-alumno
php artisan test
```


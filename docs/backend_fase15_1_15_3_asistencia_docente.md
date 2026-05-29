# Fase 15 - Subfases 15.1 a 15.3

## Estado

Subfases 15.1, 15.2 y 15.3 implementadas con Laravel, API REST versionada en `/api/v1` y PostgreSQL 16.

## Subfase 15.1 - Deteccion de horario activo del docente

Endpoint implementado:

```text
GET /api/v1/asistencia-docente/horario-activo
```

Proteccion:

- Requiere token interno.
- Requiere rol `docente`.

Comportamiento:

- Obtiene el docente autenticado.
- Usa fecha y hora local `APP_TIMEZONE=America/La_Paz`.
- Busca horarios activos del docente para el dia actual.
- Valida el dia por `dia.orden` usando el dia ISO de la semana.
- Considera horario activo o proximo si la hora actual esta entre 30 minutos antes del inicio y la hora fin.
- Devuelve horario, asistencia registrada si existe, fecha y hora actual.

## Subfase 15.2 - Marcar entrada docente

Endpoint implementado:

```text
POST /api/v1/asistencia-docente/marcar-entrada
```

Reglas implementadas:

- Valida docente autenticado.
- Valida horario activo o proximo.
- Evita doble marcado de entrada.
- Registra `hora_entrada`.
- Registra `estado_entrada = presente` si marca hasta 30 minutos despues del inicio.
- Registra `estado_entrada = retraso` si marca despues de 30 minutos, mientras la clase siga activa.
- Guarda `marcado_por_usuario_id`.

## Subfase 15.3 - Marcar salida docente

Endpoint implementado:

```text
POST /api/v1/asistencia-docente/marcar-salida
```

Reglas implementadas:

- Valida docente autenticado.
- Busca asistencia con entrada previa.
- Evita marcar salida sin entrada.
- Evita doble marcado de salida.
- Registra `hora_salida`.
- Registra `estado_salida = finalizado`.
- Guarda `marcado_por_usuario_id`.

## Archivos creados o modificados

- `config/app.php`
- `.env`
- `.env.example`
- `app/Models/AsistenciaDocenteModel.php`
- `app/Services/Attendance/TeacherAttendanceService.php`
- `app/Http/Controllers/Api/TeacherAttendanceController.php`
- `routes/api.php`
- `tests/Feature/ExampleTest.php`
- `docs/backend_fase15_1_15_3_asistencia_docente.md`

## Verificacion

Comandos:

```bash
php artisan route:list --path=api/v1/asistencia-docente
php artisan test
```

Prueba manual:

```bash
curl http://127.0.0.1:8000/api/v1/asistencia-docente/horario-activo
```

Debe responder `401` si no se envia token de docente.

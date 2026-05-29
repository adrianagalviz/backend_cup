# Fase 13 - Subfases 13.1 a 13.3

## Estado

Subfases 13.1, 13.2 y 13.3 implementadas con Laravel, API REST versionada en `/api/v1` y PostgreSQL 16.

## Subfase 13.1 - Dias

Endpoint implementado:

```text
GET /api/v1/dias
```

Proteccion:

- Requiere token interno.
- Requiere rol `administrador`.

Comportamiento:

- Crea el catalogo base si no existe.
- Lista dias disponibles ordenados por `orden`.
- Los dias quedan disponibles para asociarse a horarios en subfases posteriores.

Catalogo base:

- Lunes
- Martes
- Miercoles
- Jueves
- Viernes
- Sabado
- Domingo

## Subfase 13.2 - Turnos

Endpoints implementados:

```text
POST /api/v1/turnos
GET  /api/v1/turnos
```

Validaciones:

- `nombre` obligatorio y unico.
- `hora_inicio` obligatoria en formato `HH:MM`.
- `hora_fin` obligatoria en formato `HH:MM`.
- `hora_inicio` debe ser menor que `hora_fin`.
- `activo` opcional.

## Subfase 13.3 - Periodos de 45 minutos

Endpoints implementados:

```text
POST /api/v1/periodos
GET  /api/v1/periodos
```

Validaciones:

- `turno_id` obligatorio y existente.
- `numero_periodo` obligatorio.
- `numero_periodo` no se repite dentro del mismo turno.
- `hora_inicio` obligatoria en formato `HH:MM`.
- `hora_fin` obligatoria en formato `HH:MM`.
- La diferencia entre hora inicio y hora fin debe ser exactamente 45 minutos.
- El periodo debe estar dentro del rango horario del turno.
- `duracion_minutos` se guarda como `45`.

## Archivos creados o modificados

- `app/Models/DiaModel.php`
- `app/Models/TurnoModel.php`
- `app/Models/PeriodoModel.php`
- `app/Services/Academic/ScheduleCatalogService.php`
- `app/Http/Controllers/Api/ScheduleCatalogController.php`
- `routes/api.php`
- `tests/Feature/ExampleTest.php`
- `docs/backend_fase13_1_13_3_catalogos_horario.md`

## Verificacion

Comandos:

```bash
php artisan route:list --path=api/v1/dias
php artisan route:list --path=api/v1/turnos
php artisan route:list --path=api/v1/periodos
php artisan test
```

Prueba manual:

```bash
curl http://127.0.0.1:8000/api/v1/dias
```

Debe responder `401` si no se envia token de administrador.

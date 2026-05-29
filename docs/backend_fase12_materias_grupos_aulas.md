# Fase 12 - Materias, grupos y aulas

## Estado

Fase 12 implementada con Laravel, API REST versionada en `/api/v1` y PostgreSQL 16.

## Subfase 12.1 - Materias base

Endpoint implementado:

```text
GET /api/v1/materias
```

Proteccion:

- Requiere token interno.
- Requiere rol `administrador`.

Materias obligatorias:

- Fisica
- Matematicas
- Computacion
- Ingles

La base ya tenia insercion de materias mediante `RoleAndSubjectSeeder`. El endpoint tambien verifica que existan las materias base antes de listar.

## Subfase 12.2 - Creacion y gestion de grupos

Endpoints implementados:

```text
POST /api/v1/grupos
GET  /api/v1/grupos
GET  /api/v1/grupos/{id}/alumnos
```

Validaciones:

- El grupo debe asociarse a una `gestion_academica`.
- El nombre es obligatorio.
- No se permite repetir nombre dentro de la misma gestion.
- `cupo_maximo` no puede superar 70.
- El listado devuelve cupos ocupados y disponibles.
- La consulta de alumnos devuelve los alumnos activos asignados al grupo.

## Subfase 12.3 - Calculo automatico de grupos necesarios

Endpoint implementado:

```text
GET /api/v1/grupos/calcular-necesarios?gestion_academica_id=1
```

Formula:

```text
ceil(total_inscritos / 70)
```

El total de inscritos se calcula desde `alumno` filtrando por `gestion_academica_id`.

## Subfase 12.4 - Asignacion de alumnos a grupos

Endpoint implementado:

```text
POST /api/v1/grupos/asignar-alumnos
```

Datos esperados:

- `gestion_academica_id`
- `grupo_id`
- `alumno_ids` opcional

Comportamiento:

- Valida que la gestion exista.
- Valida que el grupo exista.
- Valida que el grupo pertenezca a la gestion enviada.
- Respeta el cupo maximo del grupo.
- Inserta en `grupo_alumno`.
- Evita duplicar alumnos activos en grupos.
- Si no se envia `alumno_ids`, asigna alumnos activos disponibles de la gestion hasta llenar el cupo.

## Subfase 12.5 - Aulas

Endpoints implementados:

```text
GET  /api/v1/aulas
POST /api/v1/aulas
PUT  /api/v1/aulas/{id}
```

Regla aplicada:

```text
La ubicacion siempre sera Modulo 236. Solo cambia el aula.
```

El endpoint recibe:

```json
{
  "aula": "11"
}
```

Y guarda:

```text
Modulo 236, Aula 11
```

Validaciones:

- El aula es obligatoria al crear.
- No se permite repetir la misma aula en el Modulo 236.
- Permite activar/desactivar aula con `activa`.
- Las aulas quedan disponibles para asociarse a horarios en fases posteriores.

## Archivos creados o modificados

- `app/Models/MateriaModel.php`
- `app/Models/GrupoModel.php`
- `app/Models/GrupoAlumnoModel.php`
- `app/Models/AulaModel.php`
- `app/Models/AlumnoModel.php`
- `app/Services/Academic/ClassroomGroupService.php`
- `app/Http/Controllers/Api/ClassroomGroupController.php`
- `routes/api.php`
- `tests/Feature/ExampleTest.php`
- `docs/backend_fase12_materias_grupos_aulas.md`

## Verificacion

Comandos:

```bash
php artisan route:list --path=api/v1/materias
php artisan route:list --path=api/v1/grupos
php artisan route:list --path=api/v1/aulas
php artisan test
```

Prueba manual:

```bash
curl http://127.0.0.1:8000/api/v1/aulas
```

Debe responder `401` si no se envia token de administrador.

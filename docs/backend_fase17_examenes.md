# Backend fase 17 - Examenes

## Alcance implementado

Se implementaron las subfases 17.1 a 17.5 para la gestion administrativa de examenes:

- 17.1 Crear examen parcial asociado a gestion academica.
- 17.2 Asociar materias y porcentajes, con suma obligatoria de 100.
- 17.3 Crear preguntas de seleccion multiple por materia.
- 17.4 Registrar opciones de respuesta con una sola opcion correcta.
- 17.5 Habilitar y deshabilitar examenes.

## Endpoints

Todos los endpoints requieren autenticacion interna y rol `administrador`.

```text
GET /api/v1/examenes
POST /api/v1/examenes
POST /api/v1/examenes/{id}/materias
POST /api/v1/examenes/{id}/preguntas
POST /api/v1/preguntas/{id}/opciones
PATCH /api/v1/examenes/{id}/habilitar
PATCH /api/v1/examenes/{id}/deshabilitar
```

## Reglas principales

1. Cada gestion academica puede tener solo los parciales 1, 2 y 3.
2. No se permite duplicar un parcial dentro de la misma gestion.
3. El examen se crea siempre con `habilitado = false`.
4. Las materias del examen se registran en `examen_materia_porcentaje`.
5. La suma de porcentajes de materias debe ser exactamente 100.
6. No se puede duplicar materia dentro del mismo examen.
7. Una pregunta solo puede crearse sobre una materia ya asociada al examen.
8. Cada pregunta es de tipo `seleccion_multiple`.
9. Cada pregunta debe tener al menos dos opciones.
10. Cada pregunta debe tener exactamente una opcion correcta.
11. Para habilitar un examen debe tener materias, porcentajes validos, preguntas y opciones validas.
12. Cada materia asociada debe tener al menos una pregunta activa.
13. No se puede modificar materias, preguntas ni opciones de un examen ya habilitado.
14. El administrador puede deshabilitar un examen cuando corresponda.

## Tablas usadas

```text
examen
examen_materia_porcentaje
pregunta
opcion_pregunta
gestion_academica
materia
usuario
```

## Ejemplos de entrada

Crear examen:

```json
{
  "gestion_academica_id": 1,
  "numero_parcial": 1,
  "titulo": "Primer parcial CUP",
  "descripcion": "Evaluacion del primer parcial",
  "fecha_inicio": "2026-06-01 08:00:00",
  "fecha_fin": "2026-06-01 10:00:00"
}
```

Asociar materias:

```json
{
  "materias": [
    { "materia_id": 1, "porcentaje": 25 },
    { "materia_id": 2, "porcentaje": 30 },
    { "materia_id": 3, "porcentaje": 30 },
    { "materia_id": 4, "porcentaje": 15 }
  ]
}
```

Crear pregunta:

```json
{
  "materia_id": 1,
  "enunciado": "Pregunta de seleccion multiple",
  "puntaje": 1,
  "activa": true
}
```

Crear opciones:

```json
{
  "opciones": [
    { "texto_opcion": "Opcion A", "es_correcta": false, "orden": 1 },
    { "texto_opcion": "Opcion B", "es_correcta": true, "orden": 2 },
    { "texto_opcion": "Opcion C", "es_correcta": false, "orden": 3 }
  ]
}
```

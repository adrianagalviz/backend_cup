# Backend fase 18 - Resolucion de examen por alumno

## Alcance implementado

Se implementaron las subfases 18.1 a 18.4 para que el alumno pueda rendir examenes habilitados:

- 18.1 Consulta de examenes habilitados.
- 18.2 Visualizacion del examen sin exponer respuestas correctas.
- 18.3 Envio de respuestas, correccion automatica y registro de nota parcial.
- 18.4 Consulta del resultado del examen.

## Endpoints

Todos los endpoints requieren autenticacion interna y rol `alumno`.

```text
GET /api/v1/alumno/examenes/habilitados
GET /api/v1/alumno/examenes/{id}
POST /api/v1/alumno/examenes/{id}/responder
GET /api/v1/alumno/examenes/{id}/resultado
```

## Tablas usadas

```text
examen
examen_materia_porcentaje
pregunta
opcion_pregunta
intento_examen
respuesta_alumno
nota_examen_materia
nota_parcial
alumno
usuario
```

## Reglas principales

1. El alumno solo ve examenes habilitados de su misma `gestion_academica_id`.
2. Se respetan `fecha_inicio` y `fecha_fin` si fueron configuradas.
3. El examen se devuelve sin `es_correcta` en las opciones.
4. El alumno debe responder todas las preguntas activas del examen.
5. No se puede responder una pregunta mas de una vez.
6. La opcion elegida debe pertenecer a la pregunta respondida.
7. Se evita doble respuesta del mismo examen por alumno.
8. Las respuestas se registran en `respuesta_alumno`.
9. El intento se registra en `intento_examen`.
10. La nota por materia se calcula como respuestas correctas sobre preguntas de esa materia.
11. La nota ponderada aplica el porcentaje de `examen_materia_porcentaje`.
12. La nota total del parcial es la suma de notas ponderadas.
13. La nota final se guarda en `nota_parcial`.
14. El resultado no expone opciones correctas ni detalle de respuestas.

## Ejemplo para responder

```json
{
  "respuestas": [
    { "pregunta_id": 1, "opcion_pregunta_id": 2 },
    { "pregunta_id": 2, "opcion_pregunta_id": 5 },
    { "pregunta_id": 3, "opcion_pregunta_id": 9 }
  ]
}
```

## Resultado calculado

El backend devuelve:

```text
intento_id
estado
fecha_inicio
fecha_fin
nota_total
nota_parcial
examen
notas_por_materia
```

No se devuelven respuestas correctas, opciones correctas ni comparaciones pregunta por pregunta.

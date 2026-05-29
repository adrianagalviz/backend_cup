# Backend fase 20 - Asignacion final de carrera por nota y cupo

## Alcance implementado

Se implementaron las subfases 20.1 a 20.5:

- 20.1 Obtener alumnos aprobados por gestion.
- 20.2 Asignar primera opcion si tiene cupo.
- 20.3 Asignar segunda opcion si la primera esta llena.
- 20.4 Asignar carrera con menos personas si ambas opciones estan llenas.
- 20.5 Endpoint administrativo para ejecutar la asignacion completa.

## Endpoint

Solo administrador:

```text
POST /api/v1/admisiones/asignar-carreras
```

## Entrada

```json
{
  "gestion_academica_id": 1,
  "reasignar": false
}
```

`reasignar` es opcional. Por defecto no se reasignan alumnos que ya tengan carrera final asignada.

## Tablas usadas

```text
promedio_final
alumno
postulante
postulacion
carrera
cupo_carrera
```

## Reglas principales

1. Solo se toman alumnos con `promedio_final.estado_final = aprobado`.
2. Se filtra por `gestion_academica_id`.
3. Se ordena de mayor a menor promedio.
4. Ante empate, se ordena por `alumno_id`.
5. Primero se intenta asignar `primera_carrera_id`.
6. Si no hay cupo, se intenta `segunda_carrera_id`.
7. Si ambas opciones no tienen cupo, se busca la carrera activa con menos personas asignadas y cupo disponible.
8. Los cupos ocupados se calculan desde `postulacion.carrera_asignada_id`.
9. El resultado se guarda en `postulacion.carrera_asignada_id`.
10. El motivo se guarda como `primera_opcion`, `segunda_opcion` o `carrera_con_menos_personas`.
11. Se guarda `orden_prioridad` segun el orden por nota.
12. Se actualiza `asignado_en`.
13. Si no hay cupos disponibles, el alumno queda omitido en el resumen.
14. Si `reasignar = false`, no se toca una postulacion ya asignada.
15. Si `reasignar = true`, se limpian asignaciones aprobadas de esa gestion y se recalcula el proceso.

## Respuesta

La respuesta incluye:

```text
gestion_academica_id
reasignado
cantidad_aprobados
cantidad_asignados
cantidad_omitidos
aprobados_ordenados
asignaciones
omitidos
cupos
```

## Nota de implementacion

La tabla `cupo_carrera` no tiene una columna fisica de ocupados. Por eso el backend calcula cupos ocupados contando las postulaciones asignadas por carrera y gestion.

# Backend fase 19 - Notas, promedios y estado final

## Alcance implementado

Se implementaron las subfases 19.1 a 19.4:

- 19.1 Consulta y consolidacion de notas parciales generadas al responder examenes.
- 19.2 Calculo de promedio final con los 3 parciales.
- 19.3 Determinacion automatica de estado `aprobado` o `reprobado`.
- 19.4 Consultas de notas, promedios, aprobados y reprobados con permisos por rol.

## Endpoints

Calculo administrativo:

```text
POST /api/v1/promedios/calcular
```

Consultas para administrador y alumno, respetando permisos:

```text
GET /api/v1/notas/alumno/{id}
GET /api/v1/promedios
GET /api/v1/promedios/aprobados
GET /api/v1/promedios/reprobados
```

## Tablas usadas

```text
nota_parcial
promedio_final
alumno
postulacion
examen
gestion_academica
```

## Reglas principales

1. Las notas parciales se registran al finalizar un examen en fase 18.
2. Cada nota parcial debe estar entre 0 y 100.
3. Para calcular promedio deben existir los parciales 1, 2 y 3.
4. La formula usada es `(parcial_1 + parcial_2 + parcial_3) / 3`.
5. El promedio se redondea a 2 decimales.
6. `promedio >= 60` genera estado `aprobado`.
7. `promedio < 60` genera estado `reprobado`.
8. El resultado se guarda en `promedio_final`.
9. Tambien se actualiza `alumno.estado_academico`.
10. Tambien se actualiza `postulacion.promedio_final` y `postulacion.estado_final` para la asignacion por cupos.
11. El administrador puede consultar promedios generales.
12. El alumno solo puede consultar sus propias notas y promedios.

## Ejemplos de calculo

Calcular un alumno:

```json
{
  "alumno_id": 1
}
```

Calcular todos los alumnos de una gestion:

```json
{
  "gestion_academica_id": 1
}
```

## Respuesta de consulta

Las consultas devuelven:

```text
alumno
gestion_academica
parcial_1
parcial_2
parcial_3
promedio
estado_final
calculado_en
```

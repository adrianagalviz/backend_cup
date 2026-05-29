# Backend fase 21.6 a 21.11 - Reportes administrativos

## Alcance implementado

Se implementaron las subfases 21.6 a 21.11:

- 21.6 Estadisticas por materia.
- 21.7 Reporte de docentes por grupos.
- 21.8 Grupos con mayor cantidad de aprobados.
- 21.9 Reporte de asistencia de docentes.
- 21.10 Reporte de asistencia de alumnos.
- 21.11 Registro de reportes generados.

## Endpoints

Todos los endpoints requieren autenticacion interna y rol `administrador`.

```text
GET /api/v1/reportes/estadisticas-materia
GET /api/v1/reportes/docentes-grupos
GET /api/v1/reportes/grupos-mayor-aprobados
GET /api/v1/reportes/asistencia-docentes
GET /api/v1/reportes/asistencia-alumnos
```

## Filtros disponibles

Estadisticas por materia:

```text
gestion_academica_id
```

Docentes por grupos:

```text
gestion_academica_id
docente_id
```

Grupos con mayor cantidad de aprobados:

```text
gestion_academica_id
```

Asistencia de docentes:

```text
gestion_academica_id
docente_id
fecha_desde
fecha_hasta
```

Asistencia de alumnos:

```text
gestion_academica_id
alumno_id
grupo_id
fecha_desde
fecha_hasta
```

## Datos devueltos

### Estadisticas por materia

```text
materia
cantidad de notas
promedio de materia
promedio ponderado
nota minima
nota maxima
cantidad de aprobados
cantidad de reprobados
```

### Docentes por grupos

```text
docente
materia
grupo
horario
gestion_academica_id
```

### Grupos con mayor cantidad de aprobados

```text
grupo
gestion_academica_id
cupo_maximo
cantidad_alumnos
cantidad_aprobados
activo
```

### Asistencia de docentes

```text
docente
total_registros
presentes
retrasos
faltas
pendientes
```

### Asistencia de alumnos

```text
alumno
total_registros
presentes
retrasos
faltas
pendientes
```

## Registro de reportes generados

Cada endpoint de reportes administrativos registra automaticamente:

```text
usuario_id
tipo_reporte
formato_exportacion
parametros
archivo_url
generado_en
```

El registro se guarda en la tabla `reporte_generado`.

En estas subfases no se guarda archivo porque la exportacion PDF/Excel corresponde a la fase 22. Por eso `formato_exportacion` y `archivo_url` quedan en `null`.

## Tablas usadas

```text
nota_examen_materia
materia
intento_examen
examen
asignacion_docente
docente
persona
grupo
horario_clase
dia
grupo_alumno
promedio_final
asistencia_docente
asistencia_alumno
alumno
reporte_generado
usuario
```

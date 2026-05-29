# Backend fase 21.1 a 21.5 - Reportes administrativos

## Alcance implementado

Se implementaron las subfases 21.1 a 21.5:

- 21.1 Reporte de lista general de postulantes.
- 21.2 Reporte de alumnos aprobados.
- 21.3 Reporte de alumnos reprobados.
- 21.4 Reporte de promedios generales.
- 21.5 Reporte de grupos habilitados.

## Endpoints

Todos los endpoints requieren autenticacion interna y rol `administrador`.

```text
GET /api/v1/reportes/postulantes
GET /api/v1/reportes/aprobados
GET /api/v1/reportes/reprobados
GET /api/v1/reportes/promedios
GET /api/v1/reportes/grupos
```

## Filtros disponibles

Reportes de postulantes:

```text
gestion_academica_id
estado_postulante
por_pagina
```

Reportes de aprobados, reprobados y promedios:

```text
gestion_academica_id
por_pagina
```

Reporte de grupos:

```text
gestion_academica_id
activo
por_pagina
```

## Datos devueltos

### Postulantes

```text
CI
nombres
apellidos
correo
celular
estado
estado_requisitos
estado_pago
gestion
primera opcion
segunda opcion
carrera asignada
```

### Aprobados y reprobados

```text
alumno
codigo_alumno
CI
nombres
apellidos
gestion
notas parciales
promedio
estado final
opciones de carrera
carrera asignada
```

### Promedios generales

```text
parcial_1
parcial_2
parcial_3
notas_parciales
promedio
estado_final
datos del alumno
gestion
opciones de carrera
```

### Grupos

```text
grupo
gestion
cupo maximo
cantidad de alumnos activos
cupos disponibles
estado activo
```

## Tablas usadas

```text
postulante
persona
postulacion
carrera
gestion_academica
promedio_final
nota_parcial
alumno
grupo
grupo_alumno
```

## Nota de alcance

Esta entrega cubre solo las subfases 21.1 a 21.5. Las estadisticas por materia, docentes por grupos, asistencia y registro historico de reportes corresponden a subfases posteriores de la fase 21.

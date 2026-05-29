# Backend fase 23 - Reportes por comando de voz

## Alcance implementado

Se implementaron las subfases 23.1 a 23.4:

- 23.1 Aclaracion tecnica: el backend no procesa audio.
- 23.2 Endpoint para recibir texto ya interpretado.
- 23.3 Comandos iniciales permitidos.
- 23.4 Seleccion opcional de formato PDF o Excel.

## Endpoint

Solo administrador:

```text
POST /api/v1/reportes/comando-voz
```

## Entrada

```json
{
  "texto": "listar postulantes",
  "formato": "pdf",
  "filtros": {
    "gestion_academica_id": 1
  }
}
```

`formato` es opcional. Si no se envia, el backend devuelve datos JSON del reporte detectado.

`filtros` es opcional y puede incluir:

```text
gestion_academica_id
fecha_desde
fecha_hasta
por_pagina
```

## Comandos permitidos

```text
listar alumnos aprobados
listar alumnos reprobados
listar alumnos reprobados y aprobados
listar postulantes
listar grupos habilitados
listar asistencia docentes
listar asistencia alumnos
listar promedios generales
```

## Mapeo de comandos

```text
listar alumnos aprobados -> aprobados
listar alumnos reprobados -> reprobados
listar alumnos reprobados y aprobados -> promedios
listar postulantes -> postulantes
listar grupos habilitados -> grupos
listar asistencia docentes -> asistencia-docentes
listar asistencia alumnos -> asistencia-alumnos
listar promedios generales -> promedios
```

## Reglas

1. El backend no recibe audio.
2. El backend no procesa voz.
3. El frontend debe usar Web Speech API y enviar texto.
4. El backend limpia espacios y convierte el texto a minusculas.
5. Solo se aceptan comandos definidos en esta fase.
6. Si el comando no coincide, se registra igualmente sin intencion detectada.
7. Si el comando coincide y no hay formato, se devuelve el reporte en JSON.
8. Si `formato = pdf`, se usa la exportacion PDF de fase 22.
9. Si `formato = excel`, se usa la exportacion Excel de fase 22.
10. Se registra cada uso en `comando_voz_reporte`.
11. Si se genera reporte o exportacion, se relaciona con `reporte_generado`.

## Tablas usadas

```text
comando_voz_reporte
reporte_generado
usuario
```

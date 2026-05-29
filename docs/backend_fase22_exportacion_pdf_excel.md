# Backend fase 22 - Exportacion PDF y Excel

## Alcance implementado

Se implementaron las subfases 22.1 a 22.3:

- 22.1 Configuracion de librerias y servicios de exportacion.
- 22.2 Exportacion PDF de reportes.
- 22.3 Exportacion Excel de reportes.

## Librerias instaladas

```text
dompdf/dompdf
phpoffice/phpspreadsheet
```

`PhpSpreadsheet` se instalo ignorando el requisito `ext-gd` porque esta fase genera hojas simples sin imagenes ni graficos. Para un entorno final se recomienda habilitar `gd` en PHP.

## Carpeta de salida

```text
storage/reports
```

La carpeta contiene `.gitignore` para no versionar archivos generados.

## Endpoint

Solo administrador:

```text
GET /api/v1/reportes/{tipo}/exportar?formato=pdf
GET /api/v1/reportes/{tipo}/exportar?formato=excel
```

## Tipos de reporte permitidos

```text
postulantes
aprobados
reprobados
promedios
grupos
estadisticas-materia
docentes-grupos
grupos-mayor-aprobados
asistencia-docentes
asistencia-alumnos
```

## Filtros aceptados

El endpoint acepta los filtros de los reportes administrativos ya implementados:

```text
gestion_academica_id
estado_postulante
activo
docente_id
alumno_id
grupo_id
fecha_desde
fecha_hasta
por_pagina
```

## Servicios creados

```text
App\Services\Reports\PdfExportService
App\Services\Reports\ExcelExportService
App\Services\Reports\ReportExportService
```

## Registro en base de datos

Cada exportacion registra en `reporte_generado`:

```text
usuario_id
tipo_reporte
formato_exportacion
parametros
archivo_url
generado_en
```

## Respuesta

El backend devuelve:

```text
reporte
tipo_reporte
formato
archivo.nombre
archivo.ruta
archivo.ruta_absoluta
total_filas
```

La respuesta devuelve la ruta del archivo generado. La descarga directa o exposicion publica del archivo puede conectarse desde el frontend usando esa ruta o una ruta futura de descarga controlada.

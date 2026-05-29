# Backend - Fase 24: Carga masiva Excel/CSV

## Objetivo

Implementar la carga masiva de datos desde archivos CSV o Excel, registrando la cabecera de la carga en `carga_masiva` y el resultado de cada fila en `detalle_carga_masiva`.

## Endpoints

Todos los endpoints requieren autenticacion interna y rol `administrador`.

```text
POST /api/v1/cargas/csv
POST /api/v1/cargas/excel
GET  /api/v1/cargas
GET  /api/v1/cargas/{id}/detalle
```

## Subfase 24.1: Subida de archivo

- Se agrego `BulkLoadController`.
- `POST /api/v1/cargas/csv` valida `archivo` con extension `csv` o `txt`.
- `POST /api/v1/cargas/excel` valida `archivo` con extension `xlsx` o `xls`.
- El tamano maximo aceptado es 5 MB.
- El archivo se guarda temporalmente en `storage/temp`.
- El registro inicial se crea en `carga_masiva` con estado `procesando`.

Campos de request:

```text
archivo: file, requerido
tipo_carga: string opcional, por defecto usuarios
```

## Subfase 24.2: Lectura de archivo

- Los CSV se leen con `fgetcsv`.
- Los Excel se leen con `PhpSpreadsheet`.
- La primera fila se interpreta como encabezado.
- Los encabezados se normalizan a minusculas, sin acentos y con guion bajo.
- Se recorren todas las filas posteriores.
- Las filas completamente vacias se ignoran.

## Subfase 24.3: Validacion por fila

La carga masiva soporta los roles necesarios para esta fase:

```text
docente
alumno
```

Para `docente`, los campos obligatorios son:

```text
rol
cedula_identidad
nombres
apellido_paterno
correo
celular
```

Campos opcionales para `docente`:

```text
apellido_materno
fecha_nacimiento
sexo
direccion
telefono
ciudad
nombre_usuario
password
correo_verificado
```

Para `alumno`, se usa la conversion existente de postulante a alumno. Campo obligatorio:

```text
rol
postulante_id
```

Validaciones implementadas:

- Campos obligatorios por rol.
- CI duplicado contra `persona.cedula_identidad` y dentro del archivo.
- Correo valido.
- Correo duplicado contra `persona.correo` y dentro del archivo.
- Rol permitido.
- `postulante_id` existente.
- Postulante no convertido previamente en `alumno`.
- Nombre de usuario no duplicado cuando se envia.
- Errores registrados por fila en `detalle_carga_masiva`.

## Subfase 24.4: Insercion de registros validos

- Los docentes se crean usando `TeacherManagementService`.
- Los alumnos se crean usando `ApplicantConversionService`, respetando las reglas ya implementadas de requisitos aprobados, pago confirmado y pago validado por administrador.
- Cada fila valida se procesa dentro de transaccion.
- Una fila con error no detiene las demas filas.
- Se evitan duplicados antes de insertar.
- La gestion academica del alumno se asocia mediante la conversion del postulante, usando `postulante.gestion_academica_id`.

## Subfase 24.5: Registro de carga y detalle

- `GET /api/v1/cargas` lista el historial paginado de cargas.
- `GET /api/v1/cargas/{id}/detalle` muestra el resumen y el detalle de filas.
- `carga_masiva.total_registros` guarda filas leidas no vacias.
- `carga_masiva.registros_exitosos` guarda filas insertadas correctamente.
- `carga_masiva.registros_error` guarda filas con error.
- El estado final queda como:
  - `finalizado` cuando no hay errores.
  - `con_errores` cuando una o mas filas fallan.
  - `fallido` cuando no se puede procesar el archivo.

## Archivos creados o modificados

- `app/Models/CargaMasivaModel.php`
- `app/Models/DetalleCargaMasivaModel.php`
- `app/Services/BulkLoads/BulkLoadService.php`
- `app/Http/Controllers/Api/BulkLoadController.php`
- `routes/api.php`
- `storage/temp/.gitignore`
- `tests/Feature/ExampleTest.php`

## Formato ejemplo CSV

Docentes:

```csv
rol,cedula_identidad,nombres,apellido_paterno,apellido_materno,correo,celular,nombre_usuario,password
docente,1234567,Juan,Perez,Rojas,juan.perez@example.com,70000001,docente_1234567,secret123
```

Alumnos:

```csv
rol,postulante_id
alumno,15
```

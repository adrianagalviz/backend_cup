# Fase 7 - Requisitos, documentos y Cloudinary

## Estado

Fase 7 implementada con Laravel, API REST versionada en `/api/v1`, Cloudinary configurado desde `.env` y persistencia en PostgreSQL 16.

## Subfase 7.1 - Configuracion de Cloudinary

Archivos creados:

- `config/cloudinary.php`
- `app/Services/Documents/CloudinaryService.php`

Variables usadas desde `.env`:

- `CLOUDINARY_CLOUD_NAME`
- `CLOUDINARY_API_KEY`
- `CLOUDINARY_API_SECRET`
- `CLOUDINARY_FOLDER`

`CLOUDINARY_FOLDER` es opcional. Si no existe, se usa `cup-ficct/postulantes/titulos-bachiller`.

El servicio usa subida firmada a la API de Cloudinary y devuelve:

- URL segura.
- `public_id`.
- Formato del archivo.

## Subfase 7.2 - Subida del titulo de bachiller

Endpoint implementado:

```text
POST /api/v1/postulantes/{id}/documentos
```

Campo esperado:

```text
titulo_bachiller
```

Validaciones implementadas:

- El postulante debe existir.
- El archivo es obligatorio.
- El archivo debe ser imagen.
- Formatos permitidos: `jpg`, `jpeg`, `png`, `webp`.
- Tamano maximo: 5 MB.
- Subida a Cloudinary.
- Registro en `documento_postulante`.
- Tipo de documento fijo: `titulo_bachiller`.
- Estado inicial del documento: `pendiente`.
- Estado de requisitos del postulante: `pendiente`.

## Subfase 7.3 - Listado de documentos del postulante

Endpoint implementado:

```text
GET /api/v1/postulantes/{id}/documentos
```

Proteccion:

- Requiere token interno.
- Requiere rol `administrador`.

Devuelve:

- URL de Cloudinary.
- `public_id`.
- Tipo de documento.
- Formato.
- Estado de revision.
- Observacion.
- Fecha de subida.

## Subfase 7.4 - Validacion de requisitos por administrador

Endpoint implementado:

```text
PATCH /api/v1/postulantes/{id}/requisitos/validar
```

Proteccion:

- Requiere token interno.
- Requiere rol `administrador`.

Datos esperados:

- `estado_revision`: `aprobado` o `rechazado`.
- `observacion`: opcional.

Validaciones implementadas:

- El postulante debe existir.
- Debe existir imagen de titulo de bachiller.
- El estado debe ser `aprobado` o `rechazado`.
- Se actualiza `documento_postulante.estado_revision`.
- Se actualiza `postulante.estado_requisitos`.
- Si se aprueba, `postulante.estado_postulante` pasa a `pendiente_pago`.
- Si se rechaza, `postulante.estado_postulante` pasa a `rechazado`.

## Aclaracion sobre requisitos

`backend_subfases.md` menciona requisitos, pero `base_de_datos.md` no define una tabla `requisito_postulante` en el esquema final implementado. Por eso la Fase 7 se resolvio usando las columnas reales:

- `postulante.estado_requisitos`
- `postulante.observacion`
- `documento_postulante.estado_revision`
- `documento_postulante.observacion`

No se creo una tabla adicional para no inventar estructura fuera de `base_de_datos.md`.

## Archivos creados o modificados

- `config/cloudinary.php`
- `app/Services/Documents/CloudinaryService.php`
- `app/Services/Documents/ApplicantDocumentService.php`
- `app/Http/Controllers/Api/ApplicantDocumentController.php`
- `app/Models/DocumentoPostulanteModel.php`
- `routes/api.php`
- `tests/Feature/ExampleTest.php`
- `docs/backend_fase7_requisitos_documentos_cloudinary.md`

## Verificacion

Comandos:

```bash
php artisan route:list --path=api/v1/postulantes
php artisan test
```

Pruebas manuales:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/postulantes/1/documentos
```

Debe responder `422` si no se envia imagen.

```bash
curl http://127.0.0.1:8000/api/v1/postulantes/1/documentos
```

Debe responder `401` si no se envia token de administrador.

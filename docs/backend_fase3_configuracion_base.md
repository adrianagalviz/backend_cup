# Fase 3 - Configuracion base del backend Laravel

Documento de cumplimiento de las subfases 3.1 a 3.5 de `backend_subfases.md`, adaptadas obligatoriamente a Laravel.

## Criterio principal

La Fase 3 se implementa con Laravel, no con router PHP puro. Por eso las tareas de entrada unica, router, autoload, entorno y conexion PDO se cumplen mediante la estructura oficial de Laravel:

- `public/index.php`
- `vendor/autoload.php`
- `bootstrap/app.php`
- `routes/api.php`
- `config/database.php`
- `app/Http/Controllers`
- `app/Http/Middleware`
- `app/Services`
- `app/Support`

No se agregan funcionalidades fuera del alcance ni se modifican reglas de negocio.

## Subfase 3.1: Creacion de estructura de carpetas

Estructura verificada y completada:

```text
backend/
|-- public/
|-- app/
|   |-- Http/
|   |   |-- Controllers/
|   |   |-- Middleware/
|   |   `-- Requests/
|   |-- Models/
|   |-- Services/
|   |-- Support/
|   |-- Routes/
|   |-- Validators/
|   `-- Helpers/
|-- config/
|-- database/
|-- storage/
|   |-- logs/
|   |-- reports/
|   `-- temp/
```

Adaptacion Laravel:

- `Controllers` vive en `app/Http/Controllers`.
- `Middlewares` vive en `app/Http/Middleware`.
- `Validators` queda preparado como carpeta y Laravel tambien usara `app/Http/Requests` para Form Requests.
- `Routes` principal vive en `routes/`; se deja `app/Routes` como carpeta preparada si se requieren agrupadores internos futuros.
- `Helpers` queda preparado y las utilidades base actuales viven en `app/Support`.

## Subfase 3.2: Configuracion de Composer

Composer ya esta configurado por Laravel en `backend/composer.json`.

Autoload PSR-4 activo:

```json
"App\\": "app/"
```

Dependencias disponibles:

- PHP `^8.2`.
- Laravel Framework `^12.0`.
- `vlucas/phpdotenv` incluido por Laravel.
- PHPUnit y herramientas de desarrollo incluidas por Laravel.

Comandos de verificacion:

```bash
composer dump-autoload
php artisan test
```

## Subfase 3.3: Configuracion de entrada unica

Archivo principal:

```text
backend/public/index.php
```

Cumplimiento en Laravel:

1. Carga autoload con `vendor/autoload.php`.
2. Laravel carga variables `.env` durante bootstrap.
3. Las respuestas API se fuerzan a JSON para rutas `api/*`.
4. CORS se configura con `config/cors.php` y middleware de Laravel.
5. Laravel captura metodo HTTP y URI desde `Request::capture()`.
6. Laravel envia la peticion al router.
7. Los errores API se renderizan como JSON desde `bootstrap/app.php`.

Archivos relacionados:

- `public/index.php`
- `bootstrap/app.php`
- `app/Http/Middleware/ForceJsonResponse.php`
- `config/cors.php`

## Subfase 3.4: Creacion del router basico

El router base se implementa con `routes/api.php`.

Rutas actuales:

```text
GET /api/v1/salud
GET /api/v1/conexion-postgresql
```

Laravel soporta nativamente:

- `Route::get`
- `Route::post`
- `Route::put`
- `Route::patch`
- `Route::delete`
- parametros como `/recurso/{id}`
- asociacion con controladores

Se agrego respuesta 404 JSON para rutas API inexistentes:

```json
{
  "ok": false,
  "mensaje": "Ruta API no encontrada.",
  "errores": []
}
```

No se crean endpoints funcionales adicionales porque cada modulo debe implementarse en su fase correspondiente.

## Subfase 3.5: Conexion a PostgreSQL con PDO

Conexion configurada en:

```text
config/database.php
```

Variables usadas desde `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cup_ficct
DB_USERNAME=postgres
DB_PASSWORD=1313
```

Servicio creado para prueba controlada de conexion:

```text
app/Services/Database/PostgreSqlConnectionService.php
```

Cumplimiento:

1. `config/database.php` existe.
2. Laravel lee credenciales desde `.env`.
3. Existe clase de conexion/prueba en `PostgreSqlConnectionService`.
4. La conexion usa PDO PostgreSQL por medio de Laravel.
5. PDO usa `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`.
6. La consulta simple usa `select version() as version`.
7. El error de conexion no expone la contrasena.

Endpoint de verificacion:

```text
GET /api/v1/conexion-postgresql
```

## Verificacion

Comandos usados:

```bash
composer dump-autoload
php artisan route:list
php artisan test
```

Tambien se verifica la conexion real con:

```text
GET /api/v1/conexion-postgresql
```

## Subfase 3.6: Helpers base

Helpers creados:

- `app/Helpers/ResponseHelper.php`
- `app/Helpers/ErrorHelper.php`
- `app/Helpers/AuthHelper.php`
- `app/Helpers/DateHelper.php`
- `app/Helpers/ValidationHelper.php`

Cumplimiento:

1. `ResponseHelper` centraliza respuestas JSON correctas y de error.
2. `ErrorHelper` centraliza mensajes seguros y errores de conexion sin contrasena.
3. `DateHelper` centraliza fecha/hora actual para uso futuro.
4. `ValidationHelper` centraliza respuestas de validacion fallida.
5. `AuthHelper` obtiene el usuario autenticado desde un token Bearer interno.

El backend ya cuenta con funciones comunes para evitar repetir codigo en controladores y servicios.

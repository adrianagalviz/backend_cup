# Fase 4 - Modulo de autenticacion y sesiones completo

Documento de verificacion de Fase 4 completa, subfases 4.1 a 4.8.

## Subfases ya cubiertas

Las subfases 4.1 a 4.4 quedaron implementadas en `backend_fase4_auth_4_1_4_4.md`.

Este documento completa y verifica:

- 4.5 Logout.
- 4.6 Perfil autenticado.
- 4.7 Middleware de autenticacion.
- 4.8 Middleware de autorizacion por rol.

## Subfase 4.5: Logout

Endpoint implementado:

```text
POST /api/v1/auth/logout
```

Proteccion:

```text
auth.internal
```

Cumplimiento:

1. Recibe token actual desde `Authorization: Bearer`.
2. Invalida el token usando cache de Laravel con hash SHA-256 del token hasta su expiracion.
3. Cierra sesion logica sin crear tablas fuera del esquema definido.
4. Responde confirmacion JSON.

Archivo principal:

- `app/Http/Controllers/Api/AuthController.php`
- `app/Services/Auth/InternalTokenService.php`

## Subfase 4.6: Perfil autenticado

Endpoint implementado:

```text
GET /api/v1/auth/perfil
```

Cumplimiento:

1. Valida token con middleware `auth.internal`.
2. Obtiene usuario autenticado desde el request.
3. Obtiene rol con relacion `rol`.
4. Obtiene datos de persona con relacion `persona`.
5. Obtiene datos especificos segun rol:
   - `administrador`
   - `docente`
   - `alumno`
6. Responde perfil con formato JSON estandar.

## Subfase 4.7: Middleware de autenticacion

Middleware creado:

```text
app/Http/Middleware/AuthenticateInternalToken.php
```

Alias registrado:

```text
auth.internal
```

Cumplimiento:

1. Lee encabezado `Authorization`.
2. Valida token firmado.
3. Obtiene usuario autenticado.
4. Bloquea si no hay token.
5. Bloquea si el token es invalido, expiro o fue revocado.
6. Bloquea si el usuario esta desactivado.

## Subfase 4.8: Middleware de autorizacion por rol

Middleware creado:

```text
app/Http/Middleware/AuthorizeRole.php
```

Alias registrado:

```text
role
```

Uso previsto:

```php
Route::middleware(['auth.internal', 'role:administrador'])->group(function () {
    // Rutas exclusivas para administrador.
});

Route::middleware(['auth.internal', 'role:docente'])->group(function () {
    // Rutas exclusivas para docente.
});

Route::middleware(['auth.internal', 'role:alumno'])->group(function () {
    // Rutas exclusivas para alumno.
});
```

Cumplimiento:

1. Recibe rol requerido.
2. Verifica rol del usuario autenticado.
3. Permite acceso si coincide.
4. Bloquea acceso si no coincide.
5. Permite rutas exclusivas para administrador.
6. Permite rutas exclusivas para docente.
7. Permite rutas exclusivas para alumno.

No se crean endpoints funcionales de modulos futuros solo para probar roles, porque eso corresponderia a fases posteriores.

## Rutas de Fase 4

```text
POST /api/v1/auth/login
POST /api/v1/auth/alumno/login
POST /api/v1/auth/firebase
POST /api/v1/auth/logout
GET  /api/v1/auth/perfil
```

## Verificacion

Comandos usados:

```bash
composer dump-autoload
php artisan route:list
php artisan migrate:status
php artisan test
```

La Fase 4 queda completa sin agregar roles fuera de:

- administrador
- docente
- alumno

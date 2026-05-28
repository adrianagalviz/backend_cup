# Fase 4 - Autenticacion y sesiones, subfases 4.1 a 4.4

Documento de cumplimiento parcial de Fase 4 segun `backend_subfases.md`, usando Laravel y la base definida en `base_de_datos.md`.

## Alcance implementado

Se implementan solo las subfases solicitadas:

- 4.1 Modelo de autenticacion.
- 4.2 Login tradicional.
- 4.3 Login de alumno con codigo automatico.
- 4.4 Firebase Authentication.

No se implementan todavia logout, perfil autenticado, middleware de autenticacion ni middleware por rol, porque pertenecen a subfases posteriores.

## Subfase 4.1: Modelo de autenticacion

Modelos creados:

- `app/Models/UsuarioModel.php`
- `app/Models/RolModel.php`
- `app/Models/PersonaModel.php`
- `app/Models/AdministradorModel.php`
- `app/Models/DocenteModel.php`
- `app/Models/AlumnoModel.php`

Cumplimiento:

1. `UsuarioModel` consulta tabla `usuario`.
2. `RolModel` consulta tabla `rol`.
3. `UserAuthenticationService::findByIdentifier()` busca por `nombre_usuario`, `codigo_acceso` o `persona.correo`.
4. `UsuarioModel` obtiene rol con relacion `rol`.
5. `UsuarioModel` obtiene persona con relacion `persona`.
6. El servicio valida `activo = true` antes de autenticar.

## Subfase 4.2: Login tradicional

Endpoint:

```text
POST /api/v1/auth/login
```

Entrada:

```json
{
  "usuario": "admin@correo.com",
  "password": "clave"
}
```

Cumplimiento:

1. Recibe usuario, codigo o correo en el campo `usuario`.
2. Recibe `password`.
3. Valida usuario obligatorio.
4. Valida password obligatorio.
5. Busca usuario en `usuario`, `persona` y `rol`.
6. Valida contrasena con `password_verify`.
7. Valida que el usuario este activo y tenga rol asociado.
8. Genera token interno firmado.
9. Responde datos minimos del usuario autenticado.

## Subfase 4.3: Login de alumno con codigo automatico

Endpoint directo agregado:

```text
POST /api/v1/auth/alumno/login
```

Tambien queda soportado por:

```text
POST /api/v1/auth/login
```

Entrada del endpoint directo:

```json
{
  "codigo_alumno": "2026113541539",
  "password": "opcional_si_el_usuario_tiene_password_hash"
}
```

Cumplimiento:

1. Recibe codigo del alumno.
2. Valida existencia en `usuario.codigo_acceso` o `alumno.codigo_alumno`.
3. Valida rol `alumno`.
4. Valida contrasena si el usuario tiene `password_hash`.
5. Genera token interno firmado.
6. Devuelve perfil minimo del alumno.

## Subfase 4.4: Firebase Authentication

Endpoint:

```text
POST /api/v1/auth/firebase
```

Entrada:

```json
{
  "firebase_token": "token_id_de_firebase"
}
```

Cumplimiento:

1. Recibe token de Firebase.
2. Verifica formato JWT, emisor, audiencia, expiracion, algoritmo RS256 y firma con certificados publicos de Firebase.
3. Obtiene correo verificado desde el payload.
4. Verifica que el correo exista en `persona.correo`.
5. Asocia `firebase_uid` al usuario si aun no esta registrado.
6. Genera token interno firmado.
7. Responde datos minimos del usuario.

Configuracion requerida:

```env
FIREBASE_PROJECT_ID=
```

Si `FIREBASE_PROJECT_ID` no esta configurado, el endpoint responde error controlado.

## Token interno

El token interno se genera en:

```text
app/Services/Auth/InternalTokenService.php
```

Contiene:

- `sub`: id del usuario.
- `rol`: rol del usuario.
- `iat`: fecha de emision.
- `exp`: expiracion.

El token se firma con HMAC SHA-256 usando `APP_KEY`.

## Archivos principales

- `app/Http/Controllers/Api/AuthController.php`
- `app/Services/Auth/UserAuthenticationService.php`
- `app/Services/Auth/InternalTokenService.php`
- `app/Services/Auth/FirebaseTokenVerifier.php`
- `routes/api.php`
- `config/services.php`

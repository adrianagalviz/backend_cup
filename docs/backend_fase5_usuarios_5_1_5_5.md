# Fase 5 - Usuarios, roles y administradores

Documento de cumplimiento de las subfases 5.1 a 5.5 de `backend_subfases.md`, usando Laravel y PostgreSQL.

## Subfase 5.1: Seed de roles base

Seeder:

```text
database/seeders/RoleAndSubjectSeeder.php
```

Cumplimiento:

1. Inserta rol `administrador`.
2. Inserta rol `docente`.
3. Inserta rol `alumno`.
4. Usa `upsert` y restriccion `UNIQUE`, por lo tanto no duplica roles.
5. No existe endpoint de creacion de roles y la base tiene `CHECK` que solo permite esos tres nombres.

Resultado:

Solo existen tres roles:

- administrador
- docente
- alumno

## Subfase 5.2: Creacion del administrador inicial

Seeder:

```text
database/seeders/InitialAdminSeeder.php
```

Variables configurables en `.env`:

```env
ADMIN_INITIAL_CI=0000001
ADMIN_INITIAL_NAMES=Administrador
ADMIN_INITIAL_LASTNAME=Inicial
ADMIN_INITIAL_SECOND_LASTNAME=
ADMIN_INITIAL_EMAIL=admin@cupficct.local
ADMIN_INITIAL_PHONE=
ADMIN_INITIAL_CITY="Santa Cruz"
ADMIN_INITIAL_USERNAME=admin
ADMIN_INITIAL_PASSWORD=admin12345
```

Cumplimiento:

1. Crea persona inicial.
2. Crea usuario administrador inicial.
3. Encripta contrasena con `password_hash`.
4. Crea registro en `administrador`.
5. Se verifico inicio de sesion con `POST /api/v1/auth/login`.

## Subfase 5.3: Crear administradores desde el sistema

Endpoint:

```text
POST /api/v1/usuarios/administradores
```

Proteccion:

```text
auth.internal
role:administrador
```

Cumplimiento:

1. Valida que quien solicita sea administrador.
2. Recibe datos de persona.
3. Valida CI unico.
4. Valida correo valido y unico.
5. Crea persona.
6. Crea usuario.
7. Asocia rol administrador.
8. Crea registro en `administrador`.

## Subfase 5.4: Listado y consulta de usuarios

Endpoints:

```text
GET /api/v1/usuarios
GET /api/v1/usuarios/{id}
```

Filtros soportados:

```text
rol=administrador|docente|alumno
estado=true|false|1|0
buscar=ci_correo_nombre_usuario
por_pagina=15
```

Cumplimiento:

1. Lista usuarios.
2. Filtra por rol.
3. Filtra por estado.
4. Busca por CI, correo, nombres, apellido paterno, nombre de usuario o codigo.
5. Consulta detalle de usuario.
6. Devuelve paginacion en `meta`.

## Subfase 5.5: Activar, desactivar y actualizar usuarios

Endpoints:

```text
PUT   /api/v1/usuarios/{id}
PATCH /api/v1/usuarios/{id}/estado
```

Cumplimiento:

1. Valida rol administrador.
2. Actualiza datos basicos de persona y usuario.
3. Activa usuario con `activo=true`.
4. Desactiva usuario con `activo=false`.
5. No elimina registros; conserva trazabilidad en `persona`, `usuario` y tablas por rol.

## Archivos principales

- `app/Http/Controllers/Api/UserController.php`
- `app/Services/Users/UserManagementService.php`
- `database/seeders/RoleAndSubjectSeeder.php`
- `database/seeders/InitialAdminSeeder.php`
- `routes/api.php`

## Verificacion realizada

Comandos:

```bash
php artisan config:clear
php artisan db:seed
composer dump-autoload
php artisan route:list
php artisan test
```

Prueba HTTP manual:

1. Login con administrador inicial.
2. Listado de usuarios.
3. Creacion de administrador.
4. Consulta del usuario creado.
5. Actualizacion de datos basicos.
6. Desactivacion del usuario.
7. Verificacion de que `POST /api/v1/roles` responde 404, porque no existe creacion de roles desde backend.

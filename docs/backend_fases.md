# backend_fases.md

# Plan de desarrollo por fases del Backend - Aplicación Web de Admisión Universitaria (CUP) para la FICCT

## 1. Objetivo del archivo

Este archivo tiene como objetivo definir, de manera ordenada y específica, las fases para desarrollar el backend de la **Aplicación Web de Admisión Universitaria (CUP) para la FICCT**.

El backend será desarrollado **netamente en PHP**, conectado a una base de datos **PostgreSQL 16**, administrada desde **pgAdmin 4**.

Este documento sirve como guía para construir la estructura del backend de forma progresiva, sin eliminar requisitos, sin cambiar el alcance definido y sin inventar funcionalidades que no correspondan al contexto del proyecto.

---

## 2. Tecnologías obligatorias del backend

### 2.1 Lenguaje principal

- PHP 8.2.12 CLI.
- Backend desarrollado netamente en PHP.
- No se define uso de Laravel ni otro framework pesado.
- Se recomienda una estructura propia tipo MVC/API REST para mantener ordenado el proyecto.

### 2.2 Servidor local

- Apache.
- XAMPP como entorno local de desarrollo.

### 2.3 Base de datos

- PostgreSQL 16.
- pgAdmin 4 para crear, visualizar y administrar la base de datos.

### 2.4 Conexión PHP con PostgreSQL

- Usar PDO con driver `pgsql`.
- Evitar consultas SQL concatenadas directamente.
- Usar consultas preparadas para prevenir inyección SQL.

### 2.5 Servicios externos que debe consumir el backend

El backend deberá integrarse con:

- Stripe para pagos.
- Cloudinary para almacenar imágenes del título de bachiller.
- Firebase Authentication para validar tokens de login con Google/correo.
- Web Speech API será manejada principalmente desde frontend, pero el backend deberá recibir el texto interpretado para ejecutar reportes.

---

## 3. Alcance general del backend

El backend debe cubrir los siguientes módulos:

1. Configuración del proyecto PHP.
2. Conexión con PostgreSQL 16.
3. Migraciones o scripts SQL de creación de base de datos.
4. Autenticación.
5. Roles del sistema.
6. Usuarios.
7. Administradores.
8. Postulantes.
9. Validación de requisitos.
10. Documentos del postulante.
11. Integración con Cloudinary.
12. Pagos mediante Stripe.
13. Conversión de postulante a alumno.
14. Generación automática del código del alumno.
15. Docentes.
16. Gestión académica.
17. Carreras.
18. Cupos por carrera.
19. Materias.
20. Grupos.
21. Aulas.
22. Días.
23. Turnos.
24. Periodos de 45 minutos.
25. Horarios.
26. Asignación de docentes a grupos y materias.
27. Asignación de alumnos a grupos.
28. Asistencia docente.
29. Asistencia de alumnos.
30. Exámenes.
31. Preguntas de selección múltiple.
32. Opciones de respuesta.
33. Respuestas del alumno.
34. Notas.
35. Promedios.
36. Estado final aprobado/reprobado.
37. Asignación de carrera según cupo y nota.
38. Reportes.
39. Exportación PDF.
40. Exportación Excel.
41. Reportes por comando de voz.
42. Carga masiva Excel/CSV.
43. Dashboard administrativo.
44. Seguridad.
45. Validaciones.
46. Pruebas.
47. Preparación para despliegue futuro en Railway.

---

## 4. Roles definitivos del sistema

El backend debe manejar solamente tres roles:

1. Administrador.
2. Docente.
3. Alumno.

No se deben crear roles adicionales como autoridad, coordinador u otros.

### 4.1 Administrador

El administrador tendrá acceso completo al sistema.

Puede:

- Crear administradores.
- Gestionar docentes.
- Gestionar postulantes.
- Gestionar alumnos.
- Gestionar carreras.
- Gestionar cupos.
- Gestionar materias.
- Gestionar grupos.
- Gestionar aulas.
- Gestionar días.
- Gestionar turnos.
- Gestionar periodos.
- Gestionar horarios.
- Gestionar exámenes.
- Crear preguntas.
- Habilitar exámenes.
- Definir porcentajes por materia.
- Validar requisitos.
- Validar pagos.
- Dar acceso al postulante como alumno.
- Ver asistencia de docentes.
- Ver asistencia de alumnos.
- Generar reportes.
- Exportar reportes en PDF.
- Exportar reportes en Excel.
- Usar reportes por comandos de voz.

### 4.2 Docente

El docente tendrá acceso limitado.

Puede:

- Iniciar sesión.
- Ver su perfil.
- Ver su carga horaria.
- Ver sus grupos asignados.
- Ver sus materias asignadas.
- Marcar su asistencia de entrada.
- Marcar su salida o finalización de clase.
- Tomar asistencia a sus alumnos.
- Ver asistencia de sus alumnos.

No puede:

- Ver asistencia de todos los docentes.
- Ver asistencia de todos los alumnos.
- Gestionar usuarios.
- Gestionar pagos.
- Gestionar cupos.
- Gestionar reportes generales.
- Crear administradores.

### 4.3 Alumno

El alumno tendrá acceso limitado.

Puede:

- Iniciar sesión con su código generado automáticamente.
- Ver su perfil.
- Ver sus horarios.
- Marcar su asistencia.
- Ver sus asistencias.
- Dar examen si el administrador lo habilita.

No puede:

- Ver datos de otros alumnos.
- Ver datos administrativos.
- Ver reportes generales.
- Crear preguntas.
- Modificar notas.
- Cambiar horarios.

---

## 5. Tablas base que debe respetar el backend

El backend debe construirse sobre la base de datos definida para el proyecto. La estructura mínima que debe tomar en cuenta incluye las siguientes tablas o equivalentes directos:

### 5.1 Seguridad y usuarios

- `rol`
- `usuario`
- `administrador`
- `docente`
- `alumno`

### 5.2 Personas y postulantes

- `persona`
- `postulante`
- `requisito_postulante`
- `documento_postulante`

### 5.3 Pagos y acceso

- `pago`
- `gestion_academica`

### 5.4 Carreras y cupos

- `carrera`
- `cupo_carrera`
- `postulacion`

### 5.5 Organización académica

- `materia`
- `grupo`
- `aula`
- `dia`
- `turno`
- `periodo`
- `horario_clase`
- `docente_materia_grupo`
- `grupo_alumno`

### 5.6 Asistencia

- `asistencia_docente`
- `asistencia_alumno`

### 5.7 Exámenes y notas

- `examen`
- `examen_materia`
- `pregunta`
- `opcion_respuesta`
- `respuesta_alumno`
- `nota_parcial`
- `promedio_final`

### 5.8 Reportes y carga masiva

- `reporte_generado`
- `carga_masiva`
- `detalle_carga_masiva`

---

## 6. Relaciones principales que debe respetar el backend

### 6.1 Persona, usuario y roles

- Una `persona` puede tener cero o un `usuario`.
- Un `usuario` pertenece a un solo `rol`.
- Un `rol` puede pertenecer a muchos `usuarios`.
- Una `persona` puede ser administrador, docente, alumno o postulante según el flujo del sistema.

Cardinalidades:

- `persona` 1 ── 0..1 `usuario`
- `rol` 1 ── 0..* `usuario`
- `persona` 1 ── 0..1 `administrador`
- `persona` 1 ── 0..1 `docente`
- `persona` 1 ── 0..1 `alumno`
- `persona` 1 ── 0..1 `postulante`

### 6.2 Postulante, requisitos y documentos

- Un postulante debe cumplir requisitos.
- Un postulante debe subir imagen del título de bachiller.
- La imagen del título de bachiller se almacena en Cloudinary.

Cardinalidades:

- `postulante` 1 ── 0..* `requisito_postulante`
- `postulante` 1 ── 0..* `documento_postulante`

### 6.3 Postulante, pago y alumno

- El postulante debe cumplir requisitos antes del pago.
- El postulante debe pagar mediante Stripe.
- El administrador valida el pago.
- Después de validar requisitos y pago, el administrador le da acceso como alumno.

Cardinalidades:

- `postulante` 1 ── 0..* `pago`
- `postulante` 1 ── 0..1 `alumno`

### 6.4 Alumno y código automático

- El alumno tiene un código automático.
- El código se genera con año actual + gestión + cédula de identidad.

Ejemplo:

```text
Año: 2026
Gestión: 1
CI: 13541539
Código: 2026113541539
```

Cardinalidad:

- `alumno` 1 ── 1 `usuario`

### 6.5 Gestión académica

- Una gestión académica pertenece a un año y a un semestre.
- Cada año tiene gestión 1 y gestión 2.
- La gestión se relaciona con postulaciones, grupos, cupos, exámenes, horarios y reportes.

Cardinalidades:

- `gestion_academica` 1 ── 0..* `postulacion`
- `gestion_academica` 1 ── 0..* `grupo`
- `gestion_academica` 1 ── 0..* `cupo_carrera`
- `gestion_academica` 1 ── 0..* `examen`

### 6.6 Carreras, cupos y postulaciones

- Cada alumno/postulante debe elegir dos carreras.
- Primera opción obligatoria.
- Segunda opción obligatoria.
- Cada carrera tiene cupos por gestión.
- La asignación se prioriza por mayor nota.
- Si la primera opción está llena, se intenta la segunda opción.
- Si ambas están llenas, se asigna a la carrera con menos personas.

Cardinalidades:

- `carrera` 1 ── 0..* `cupo_carrera`
- `gestion_academica` 1 ── 0..* `cupo_carrera`
- `postulante` 1 ── 0..* `postulacion`
- `postulacion` * ── 1 `carrera` como primera opción
- `postulacion` * ── 1 `carrera` como segunda opción
- `postulacion` * ── 0..1 `carrera` como carrera asignada

### 6.7 Grupos

- Cada grupo admite máximo 70 estudiantes.
- El sistema debe calcular automáticamente la cantidad de grupos necesarios.

Cardinalidades:

- `gestion_academica` 1 ── 0..* `grupo`
- `grupo` 1 ── 0..* `grupo_alumno`
- `alumno` 1 ── 0..* `grupo_alumno`

### 6.8 Docentes, materias y grupos

- Un docente puede ser asignado de 1 a 4 grupos.
- Un docente puede dar de 1 a 4 materias como máximo.
- El administrador asigna las materias y grupos al docente.

Cardinalidades:

- `docente` 1 ── 0..* `docente_materia_grupo`
- `materia` 1 ── 0..* `docente_materia_grupo`
- `grupo` 1 ── 0..* `docente_materia_grupo`

Reglas:

- Validar que el docente no supere 4 grupos asignados.
- Validar que el docente no supere 4 materias asignadas.

### 6.9 Horarios, aulas, periodos y turnos

- El administrador define días, turnos y periodos.
- Cada periodo dura 45 minutos.
- El aula solo maneja ubicación.

Cardinalidades:

- `dia` 1 ── 0..* `horario_clase`
- `turno` 1 ── 0..* `horario_clase`
- `periodo` 1 ── 0..* `horario_clase`
- `aula` 1 ── 0..* `horario_clase`
- `grupo` 1 ── 0..* `horario_clase`
- `materia` 1 ── 0..* `horario_clase`
- `docente` 1 ── 0..* `horario_clase`

### 6.10 Asistencia docente

- El docente marca entrada.
- El docente marca salida o finalización de clase.
- La asistencia depende del horario.
- Tiene hasta 30 minutos después del inicio de clase.
- Luego de 30 minutos se marca retraso.
- Pasado el horario de clase se marca falta automática.

Cardinalidades:

- `docente` 1 ── 0..* `asistencia_docente`
- `horario_clase` 1 ── 0..* `asistencia_docente`

### 6.11 Asistencia alumno

- El alumno puede marcar su asistencia.
- El docente puede tomar asistencia a sus alumnos.
- La asistencia depende del horario.
- Tiene hasta 30 minutos después del inicio de clase.
- Luego de 30 minutos se marca retraso.
- Pasado el horario de clase se marca falta automática.

Cardinalidades:

- `alumno` 1 ── 0..* `asistencia_alumno`
- `horario_clase` 1 ── 0..* `asistencia_alumno`
- `docente` 1 ── 0..* `asistencia_alumno` cuando el docente registra asistencia de sus alumnos

### 6.12 Exámenes

- El administrador crea el examen.
- El administrador carga preguntas.
- El administrador habilita el examen.
- El alumno solo puede dar examen si está habilitado.
- Existen 3 exámenes/parciales por gestión.
- Las preguntas son de selección múltiple.
- Materias: Física, Matemáticas, Computación e Inglés.
- Porcentajes ejemplo: Física 25%, Matemáticas 30%, Computación 30%, Inglés 15%.
- La suma de porcentajes debe ser 100%.

Cardinalidades:

- `gestion_academica` 1 ── 0..* `examen`
- `examen` 1 ── 1..* `examen_materia`
- `materia` 1 ── 0..* `examen_materia`
- `examen` 1 ── 0..* `pregunta`
- `pregunta` 1 ── 2..* `opcion_respuesta`
- `alumno` 1 ── 0..* `respuesta_alumno`
- `pregunta` 1 ── 0..* `respuesta_alumno`
- `opcion_respuesta` 1 ── 0..* `respuesta_alumno`

### 6.13 Notas y promedio final

- Las notas van de 0 a 100.
- El alumno tiene 3 parciales.
- El promedio final es la suma de los 3 parciales dividida entre 3.
- Aprobado si promedio >= 60.
- Reprobado si promedio < 60.

Cardinalidades:

- `alumno` 1 ── 0..* `nota_parcial`
- `examen` 1 ── 0..* `nota_parcial`
- `alumno` 1 ── 0..1 `promedio_final` por gestión

### 6.14 Reportes

- El administrador genera reportes.
- Los reportes pueden exportarse en PDF o Excel.
- Los reportes pueden generarse mediante comando de voz interpretado por frontend y procesado por backend.

Cardinalidades:

- `usuario` 1 ── 0..* `reporte_generado`

### 6.15 Carga masiva

- El sistema permite carga masiva desde Excel/CSV.
- La administración de la facultad puede entregar datos por gestión académica.
- La carga se realiza desde la app web.

Cardinalidades:

- `usuario` 1 ── 0..* `carga_masiva`
- `carga_masiva` 1 ── 0..* `detalle_carga_masiva`

---

# 7. Fases de desarrollo del backend

## Fase 0: Planificación técnica del backend

### Objetivo

Definir cómo se construirá el backend en PHP puro, cómo se conectará con PostgreSQL 16 y cómo se organizará el proyecto antes de programar los módulos.

### Actividades

1. Confirmar que el backend será desarrollado netamente en PHP.
2. Confirmar que no se usará Laravel ni otro framework pesado.
3. Definir estructura del proyecto con carpetas claras.
4. Definir uso de API REST para comunicarse con React.
5. Definir conexión a PostgreSQL mediante PDO.
6. Definir manejo de variables de entorno.
7. Definir sistema de rutas.
8. Definir controladores.
9. Definir modelos.
10. Definir servicios.
11. Definir middlewares.
12. Definir validadores.
13. Definir respuestas JSON estándar.
14. Definir manejo de errores.
15. Definir formato de autenticación.
16. Definir integración con Firebase Authentication.
17. Definir integración con Stripe.
18. Definir integración con Cloudinary.
19. Definir cómo se generarán reportes PDF y Excel.
20. Definir cómo se recibirá texto desde comandos de voz.

### Estructura recomendada del proyecto

```text
backend/
│
├── public/
│   └── index.php
│
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Services/
│   ├── Middlewares/
│   ├── Validators/
│   ├── Helpers/
│   └── Routes/
│
├── config/
│   ├── database.php
│   ├── app.php
│   ├── stripe.php
│   ├── cloudinary.php
│   └── firebase.php
│
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── scripts/
│
├── storage/
│   ├── logs/
│   ├── reports/
│   └── temp/
│
├── vendor/
│
├── .env
├── composer.json
└── README.md
```

### Resultado esperado

Al finalizar esta fase debe existir una planificación clara del backend, su estructura de carpetas y la forma en que se implementará cada módulo.

---

## Fase 1: Preparación del entorno local

### Objetivo

Preparar el entorno de desarrollo local para PHP, Apache, PostgreSQL 16 y pgAdmin 4.

### Actividades

1. Instalar o verificar XAMPP.
2. Verificar PHP 8.2.12 CLI.
3. Verificar Apache activo.
4. Instalar PostgreSQL 16.
5. Instalar pgAdmin 4.
6. Crear la base de datos desde pgAdmin 4.
7. Verificar que PHP tenga habilitado el driver de PostgreSQL.
8. Habilitar extensiones necesarias en PHP.
9. Crear archivo `.env` para credenciales.
10. Probar conexión inicial desde PHP hacia PostgreSQL.

### Extensiones PHP necesarias

```text
pdo_pgsql
pgsql
openssl
mbstring
json
curl
fileinfo
```

### Variables de entorno iniciales

```env
APP_NAME=CUP_FICCT
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/cup-ficct/backend/public

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cup_ficct
DB_USERNAME=postgres
DB_PASSWORD=tu_password

STRIPE_SECRET_KEY=
STRIPE_WEBHOOK_SECRET=

CLOUDINARY_CLOUD_NAME=
CLOUDINARY_API_KEY=
CLOUDINARY_API_SECRET=

FIREBASE_PROJECT_ID=
```

### Resultado esperado

El entorno debe permitir ejecutar PHP con Apache y conectarse correctamente a PostgreSQL 16.

---

## Fase 2: Creación de la base de datos en PostgreSQL 16

### Objetivo

Crear la base de datos completa en PostgreSQL 16 usando pgAdmin 4 y scripts SQL.

### Actividades

1. Crear base de datos `cup_ficct`.
2. Crear el esquema público o esquema propio del proyecto.
3. Crear tablas de seguridad.
4. Crear tablas de personas y usuarios.
5. Crear tablas de postulantes.
6. Crear tablas de pagos.
7. Crear tablas académicas.
8. Crear tablas de horarios.
9. Crear tablas de asistencia.
10. Crear tablas de exámenes.
11. Crear tablas de notas.
12. Crear tablas de reportes.
13. Crear tablas de carga masiva.
14. Crear llaves primarias.
15. Crear llaves foráneas.
16. Crear restricciones `CHECK`.
17. Crear restricciones `UNIQUE`.
18. Crear índices para búsquedas frecuentes.
19. Insertar roles base: administrador, docente y alumno.
20. Insertar materias base: Física, Matemáticas, Computación e Inglés.

### Tablas obligatorias a crear

```text
rol
persona
usuario
administrador
docente
alumno
postulante
requisito_postulante
documento_postulante
pago
gestion_academica
carrera
cupo_carrera
postulacion
materia
grupo
aula
dia
turno
periodo
horario_clase
docente_materia_grupo
grupo_alumno
asistencia_docente
asistencia_alumno
examen
examen_materia
pregunta
opcion_respuesta
respuesta_alumno
nota_parcial
promedio_final
reporte_generado
carga_masiva
detalle_carga_masiva
```

### Reglas obligatorias en base de datos

1. No permitir CI duplicado en `persona`.
2. No permitir email duplicado si se define como único.
3. Solo permitir roles: administrador, docente y alumno.
4. La gestión solo puede ser 1 o 2.
5. Cada grupo debe tener cupo máximo de 70.
6. Cada periodo debe durar 45 minutos.
7. Las notas deben estar entre 0 y 100.
8. El estado final debe ser aprobado o reprobado.
9. Los porcentajes de materias en examen deben sumar 100 desde lógica de backend.
10. El docente no debe superar 4 grupos asignados.
11. El docente no debe superar 4 materias asignadas.
12. El alumno debe tener primera y segunda opción de carrera obligatorias.
13. No se debe dar acceso como alumno sin pago validado.

### Resultado esperado

Base de datos creada en PostgreSQL 16 y visible desde pgAdmin 4.

---

## Fase 3: Configuración base del backend PHP

### Objetivo

Crear la estructura inicial del backend en PHP puro para manejar rutas, controladores, conexión a base de datos y respuestas JSON.

### Actividades

1. Crear archivo `public/index.php`.
2. Crear sistema básico de rutas.
3. Crear clase de conexión a PostgreSQL.
4. Crear helper para respuestas JSON.
5. Crear helper para manejo de errores.
6. Crear configuración global de CORS.
7. Permitir comunicación con frontend React.
8. Crear middleware base para autenticación.
9. Crear middleware base para roles.
10. Crear manejo de excepciones.

### Respuesta JSON estándar

```json
{
  "ok": true,
  "mensaje": "Operación realizada correctamente",
  "datos": {}
}
```

### Respuesta JSON de error

```json
{
  "ok": false,
  "mensaje": "Descripción del error",
  "errores": {}
}
```

### Resultado esperado

Backend inicial funcionando con rutas básicas y conexión estable a PostgreSQL.

---

## Fase 4: Módulo de autenticación y sesiones

### Objetivo

Implementar el inicio de sesión, cierre de sesión, control de usuarios y validación de roles.

### Usuarios que pueden iniciar sesión

- Administrador.
- Docente.
- Alumno.

### Actividades

1. Crear endpoint de login.
2. Crear endpoint de logout.
3. Crear endpoint para obtener perfil del usuario autenticado.
4. Validar usuario obligatorio.
5. Validar contraseña obligatoria.
6. Validar código de alumno cuando corresponda.
7. Encriptar contraseñas con `password_hash`.
8. Validar contraseñas con `password_verify`.
9. Generar token de sesión o JWT.
10. Validar sesiones activas.
11. Crear middleware de autenticación.
12. Crear middleware de autorización por rol.
13. Bloquear rutas según rol.

### Endpoints sugeridos

```text
POST /api/auth/login
POST /api/auth/logout
GET  /api/auth/perfil
POST /api/auth/firebase
```

### Integración Firebase Authentication

El frontend puede iniciar sesión con Google usando Firebase Authentication. Luego enviará el token al backend.

El backend debe:

1. Recibir token de Firebase.
2. Verificar que el token sea válido.
3. Validar que el correo corresponda a un usuario permitido.
4. Crear o actualizar datos mínimos si corresponde.
5. Devolver token interno del sistema.

### Resultado esperado

Los usuarios pueden iniciar sesión de forma segura y el backend controla permisos según rol.

---

## Fase 5: Módulo de usuarios, roles y administradores

### Objetivo

Permitir que el administrador gestione usuarios del sistema, especialmente otros administradores, respetando que solo existen tres roles.

### Actividades

1. Crear roles base.
2. Crear usuario administrador inicial.
3. Crear endpoint para registrar administradores.
4. Crear endpoint para listar usuarios.
5. Crear endpoint para activar o desactivar usuarios.
6. Crear endpoint para cambiar contraseña.
7. Crear endpoint para actualizar datos básicos.
8. Validar que solo un administrador pueda crear administradores.
9. Validar que no se creen roles adicionales.

### Endpoints sugeridos

```text
GET    /api/roles
GET    /api/usuarios
POST   /api/usuarios/administradores
GET    /api/usuarios/{id}
PUT    /api/usuarios/{id}
PATCH  /api/usuarios/{id}/estado
```

### Resultado esperado

El backend permite gestionar usuarios y administradores sin crear roles fuera del alcance.

---

## Fase 6: Módulo de postulantes

### Objetivo

Implementar el registro, edición, eliminación, búsqueda y listado de postulantes.

### Datos del postulante

- Cédula de identidad.
- Nombres.
- Apellido paterno.
- Apellido materno.
- Fecha de nacimiento.
- Sexo.
- Dirección.
- Teléfono.
- Correo electrónico.
- Colegio de procedencia.
- Ciudad.
- Primera opción de carrera.
- Segunda opción de carrera.
- Imagen del título de bachiller.

### Actividades

1. Crear endpoint para registrar postulante.
2. Crear endpoint para modificar postulante.
3. Crear endpoint para eliminar postulante.
4. Crear endpoint para buscar postulante.
5. Crear endpoint para listar postulantes.
6. Validar campos obligatorios.
7. Validar CI único.
8. Validar correo electrónico.
9. Validar primera opción de carrera obligatoria.
10. Validar segunda opción de carrera obligatoria.
11. Validar que primera y segunda opción sean carreras existentes.
12. Registrar estado inicial del postulante.
13. Registrar usuario creador si corresponde.

### Estados sugeridos del postulante

```text
registrado
requisitos_pendientes
requisitos_aprobados
pago_pendiente
pago_validado
convertido_alumno
rechazado
```

Estos estados deben usarse solo para controlar el flujo del proceso del postulante.

### Endpoints sugeridos

```text
GET    /api/postulantes
POST   /api/postulantes
GET    /api/postulantes/{id}
PUT    /api/postulantes/{id}
DELETE /api/postulantes/{id}
GET    /api/postulantes/buscar?ci=...
```

### Resultado esperado

El backend permite gestionar postulantes y validar correctamente sus datos principales.

---

## Fase 7: Módulo de requisitos y documentos del postulante

### Objetivo

Registrar y validar los requisitos del postulante, incluyendo la imagen del título de bachiller en Cloudinary.

### Actividades

1. Crear endpoint para subir imagen del título de bachiller.
2. Integrar Cloudinary.
3. Guardar URL segura de Cloudinary.
4. Guardar `public_id` de Cloudinary si corresponde.
5. Registrar documento en `documento_postulante`.
6. Registrar cumplimiento de requisito en `requisito_postulante`.
7. Permitir que el administrador apruebe o rechace requisitos.
8. Evitar que un postulante pase a pago si no cumple requisitos.

### Endpoints sugeridos

```text
POST  /api/postulantes/{id}/documentos
GET   /api/postulantes/{id}/documentos
PATCH /api/postulantes/{id}/requisitos/validar
```

### Reglas

1. El título de bachiller debe ser imagen.
2. La imagen debe guardarse en Cloudinary.
3. El backend solo debe guardar la URL y datos de referencia.
4. El administrador valida los requisitos.
5. Sin requisitos aprobados no se habilita pago.

### Resultado esperado

El postulante puede subir su documentación y el administrador puede validar requisitos.

---

## Fase 8: Módulo de pagos con Stripe

### Objetivo

Implementar el flujo de pago obligatorio mediante Stripe.

### Actividades

1. Configurar llaves de Stripe en `.env`.
2. Crear endpoint para iniciar pago.
3. Crear sesión de pago en Stripe.
4. Guardar intento de pago en tabla `pago`.
5. Crear endpoint webhook para recibir confirmación de Stripe.
6. Validar firma del webhook.
7. Actualizar estado del pago.
8. Notificar o dejar disponible para el administrador la confirmación del pago.
9. Permitir que el administrador valide el pago.
10. Impedir conversión a alumno si el pago no está validado.

### Estados sugeridos del pago

```text
pendiente
pagado
fallido
cancelado
validado_admin
```

### Endpoints sugeridos

```text
POST /api/pagos/stripe/crear-sesion
POST /api/pagos/stripe/webhook
GET  /api/pagos/postulante/{id}
PATCH /api/pagos/{id}/validar-admin
```

### Reglas

1. El pago se realiza solo si el postulante cumple requisitos.
2. Stripe confirma el pago.
3. El administrador valida o revisa el pago.
4. Solo con pago validado el postulante puede convertirse en alumno.

### Resultado esperado

El backend registra pagos mediante Stripe y controla que solo los postulantes con pago validado puedan recibir acceso como alumnos.

---

## Fase 9: Conversión de postulante a alumno y generación de código

### Objetivo

Permitir que el administrador convierta a un postulante en alumno después de validar requisitos y pago.

### Actividades

1. Crear endpoint para convertir postulante a alumno.
2. Validar requisitos aprobados.
3. Validar pago mediante Stripe.
4. Validar que el pago esté validado por administrador.
5. Obtener año actual.
6. Obtener gestión actual.
7. Obtener CI del postulante.
8. Generar código automático.
9. Crear registro en `alumno`.
10. Crear usuario para alumno.
11. Asociar usuario con rol alumno.
12. Guardar código generado.
13. Evitar duplicidad de código.
14. Actualizar estado del postulante a convertido_alumno.

### Formato del código

```text
AÑO + GESTIÓN + CÉDULA DE IDENTIDAD
```

Ejemplo:

```text
2026 + 1 + 13541539 = 2026113541539
```

### Reglas

1. El año se genera desde el año actual.
2. La gestión solo puede ser 1 o 2.
3. Gestión 1 corresponde al primer semestre.
4. Gestión 2 corresponde al segundo semestre.
5. El código se genera automáticamente.
6. El alumno inicia sesión con ese código.

### Endpoint sugerido

```text
POST /api/postulantes/{id}/convertir-alumno
```

### Resultado esperado

El postulante con requisitos aprobados y pago validado se convierte en alumno con usuario y código automático.

---

## Fase 10: Módulo de gestión académica, carreras y cupos

### Objetivo

Implementar la gestión académica, carreras y cupos por carrera.

### Actividades

1. Crear gestiones académicas.
2. Validar que la gestión sea 1 o 2.
3. Crear carreras.
4. Editar carreras.
5. Listar carreras.
6. Crear cupos por carrera y gestión.
7. Editar cupos.
8. Consultar cupos disponibles.
9. Consultar cupos ocupados.
10. Relacionar postulaciones con primera y segunda opción.
11. Implementar asignación por mayor nota.
12. Implementar asignación a primera opción.
13. Implementar asignación a segunda opción si la primera está llena.
14. Implementar asignación a la carrera con menos personas si ambas están llenas.

### Endpoints sugeridos

```text
GET    /api/gestiones
POST   /api/gestiones
GET    /api/carreras
POST   /api/carreras
PUT    /api/carreras/{id}
GET    /api/cupos
POST   /api/cupos
PUT    /api/cupos/{id}
POST   /api/admisiones/asignar-carreras
```

### Reglas

1. Cada año tiene dos gestiones.
2. Cada carrera tiene cupos por gestión.
3. Los alumnos deben tener dos opciones de carrera.
4. Se prioriza siempre por mayor nota.
5. Si primera opción está llena, se intenta segunda opción.
6. Si ambas están llenas, se asigna a la carrera con menos personas.

### Resultado esperado

El backend gestiona carreras, cupos y asignación de alumnos aprobados según reglas definidas.

---

## Fase 11: Módulo de docentes

### Objetivo

Gestionar docentes y sus requisitos de contratación.

### Datos del docente

- Nombre.
- Apellido paterno.
- Apellido materno.
- Cédula de identidad.
- Celular.
- Correo.
- Profesional en el área.
- Maestría.
- Diplomado en educación superior.

### Actividades

1. Crear docente.
2. Editar docente.
3. Eliminar o desactivar docente.
4. Listar docentes.
5. Buscar docente por CI.
6. Validar CI único.
7. Validar correo.
8. Registrar requisitos académicos.
9. Validar si cumple profesional en el área.
10. Validar si tiene maestría.
11. Validar si tiene diplomado en educación superior.
12. Crear usuario docente.
13. Asociar rol docente.

### Endpoints sugeridos

```text
GET    /api/docentes
POST   /api/docentes
GET    /api/docentes/{id}
PUT    /api/docentes/{id}
DELETE /api/docentes/{id}
```

### Reglas

1. El docente debe ser profesional en el área.
2. El docente debe tener maestría.
3. El docente debe tener diplomado en educación superior.
4. El docente puede ser asignado de 1 a 4 grupos.
5. El docente puede dar de 1 a 4 materias como máximo.

### Resultado esperado

El backend permite gestionar docentes y validar sus requisitos de contratación.

---

## Fase 12: Módulo de materias, grupos y aulas

### Objetivo

Implementar la administración de materias, grupos y aulas.

### Materias obligatorias

- Física.
- Matemáticas.
- Computación.
- Inglés.

### Grupos

- Cada grupo admite máximo 70 estudiantes.
- La cantidad de grupos debe calcularse automáticamente según inscritos.

### Aulas

El aula solo tendrá ubicación.

Ejemplo:

```text
Módulo 236, Aula 11
```

### Actividades

1. Crear materias base.
2. Listar materias.
3. Crear grupos.
4. Editar grupos.
5. Listar grupos.
6. Calcular cantidad de grupos necesarios.
7. Asignar alumnos a grupos.
8. Validar cupo máximo de 70 por grupo.
9. Crear aulas.
10. Editar aulas.
11. Listar aulas.

### Endpoints sugeridos

```text
GET    /api/materias
POST   /api/grupos
GET    /api/grupos
GET    /api/grupos/{id}/alumnos
POST   /api/grupos/asignar-alumnos
GET    /api/aulas
POST   /api/aulas
PUT    /api/aulas/{id}
```

### Fórmula de grupos

```text
Cantidad de grupos = techo(total de inscritos / 70)
```

### Resultado esperado

El backend administra materias, grupos y aulas respetando la regla de máximo 70 alumnos por grupo.

---

## Fase 13: Módulo de horarios, días, turnos y periodos

### Objetivo

Permitir que el administrador defina horarios completos para docentes, alumnos, materias, grupos y aulas.

### Actividades

1. Crear días.
2. Crear turnos.
3. Crear periodos.
4. Validar que cada periodo dure 45 minutos.
5. Crear horarios de clase.
6. Relacionar horario con grupo.
7. Relacionar horario con materia.
8. Relacionar horario con docente.
9. Relacionar horario con aula.
10. Validar que no existan choques de horario para docente.
11. Validar que no existan choques de horario para grupo.
12. Validar que no existan choques de horario para aula.

### Endpoints sugeridos

```text
GET    /api/dias
POST   /api/turnos
GET    /api/turnos
POST   /api/periodos
GET    /api/periodos
POST   /api/horarios
GET    /api/horarios
GET    /api/horarios/docente/{id}
GET    /api/horarios/alumno/{id}
```

### Reglas

1. El administrador define días.
2. El administrador define turnos.
3. El administrador define periodos.
4. Cada periodo dura 45 minutos.
5. Los horarios controlan asistencia docente y alumno.

### Resultado esperado

El backend permite crear y consultar horarios necesarios para asistencia, grupos, docentes y alumnos.

---

## Fase 14: Asignación de docentes a materias y grupos

### Objetivo

Asignar docentes a materias y grupos, respetando el límite de grupos y materias.

### Actividades

1. Crear asignación docente-materia-grupo.
2. Validar que el docente exista.
3. Validar que la materia exista.
4. Validar que el grupo exista.
5. Validar que el docente no supere 4 grupos.
6. Validar que el docente no supere 4 materias.
7. Listar asignaciones por docente.
8. Listar asignaciones por grupo.
9. Listar asignaciones por materia.

### Endpoints sugeridos

```text
POST   /api/asignaciones/docente-materia-grupo
GET    /api/asignaciones/docente/{id}
GET    /api/asignaciones/grupo/{id}
DELETE /api/asignaciones/{id}
```

### Resultado esperado

El backend asigna docentes a grupos y materias respetando las reglas definidas.

---

## Fase 15: Módulo de asistencia docente

### Objetivo

Permitir que el docente marque su asistencia de entrada y salida de acuerdo con el horario definido por el administrador.

### Actividades

1. Crear endpoint para marcar entrada docente.
2. Crear endpoint para marcar salida docente.
3. Validar que el docente esté autenticado.
4. Validar que exista horario activo para ese docente.
5. Validar hora de inicio de clase.
6. Validar margen máximo de 30 minutos.
7. Marcar asistencia a tiempo.
8. Marcar retraso si corresponde.
9. Marcar falta automática pasado el horario de clase.
10. Permitir al administrador visualizar asistencias docentes.
11. Permitir filtros por docente, fecha, grupo, materia y estado.

### Estados sugeridos de asistencia docente

```text
presente
retraso
falta
salida_registrada
```

### Endpoints sugeridos

```text
POST /api/asistencia-docente/marcar-entrada
POST /api/asistencia-docente/marcar-salida
GET  /api/asistencia-docente
GET  /api/asistencia-docente/docente/{id}
```

### Reglas

1. El docente solo puede marcar asistencia si tiene clase según horario.
2. Puede marcar máximo 30 minutos después de iniciar la clase.
3. Luego de 30 minutos se marca como retraso.
4. Pasado el horario de la clase se marca falta automática.
5. Solo el administrador ve la asistencia de todos los docentes.

### Resultado esperado

El backend controla la asistencia docente de forma automática según horario.

---

## Fase 16: Módulo de asistencia alumno

### Objetivo

Permitir que el alumno marque su asistencia y que el docente pueda tomar asistencia a sus alumnos.

### Actividades

1. Crear endpoint para que el alumno marque asistencia.
2. Crear endpoint para que el docente tome asistencia a sus alumnos.
3. Validar que el alumno pertenezca al grupo.
4. Validar que el docente esté asignado al grupo y materia.
5. Validar horario activo.
6. Aplicar margen de 30 minutos.
7. Marcar asistencia a tiempo.
8. Marcar retraso.
9. Marcar falta automática pasado el horario.
10. Permitir al alumno ver sus asistencias.
11. Permitir al docente ver asistencia de sus alumnos.
12. Permitir al administrador ver toda la asistencia de alumnos.

### Estados sugeridos de asistencia alumno

```text
presente
retraso
falta
justificado
```

### Endpoints sugeridos

```text
POST /api/asistencia-alumno/marcar
POST /api/asistencia-alumno/docente/registrar
GET  /api/asistencia-alumno/mis-asistencias
GET  /api/asistencia-alumno/docente/mis-alumnos
GET  /api/asistencia-alumno
```

### Reglas

1. La asistencia del alumno depende del horario.
2. El alumno puede marcar asistencia.
3. El docente puede tomar asistencia a sus alumnos.
4. Solo puede registrarse dentro del rango permitido.
5. Después de 30 minutos será retraso.
6. Pasado el horario será falta automática.
7. El administrador ve todo.
8. El docente solo ve sus alumnos.
9. El alumno solo ve su propia asistencia.

### Resultado esperado

El backend registra y controla asistencia de alumnos según horario y permisos.

---

## Fase 17: Módulo de exámenes

### Objetivo

Permitir que el administrador cree exámenes, cargue preguntas de selección múltiple y habilite el examen para los alumnos.

### Actividades

1. Crear examen.
2. Asociar examen a gestión académica.
3. Definir número de parcial: 1, 2 o 3.
4. Asociar materias al examen.
5. Definir porcentajes por materia.
6. Validar que los porcentajes sumen 100.
7. Crear preguntas.
8. Asociar preguntas a materia.
9. Crear opciones de respuesta.
10. Marcar respuesta correcta.
11. Habilitar examen.
12. Deshabilitar examen.
13. Permitir que el alumno vea examen solo si está habilitado.
14. Impedir que el alumno rinda más de una vez el mismo examen si esa regla se aplica en la lógica.

### Materias y porcentajes definidos como ejemplo

```text
Física: 25%
Matemáticas: 30%
Computación: 30%
Inglés: 15%
Total: 100%
```

### Endpoints sugeridos

```text
POST  /api/examenes
GET   /api/examenes
GET   /api/examenes/{id}
PATCH /api/examenes/{id}/habilitar
PATCH /api/examenes/{id}/deshabilitar
POST  /api/examenes/{id}/materias
POST  /api/examenes/{id}/preguntas
POST  /api/preguntas/{id}/opciones
```

### Reglas

1. Solo existen 3 exámenes por estudiante en una gestión.
2. Las preguntas son de selección múltiple.
3. El administrador carga las preguntas.
4. El administrador habilita el examen.
5. El alumno solo rinde si el examen está habilitado.
6. Las notas deben estar entre 0 y 100.

### Resultado esperado

El backend permite crear, configurar y habilitar exámenes para los alumnos.

---

## Fase 18: Módulo de resolución de examen por alumno

### Objetivo

Permitir que el alumno rinda un examen habilitado y guarde sus respuestas.

### Actividades

1. Validar que el alumno esté autenticado.
2. Validar que el examen esté habilitado.
3. Validar que el alumno pertenezca a la gestión correspondiente.
4. Mostrar preguntas del examen.
5. Recibir respuestas del alumno.
6. Validar que cada pregunta tenga una respuesta seleccionada si se exige respuesta obligatoria.
7. Guardar respuestas.
8. Calcular puntaje por materia.
9. Calcular nota del parcial según porcentajes.
10. Registrar nota en `nota_parcial`.

### Endpoints sugeridos

```text
GET  /api/alumno/examenes/habilitados
GET  /api/alumno/examenes/{id}
POST /api/alumno/examenes/{id}/responder
GET  /api/alumno/examenes/{id}/resultado
```

### Resultado esperado

El alumno puede rendir exámenes habilitados y el backend guarda respuestas y calcula nota.

---

## Fase 19: Módulo de notas, promedios y estado final

### Objetivo

Calcular notas parciales, promedio final y estado final del alumno.

### Actividades

1. Registrar nota del parcial 1.
2. Registrar nota del parcial 2.
3. Registrar nota del parcial 3.
4. Validar notas entre 0 y 100.
5. Calcular promedio final.
6. Determinar estado aprobado/reprobado.
7. Guardar promedio final.
8. Mostrar promedio al administrador.
9. Mostrar estado final al administrador.
10. Permitir consulta individual del alumno si corresponde.

### Fórmula del promedio final

```text
Promedio final = (Parcial 1 + Parcial 2 + Parcial 3) / 3
```

### Estado final

```text
APROBADO  -> promedio >= 60
REPROBADO -> promedio < 60
```

### Endpoints sugeridos

```text
GET  /api/notas/alumno/{id}
GET  /api/promedios
POST /api/promedios/calcular
GET  /api/promedios/aprobados
GET  /api/promedios/reprobados
```

### Resultado esperado

El backend calcula el promedio final y define automáticamente si el alumno aprueba o reprueba.

---

## Fase 20: Asignación final de carrera por nota y cupo

### Objetivo

Asignar carrera a los alumnos aprobados según nota y disponibilidad de cupos.

### Actividades

1. Obtener alumnos aprobados.
2. Ordenar alumnos por promedio final de mayor a menor.
3. Revisar primera opción de carrera.
4. Verificar cupo disponible en primera opción.
5. Asignar primera opción si hay cupo.
6. Si no hay cupo, revisar segunda opción.
7. Asignar segunda opción si hay cupo.
8. Si ambas opciones están llenas, buscar carrera con menos personas.
9. Asignar a la carrera con menos personas.
10. Actualizar cupos ocupados.
11. Guardar carrera asignada en la postulación.

### Reglas

1. Siempre se prioriza por mayor nota.
2. Primera opción tiene prioridad.
3. Segunda opción se usa si la primera está llena.
4. Si ambas están llenas, se asigna a la carrera con menos personas.

### Endpoint sugerido

```text
POST /api/admisiones/asignar-carreras
```

### Resultado esperado

El backend asigna carrera final a alumnos aprobados respetando nota y cupos.

---

## Fase 21: Módulo de reportes

### Objetivo

Generar reportes obligatorios del sistema.

### Reportes obligatorios

1. Lista general de postulantes.
2. Postulantes aprobados.
3. Postulantes reprobados.
4. Promedios generales.
5. Cantidad de grupos habilitados.
6. Estadísticas por materia.
7. Docentes por grupos.
8. Grupos con mayor cantidad de aprobados.
9. Asistencia de docentes.
10. Asistencia de alumnos.

### Actividades

1. Crear consultas SQL para cada reporte.
2. Crear endpoints de reportes.
3. Permitir filtros por gestión.
4. Permitir filtros por carrera.
5. Permitir filtros por grupo.
6. Permitir filtros por docente.
7. Permitir filtros por fecha en asistencia.
8. Guardar registro de reporte generado.
9. Validar que solo administrador acceda a reportes generales.

### Endpoints sugeridos

```text
GET /api/reportes/postulantes
GET /api/reportes/aprobados
GET /api/reportes/reprobados
GET /api/reportes/promedios
GET /api/reportes/grupos
GET /api/reportes/estadisticas-materia
GET /api/reportes/docentes-grupos
GET /api/reportes/grupos-mayor-aprobados
GET /api/reportes/asistencia-docentes
GET /api/reportes/asistencia-alumnos
```

### Resultado esperado

El backend genera reportes administrativos completos.

---

## Fase 22: Exportación PDF y Excel

### Objetivo

Permitir que los reportes se exporten en PDF y Excel.

### Actividades

1. Elegir librería PHP para generar PDF.
2. Elegir librería PHP para generar Excel.
3. Crear servicio de exportación PDF.
4. Crear servicio de exportación Excel.
5. Recibir tipo de reporte.
6. Recibir formato seleccionado: PDF o Excel.
7. Generar archivo.
8. Guardar archivo temporalmente en `storage/reports`.
9. Devolver URL o descarga directa.
10. Registrar reporte generado en base de datos.

### Librerías PHP recomendadas

Para PDF:

- Dompdf.

Para Excel:

- PhpSpreadsheet.

Estas librerías se pueden instalar con Composer y permiten simplificar la generación de archivos.

### Endpoints sugeridos

```text
GET /api/reportes/{tipo}/exportar?formato=pdf
GET /api/reportes/{tipo}/exportar?formato=excel
```

### Resultado esperado

El administrador puede exportar reportes en PDF o Excel.

---

## Fase 23: Reportes por comando de voz

### Objetivo

Permitir que el backend procese texto interpretado desde comandos de voz para generar reportes.

### Aclaración técnica

La voz se captura en el frontend usando Web Speech API. El frontend convierte la voz en texto y envía ese texto al backend.

El backend no procesa audio directamente. El backend recibe texto.

### Ejemplo

Texto recibido:

```text
listar alumnos reprobados y aprobados
```

El backend debe interpretar ese texto y responder con el reporte correspondiente o con opciones de reportes.

### Actividades

1. Crear endpoint para recibir texto del comando.
2. Crear lista de comandos permitidos.
3. Asociar comandos con reportes.
4. Procesar texto recibido.
5. Devolver reporte o tipo de reporte detectado.
6. Permitir al administrador elegir PDF o Excel.

### Endpoints sugeridos

```text
POST /api/reportes/comando-voz
GET  /api/reportes/comando-voz/opciones
```

### Comandos iniciales sugeridos

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

### Resultado esperado

El backend puede generar reportes a partir del texto enviado por el frontend después del reconocimiento de voz.

---

## Fase 24: Carga masiva Excel/CSV

### Objetivo

Permitir cargar datos por lotes desde archivos Excel o CSV entregados por la administración de la facultad.

### Actividades

1. Crear endpoint para subir archivo CSV.
2. Crear endpoint para subir archivo Excel.
3. Validar tipo de archivo.
4. Leer filas del archivo.
5. Validar datos obligatorios.
6. Validar duplicados.
7. Registrar carga masiva.
8. Registrar detalle de errores.
9. Insertar registros válidos.
10. Devolver resumen de carga.

### Datos que podrían cargarse

Según el contexto, la carga masiva puede servir para:

- Usuarios.
- Docentes.
- Alumnos.
- Datos entregados por la administración en cada gestión académica.

### Endpoints sugeridos

```text
POST /api/cargas/csv
POST /api/cargas/excel
GET  /api/cargas
GET  /api/cargas/{id}/detalle
```

### Resultado esperado

El backend permite cargar información masiva desde Excel/CSV y registrar errores por fila.

---

## Fase 25: Dashboard administrativo

### Objetivo

Crear endpoints para alimentar el panel administrativo del frontend.

### Indicadores obligatorios

- Total inscritos.
- Total aprobados.
- Total reprobados.
- Total grupos habilitados.

### Indicadores adicionales derivados del contexto

- Total postulantes.
- Total alumnos.
- Total docentes.
- Total pagos pendientes.
- Total pagos validados.
- Total asistencias docentes.
- Total faltas docentes.
- Total asistencias alumnos.
- Total faltas alumnos.
- Cupos ocupados por carrera.
- Cupos disponibles por carrera.

### Endpoints sugeridos

```text
GET /api/dashboard/resumen
GET /api/dashboard/asistencia
GET /api/dashboard/cupos
GET /api/dashboard/examenes
```

### Resultado esperado

El frontend puede mostrar indicadores del sistema usando datos enviados por el backend.

---

## Fase 26: Seguridad del backend

### Objetivo

Proteger el backend, los datos y las rutas del sistema.

### Actividades

1. Usar consultas preparadas PDO.
2. Encriptar contraseñas con `password_hash`.
3. Validar tokens de sesión.
4. Validar roles por ruta.
5. Implementar CORS controlado.
6. Validar archivos subidos.
7. Validar tamaño de archivos.
8. Validar extensiones de imagen.
9. Validar webhook de Stripe.
10. Validar token de Firebase.
11. No exponer claves secretas.
12. Usar `.env`.
13. Manejar errores sin mostrar credenciales.
14. Registrar logs internos.
15. Evitar SQL Injection.
16. Evitar carga de archivos peligrosos.
17. Evitar acceso no autorizado a reportes.

### Resultado esperado

El backend queda protegido con controles básicos y necesarios para una aplicación web real.

---

## Fase 27: Validaciones generales del backend

### Objetivo

Centralizar validaciones para mantener consistencia en todo el sistema.

### Validaciones de persona

- CI obligatorio.
- CI único.
- Nombres obligatorios.
- Apellidos obligatorios.
- Correo válido.
- Teléfono o celular válido.

### Validaciones de postulante

- Primera opción de carrera obligatoria.
- Segunda opción de carrera obligatoria.
- Título de bachiller obligatorio.
- Requisitos aprobados antes del pago.

### Validaciones de pago

- Pago obligatorio.
- Pago asociado a postulante.
- Pago confirmado por Stripe.
- Pago validado por administrador antes de crear alumno.

### Validaciones de alumno

- Código único.
- Código generado automáticamente.
- Usuario con rol alumno.

### Validaciones de docente

- Profesional en el área.
- Maestría.
- Diplomado en educación superior.
- Máximo 4 grupos.
- Máximo 4 materias.

### Validaciones de grupo

- Máximo 70 alumnos.

### Validaciones de horario

- Periodo de 45 minutos.
- No choque de docente.
- No choque de grupo.
- No choque de aula.

### Validaciones de asistencia

- Debe existir horario.
- Máximo 30 minutos después del inicio.
- Después de 30 minutos es retraso.
- Pasado el horario es falta automática.

### Validaciones de examen

- Solo 3 parciales.
- Examen habilitado para que el alumno lo rinda.
- Preguntas de selección múltiple.
- Porcentajes de materias deben sumar 100.
- Notas entre 0 y 100.

### Validaciones de promedio

- Debe calcularse con 3 parciales.
- Aprobado si promedio >= 60.
- Reprobado si promedio < 60.

---

## Fase 28: Pruebas del backend

### Objetivo

Verificar que los módulos funcionan correctamente antes de conectar completamente con el frontend.

### Actividades

1. Probar conexión a PostgreSQL.
2. Probar login de administrador.
3. Probar login de docente.
4. Probar login de alumno con código.
5. Probar creación de postulante.
6. Probar validación de CI duplicado.
7. Probar subida de imagen a Cloudinary.
8. Probar validación de requisitos.
9. Probar creación de sesión de pago Stripe.
10. Probar webhook Stripe.
11. Probar conversión a alumno.
12. Probar generación de código automático.
13. Probar creación de docentes.
14. Probar asignación de docente a grupo y materia.
15. Probar creación de horarios.
16. Probar asistencia docente.
17. Probar asistencia alumno.
18. Probar creación de examen.
19. Probar creación de preguntas.
20. Probar resolución de examen.
21. Probar cálculo de nota.
22. Probar cálculo de promedio.
23. Probar estado aprobado/reprobado.
24. Probar asignación por cupos.
25. Probar reportes.
26. Probar exportación PDF.
27. Probar exportación Excel.
28. Probar carga masiva CSV/Excel.
29. Probar permisos por rol.

### Herramientas sugeridas para pruebas manuales

- Postman.
- Thunder Client en VS Code.
- pgAdmin 4 para verificar datos.

### Resultado esperado

El backend debe responder correctamente antes de integrarse por completo con React.

---

## Fase 29: Documentación técnica del backend

### Objetivo

Documentar el backend para facilitar desarrollo, mantenimiento y defensa del proyecto.

### Actividades

1. Documentar estructura de carpetas.
2. Documentar instalación.
3. Documentar variables de entorno.
4. Documentar conexión PostgreSQL.
5. Documentar endpoints.
6. Documentar roles.
7. Documentar reglas de negocio.
8. Documentar flujo de pagos.
9. Documentar flujo de asistencia.
10. Documentar flujo de exámenes.
11. Documentar reportes.
12. Documentar carga masiva.
13. Documentar despliegue futuro.

### Resultado esperado

Existirá documentación clara para entender cómo se construyó el backend.

---

## Fase 30: Preparación para despliegue futuro en Railway

### Objetivo

Preparar el backend para poder desplegarlo posteriormente en Railway.

### Actividades

1. Revisar variables de entorno.
2. Separar configuración local y producción.
3. Evitar rutas absolutas locales.
4. Preparar archivo de inicio si Railway lo requiere.
5. Verificar conexión con base de datos remota en Clever Cloud.
6. Configurar CORS para frontend desplegado en Vercel.
7. Configurar llaves reales de Stripe.
8. Configurar Cloudinary en producción.
9. Configurar Firebase en producción.
10. Probar endpoints principales.

### Resultado esperado

El backend estará listo para ser desplegado posteriormente, sin cambiar su arquitectura principal.

---

# 8. Orden recomendado de implementación

El orden recomendado para desarrollar el backend es:

1. Preparar entorno PHP, Apache, PostgreSQL y pgAdmin 4.
2. Crear base de datos.
3. Crear conexión PHP con PostgreSQL.
4. Crear estructura API REST.
5. Crear autenticación.
6. Crear roles y usuarios.
7. Crear módulo de postulantes.
8. Crear módulo de documentos y Cloudinary.
9. Crear módulo de pagos con Stripe.
10. Crear conversión de postulante a alumno.
11. Crear gestión académica, carreras y cupos.
12. Crear docentes.
13. Crear materias, grupos y aulas.
14. Crear horarios.
15. Crear asignación docente-materia-grupo.
16. Crear asistencia docente.
17. Crear asistencia alumno.
18. Crear exámenes.
19. Crear resolución de exámenes.
20. Crear notas y promedios.
21. Crear asignación final de carrera.
22. Crear reportes.
23. Crear exportación PDF/Excel.
24. Crear comandos de voz para reportes.
25. Crear carga masiva Excel/CSV.
26. Crear dashboard.
27. Realizar pruebas.
28. Documentar.
29. Preparar despliegue.

---

# 9. Reglas que no deben romperse durante el desarrollo

1. El backend debe ser PHP.
2. La base de datos debe ser PostgreSQL 16.
3. La administración de la base de datos debe hacerse desde pgAdmin 4.
4. Solo existen tres roles: administrador, docente y alumno.
5. El administrador tiene acceso completo.
6. El docente solo tiene acceso a sus funciones permitidas.
7. El alumno solo tiene acceso a su información.
8. El postulante debe cumplir requisitos antes de pagar.
9. El pago debe hacerse mediante Stripe.
10. El administrador valida el pago antes de dar acceso como alumno.
11. El código del alumno se genera automáticamente.
12. El código tiene formato año + gestión + CI.
13. La gestión solo puede ser 1 o 2.
14. Gestión 1 corresponde al primer semestre.
15. Gestión 2 corresponde al segundo semestre.
16. La imagen del título de bachiller se guarda en Cloudinary.
17. La validación con Google/correo se simplifica usando Firebase Authentication.
18. Los comandos de voz se simplifican usando Web Speech API en frontend.
19. El backend recibe texto interpretado desde el comando de voz.
20. Cada grupo admite máximo 70 alumnos.
21. Cada periodo dura 45 minutos.
22. La asistencia es obligatoria para docentes y alumnos.
23. Solo se puede marcar asistencia hasta 30 minutos después de iniciar clase.
24. Después de 30 minutos se marca retraso.
25. Pasado el horario de clase se marca falta automática.
26. El docente puede tomar asistencia a sus alumnos.
27. El administrador puede ver toda la asistencia.
28. El docente solo puede ver la asistencia de sus alumnos.
29. El alumno solo puede ver su asistencia.
30. Los exámenes son de selección múltiple.
31. El administrador crea preguntas.
32. El administrador habilita exámenes.
33. El alumno solo rinde examen si está habilitado.
34. Cada alumno tiene 3 parciales por gestión.
35. Las notas van de 0 a 100.
36. El promedio final es la suma de los 3 parciales dividido entre 3.
37. Aprobado es promedio mayor o igual a 60.
38. Reprobado es promedio menor a 60.
39. Se prioriza admisión por mayor nota.
40. Primero se intenta asignar primera opción de carrera.
41. Si primera opción está llena, se intenta segunda opción.
42. Si ambas están llenas, se asigna a la carrera con menos personas.
43. Los reportes deben exportarse en PDF y Excel.
44. La carga masiva debe aceptar Excel/CSV.
45. No se deben crear módulos fuera del alcance definido.

---

# 10. Resultado final esperado del backend

Al finalizar todas las fases, el backend deberá permitir administrar completamente el proceso de admisión universitaria del CUP para la FICCT.

El sistema deberá cubrir:

- Registro de postulantes.
- Validación de requisitos.
- Carga de imagen del título de bachiller en Cloudinary.
- Pago obligatorio mediante Stripe.
- Validación administrativa del pago.
- Creación de alumnos.
- Generación automática del código del alumno.
- Login de administrador, docente y alumno.
- Gestión de docentes.
- Gestión de carreras.
- Gestión de cupos.
- Gestión de grupos.
- Gestión de aulas.
- Gestión de horarios.
- Gestión de materias.
- Asignación de docentes a materias y grupos.
- Asistencia docente.
- Asistencia alumno.
- Exámenes habilitados por administrador.
- Preguntas de selección múltiple.
- Respuestas de alumnos.
- Cálculo de notas.
- Cálculo de promedio final.
- Estado aprobado/reprobado.
- Asignación final de carrera por nota y cupo.
- Reportes administrativos.
- Exportación PDF.
- Exportación Excel.
- Reportes por comando de voz.
- Carga masiva Excel/CSV.
- Dashboard administrativo.
- Seguridad y control por roles.

---

# 11. Nota final

Este archivo debe usarse como guía para implementar el backend por fases. Cada fase debe completarse y probarse antes de avanzar a la siguiente, porque los módulos dependen entre sí.

El backend debe mantenerse ordenado, comentado y conectado correctamente con PostgreSQL 16. La estructura debe facilitar la conexión con el frontend desarrollado en React con Vite y Tailwind CSS 4.

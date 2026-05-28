# backend_subfases.md

# Plan definitivo por fases y subfases del Backend - Aplicación Web de Admisión Universitaria (CUP) para la FICCT

## 1. Objetivo del archivo

Este archivo define de manera profesional, detallada y progresiva las **fases y subfases definitivas para la creación del backend** de la **Aplicación Web de Admisión Universitaria (CUP) para la FICCT**.

El backend será desarrollado **netamente en PHP y laravel**, conectado obligatoriamente a **PostgreSQL 16**, administrado desde **pgAdmin 4**.

Este archivo se basa en:

- `contexto.md`.
- `base_de_datos.md`.
- `backend_fases.md`.

El objetivo de este documento es que sirva como guía definitiva para construir el backend sin eliminar información, sin cambiar reglas ya definidas y sin inventar funcionalidades fuera del alcance del proyecto.

---

## 2. Alcance obligatorio del backend

El backend debe cubrir todo el proceso de admisión universitaria del CUP para la FICCT, incluyendo:

1. Configuración inicial del proyecto PHP.
2. Conexión con PostgreSQL 16.
3. Creación de la base de datos desde pgAdmin 4.
4. Scripts SQL o migraciones manuales controladas.
5. Autenticación.
6. Control de sesiones.
7. Roles del sistema.
8. Usuarios.
9. Administradores.
10. Postulantes.
11. Validación de requisitos.
12. Documentos del postulante.
13. Integración con Cloudinary.
14. Pagos mediante Stripe.
15. Validación administrativa del pago.
16. Conversión de postulante a alumno.
17. Generación automática del código del alumno.
18. Docentes.
19. Gestión académica.
20. Carreras.
21. Cupos por carrera.
22. Materias.
23. Grupos.
24. Aulas.
25. Días.
26. Turnos.
27. Periodos de 45 minutos.
28. Horarios.
29. Asignación de docentes a grupos y materias.
30. Asignación de alumnos a grupos.
31. Asistencia docente.
32. Asistencia de alumnos.
33. Exámenes.
34. Preguntas de selección múltiple.
35. Opciones de respuesta.
36. Respuestas del alumno.
37. Notas parciales.
38. Promedios finales.
39. Estado final aprobado/reprobado.
40. Asignación de carrera según cupo y nota.
41. Reportes.
42. Exportación PDF.
43. Exportación Excel.
44. Reportes por comando de voz.
45. Carga masiva Excel/CSV.
46. Dashboard administrativo.
47. Seguridad.
48. Validaciones.
49. Pruebas.
50. Documentación técnica.
51. Preparación para despliegue futuro en Railway.

---

## 3. Tecnologías obligatorias

### 3.1 Backend

- PHP 8.2.12 CLI.
- PHP con Laravel netamente obligatorio. (solamente para el backend, el frontend se usara react js)
- Apache para localhost.
- XAMPP como entorno local.
- Arquitectura recomendada: API REST con estructura propia tipo MVC.

### 3.2 Base de datos

- PostgreSQL 16.
- pgAdmin 4 para crear, visualizar y administrar la base de datos.

### 3.3 Conexión PHP - PostgreSQL

- Usar PDO con driver `pgsql`.
- Usar consultas preparadas.
- No concatenar directamente datos del usuario en SQL.
- Manejar errores de conexión sin mostrar credenciales.

### 3.4 Servicios externos

El backend debe integrarse con:

- Stripe para pagos.
- Cloudinary para imagen del título de bachiller.
- Firebase Authentication para validación de token de login con Google/correo.
- Web Speech API desde frontend para comandos de voz; el backend solo recibirá texto interpretado.

### 3.5 Librerías PHP recomendadas

Estas librerías se recomiendan porque simplifican el desarrollo y fueron consideradas en `backend_fases.md`:

- `vlucas/phpdotenv` para variables de entorno.
- `stripe/stripe-php` para Stripe.
- SDK o consumo HTTP de Cloudinary para subir imágenes.
- Firebase Admin SDK o verificación HTTP de tokens para Firebase.
- `dompdf/dompdf` para PDF.
- `phpoffice/phpspreadsheet` para Excel.

Estas librerías no cambian el lenguaje base del backend, porque el backend sigue siendo PHP.

---

## 4. Roles definitivos del sistema

El sistema debe manejar solamente **3 roles**:

1. Administrador.
2. Docente.
3. Alumno.

No se deben crear roles adicionales como autoridad, coordinador u otros.

### 4.1 Administrador

El administrador tiene acceso completo al sistema.

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
- Ver dashboard administrativo.
- Realizar carga masiva Excel/CSV.

### 4.2 Docente

El docente tiene acceso limitado.

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
- Crear administradores.
- Gestionar usuarios generales.
- Validar pagos.
- Gestionar cupos.
- Generar reportes generales administrativos.
- Crear preguntas de examen si no se le asigna explícitamente esa función en el alcance futuro.

### 4.3 Alumno

El alumno tiene acceso limitado.

Puede:

- Iniciar sesión con su código generado automáticamente.
- Ver su perfil.
- Ver sus horarios.
- Marcar su asistencia.
- Ver sus asistencias.
- Dar examen si el administrador habilita el examen.

No puede:

- Ver datos de otros alumnos.
- Ver datos administrativos.
- Ver reportes generales.
- Crear preguntas.
- Modificar notas.
- Cambiar horarios.
- Validar pagos.

---

## 5. Tablas obligatorias que debe respetar el backend

El backend debe trabajar con la base de datos definida en `base_de_datos.md`. Como mínimo debe respetar estas tablas:

### 5.1 Seguridad y usuarios

- `rol`
- `persona`
- `usuario`
- `administrador`
- `docente`
- `alumno`

### 5.2 Postulantes y requisitos

- `postulante`
- `requisito_postulante`
- `documento_postulante`

### 5.3 Pagos y gestión académica

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

## 6. Relaciones y cardinalidades principales que no deben romperse

### 6.1 Persona, usuario y roles

- `persona` 1 ── 0..1 `usuario`
- `rol` 1 ── 0..* `usuario`
- `persona` 1 ── 0..1 `administrador`
- `persona` 1 ── 0..1 `docente`
- `persona` 1 ── 0..1 `alumno`
- `persona` 1 ── 0..1 `postulante`

### 6.2 Postulante, requisitos y documentos

- `postulante` 1 ── 0..* `requisito_postulante`
- `postulante` 1 ── 0..* `documento_postulante`

### 6.3 Postulante, pago y alumno

- `postulante` 1 ── 0..* `pago`
- `postulante` 1 ── 0..1 `alumno`

### 6.4 Alumno y usuario

- `alumno` 1 ── 1 `usuario`

### 6.5 Gestión académica

- `gestion_academica` 1 ── 0..* `postulacion`
- `gestion_academica` 1 ── 0..* `grupo`
- `gestion_academica` 1 ── 0..* `cupo_carrera`
- `gestion_academica` 1 ── 0..* `examen`

### 6.6 Carreras, cupos y postulaciones

- `carrera` 1 ── 0..* `cupo_carrera`
- `gestion_academica` 1 ── 0..* `cupo_carrera`
- `postulante` 1 ── 0..* `postulacion`
- `postulacion` * ── 1 `carrera` como primera opción
- `postulacion` * ── 1 `carrera` como segunda opción
- `postulacion` * ── 0..1 `carrera` como carrera asignada

### 6.7 Grupos

- `gestion_academica` 1 ── 0..* `grupo`
- `grupo` 1 ── 0..* `grupo_alumno`
- `alumno` 1 ── 0..* `grupo_alumno`

### 6.8 Docentes, materias y grupos

- `docente` 1 ── 0..* `docente_materia_grupo`
- `materia` 1 ── 0..* `docente_materia_grupo`
- `grupo` 1 ── 0..* `docente_materia_grupo`

### 6.9 Horarios

- `dia` 1 ── 0..* `horario_clase`
- `turno` 1 ── 0..* `horario_clase`
- `periodo` 1 ── 0..* `horario_clase`
- `aula` 1 ── 0..* `horario_clase`
- `grupo` 1 ── 0..* `horario_clase`
- `materia` 1 ── 0..* `horario_clase`
- `docente` 1 ── 0..* `horario_clase`

### 6.10 Asistencia docente

- `docente` 1 ── 0..* `asistencia_docente`
- `horario_clase` 1 ── 0..* `asistencia_docente`

### 6.11 Asistencia alumno

- `alumno` 1 ── 0..* `asistencia_alumno`
- `horario_clase` 1 ── 0..* `asistencia_alumno`
- `docente` 1 ── 0..* `asistencia_alumno` cuando el docente registra asistencia de sus alumnos

### 6.12 Exámenes

- `gestion_academica` 1 ── 0..* `examen`
- `examen` 1 ── 1..* `examen_materia`
- `materia` 1 ── 0..* `examen_materia`
- `examen` 1 ── 0..* `pregunta`
- `pregunta` 1 ── 2..* `opcion_respuesta`
- `alumno` 1 ── 0..* `respuesta_alumno`
- `pregunta` 1 ── 0..* `respuesta_alumno`
- `opcion_respuesta` 1 ── 0..* `respuesta_alumno`

### 6.13 Notas y promedio

- `alumno` 1 ── 0..* `nota_parcial`
- `examen` 1 ── 0..* `nota_parcial`
- `alumno` 1 ── 0..1 `promedio_final` por gestión

### 6.14 Reportes y carga masiva

- `usuario` 1 ── 0..* `reporte_generado`
- `usuario` 1 ── 0..* `carga_masiva`
- `carga_masiva` 1 ── 0..* `detalle_carga_masiva`

---

# 7. Fases y subfases definitivas del backend

---

# Fase 0: Planificación técnica del backend

## Objetivo

Definir la forma exacta en que se construirá el backend en PHP y laravel, antes de iniciar la programación de módulos.

## Dependencias

- `contexto.md`.
- `base_de_datos.md`.
- `backend_fases.md`.

## Subfase 0.1: Confirmación del alcance técnico

### Tareas

1. Confirmar que el backend será desarrollado netamente en PHP y laravel.
2. Confirmar que se usará PHP 8.2.12 CLI y laravel.
3. Confirmar que se usará Apache desde XAMPP en entorno local.
4. Confirmar que PostgreSQL 16 será la base de datos obligatoria.
5. Confirmar que pgAdmin 4 será la herramienta para administrar la base de datos.
6. Confirmar que no se usará Laravel ni otro framework pesado.
7. Confirmar que el backend funcionará como API REST para comunicarse con React.

### Resultado esperado

Queda definido el entorno técnico obligatorio del backend.

## Subfase 0.2: Definición de arquitectura PHP

### Tareas

1. Definir una estructura propia tipo MVC/API REST.
2. Separar rutas, controladores, modelos, servicios, validadores y middlewares.
3. Definir un punto de entrada único en `public/index.php`.
4. Definir rutas agrupadas por módulo.
5. Definir controladores para recibir peticiones HTTP.
6. Definir modelos para interactuar con tablas.
7. Definir servicios para lógica de negocio.
8. Definir validadores para reglas de entrada.
9. Definir middlewares para autenticación y roles.
10. Definir helpers para respuestas JSON.

### Estructura recomendada

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

La arquitectura queda preparada para que el backend sea mantenible y organizado.

## Subfase 0.3: Definición de estándar de respuestas JSON

### Tareas

1. Definir respuesta estándar para operaciones correctas.
2. Definir respuesta estándar para errores.
3. Definir códigos HTTP correctos.
4. Definir estructura de validaciones fallidas.
5. Definir estructura para respuestas paginadas.

### Respuesta correcta

```json
{
  "ok": true,
  "mensaje": "Operación realizada correctamente",
  "datos": {}
}
```

### Respuesta de error

```json
{
  "ok": false,
  "mensaje": "Descripción del error",
  "errores": {}
}
```

### Resultado esperado

Todas las rutas del backend responderán con el mismo formato.

## Subfase 0.4: Definición de módulos internos

### Módulos a considerar

1. Autenticación.
2. Usuarios.
3. Roles.
4. Administradores.
5. Postulantes.
6. Requisitos.
7. Documentos.
8. Cloudinary.
9. Stripe.
10. Alumnos.
11. Docentes.
12. Gestión académica.
13. Carreras.
14. Cupos.
15. Materias.
16. Grupos.
17. Aulas.
18. Días.
19. Turnos.
20. Periodos.
21. Horarios.
22. Asignaciones docente-materia-grupo.
23. Asistencia docente.
24. Asistencia alumno.
25. Exámenes.
26. Preguntas.
27. Opciones de respuesta.
28. Respuestas del alumno.
29. Notas.
30. Promedios.
31. Admisión por cupos.
32. Reportes.
33. Exportación PDF.
34. Exportación Excel.
35. Comandos de voz.
36. Carga masiva.
37. Dashboard.
38. Seguridad.
39. Logs.
40. Pruebas.

### Resultado esperado

Se mantiene el alcance completo sin omitir módulos definidos.

---

# Fase 1: Preparación del entorno local

## Objetivo

Preparar el entorno local para desarrollar y probar el backend en PHP y laravel con PostgreSQL 16.

## Subfase 1.1: Verificación de XAMPP y Apache

### Tareas

1. Instalar XAMPP si no está instalado.
2. Verificar que Apache se pueda iniciar desde el panel de XAMPP.
3. Verificar que el backend pueda ejecutarse desde Apache.
4. Definir la ruta local del proyecto.
5. Confirmar que el proyecto será accesible desde una URL local.

### Resultado esperado

Apache queda funcionando para servir el backend PHP.

## Subfase 1.2: Verificación de PHP 8.2.12 CLI

### Tareas

1. Ejecutar `php -v`.
2. Confirmar versión PHP 8.2.12 CLI y laravel.
3. Verificar que PHP esté agregado al PATH del sistema.
4. Verificar que PHP pueda ejecutar archivos desde consola.
5. Verificar que Apache esté usando la versión correcta de PHP.

### Resultado esperado

PHP 8.2.12 está disponible para el backend.

## Subfase 1.3: Habilitación de extensiones PHP necesarias

### Extensiones requeridas

```text
pdo_pgsql
pgsql
openssl
mbstring
json
curl
fileinfo
```

### Tareas

1. Abrir el archivo `php.ini` usado por PHP/Apache.
2. Habilitar `pdo_pgsql`.
3. Habilitar `pgsql`.
4. Habilitar `openssl`.
5. Habilitar `mbstring`.
6. Habilitar `curl`.
7. Habilitar `fileinfo`.
8. Reiniciar Apache.
9. Verificar extensiones usando `php -m`.

### Resultado esperado

PHP queda listo para conectarse a PostgreSQL, consumir servicios externos y manejar archivos.

## Subfase 1.4: Instalación y verificación de PostgreSQL 16

### Tareas

1. Instalar PostgreSQL 16.
2. Confirmar el puerto local `5432`.
3. Definir usuario administrador de base de datos.
4. Verificar que el servicio de PostgreSQL esté activo.
5. Probar conexión desde consola o pgAdmin 4.

### Resultado esperado

PostgreSQL 16 está funcionando localmente.

## Subfase 1.5: Instalación y verificación de pgAdmin 4

### Tareas

1. Instalar pgAdmin 4.
2. Crear conexión al servidor PostgreSQL local.
3. Confirmar acceso al servidor.
4. Verificar que se puedan crear bases de datos.
5. Verificar que se puedan ejecutar scripts SQL.

### Resultado esperado

pgAdmin 4 queda listo para crear y administrar la base de datos.

## Subfase 1.6: Configuración inicial de variables de entorno

### Archivo `.env` inicial

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

El proyecto cuenta con variables de entorno para no escribir credenciales directamente en el código.

---

# Fase 2: Creación de la base de datos en PostgreSQL 16

## Objetivo

Crear en PostgreSQL 16 la base de datos completa del proyecto, respetando las tablas y relaciones definidas.

## Subfase 2.1: Creación de la base de datos

### Tareas

1. Abrir pgAdmin 4.
2. Crear base de datos `cup_ficct`.
3. Confirmar propietario de la base de datos.
4. Confirmar codificación UTF-8.
5. Crear esquema público o esquema propio si se decide usar uno.

### Resultado esperado

La base de datos `cup_ficct` queda creada en PostgreSQL 16.

## Subfase 2.2: Creación de scripts SQL organizados

### Tareas

1. Crear carpeta `database/migrations`.
2. Crear scripts numerados para controlar orden de ejecución.
3. Separar scripts por módulos.
4. Incluir comentarios claros en SQL.
5. Probar cada script desde pgAdmin 4.

### Orden recomendado de scripts

```text
001_crear_tablas_seguridad.sql
002_crear_tablas_personas_usuarios.sql
003_crear_tablas_postulantes.sql
004_crear_tablas_pagos.sql
005_crear_tablas_gestion_carreras_cupos.sql
006_crear_tablas_academicas.sql
007_crear_tablas_horarios.sql
008_crear_tablas_asistencia.sql
009_crear_tablas_examenes_notas.sql
010_crear_tablas_reportes_cargas.sql
011_crear_indices_restricciones.sql
012_insertar_datos_base.sql
```

### Resultado esperado

Los scripts SQL quedan ordenados y listos para ejecutarse de forma controlada.

## Subfase 2.3: Creación de tablas de seguridad y usuarios

### Tablas

- `rol`
- `persona`
- `usuario`
- `administrador`
- `docente`
- `alumno`

### Tareas

1. Crear tabla `rol`.
2. Crear tabla `persona`.
3. Crear tabla `usuario`.
4. Crear tabla `administrador`.
5. Crear tabla `docente`.
6. Crear tabla `alumno`.
7. Crear claves primarias.
8. Crear claves foráneas.
9. Crear restricción `UNIQUE` para CI.
10. Crear restricción `UNIQUE` para email si corresponde.
11. Crear restricción para que `usuario` pertenezca a un rol existente.

### Roles base obligatorios

```text
administrador
docente
alumno
```

### Resultado esperado

La base de seguridad y usuarios queda creada sin roles adicionales.

## Subfase 2.4: Creación de tablas de postulantes y documentos

### Tablas

- `postulante`
- `requisito_postulante`
- `documento_postulante`

### Tareas

1. Crear tabla `postulante` relacionada con `persona`.
2. Crear tabla `requisito_postulante`.
3. Crear tabla `documento_postulante`.
4. Incluir campos para URL de Cloudinary.
5. Incluir campos para `public_id` de Cloudinary si se manejará.
6. Crear estados de validación de requisitos.

### Resultado esperado

Los postulantes pueden tener requisitos y documentos asociados.

## Subfase 2.5: Creación de tablas de pagos y gestión académica

### Tablas

- `pago`
- `gestion_academica`

### Tareas

1. Crear tabla `pago` relacionada con `postulante`.
2. Incluir campos para identificadores de Stripe.
3. Incluir estado de pago.
4. Incluir fecha de pago.
5. Incluir validación administrativa.
6. Crear tabla `gestion_academica`.
7. Validar que gestión sea 1 o 2.
8. Relacionar gestión con módulos posteriores.

### Resultado esperado

La base queda preparada para pagos Stripe y gestiones académicas.

## Subfase 2.6: Creación de tablas de carreras, cupos y postulaciones

### Tablas

- `carrera`
- `cupo_carrera`
- `postulacion`

### Tareas

1. Crear tabla `carrera`.
2. Crear tabla `cupo_carrera`.
3. Crear tabla `postulacion`.
4. Relacionar postulación con postulante.
5. Relacionar primera opción de carrera.
6. Relacionar segunda opción de carrera.
7. Relacionar carrera asignada.
8. Relacionar cupos con gestión académica.

### Resultado esperado

La base queda preparada para admisión por nota, cupo y carrera.

## Subfase 2.7: Creación de tablas académicas

### Tablas

- `materia`
- `grupo`
- `aula`
- `docente_materia_grupo`
- `grupo_alumno`

### Tareas

1. Crear tabla `materia`.
2. Insertar materias base.
3. Crear tabla `grupo`.
4. Crear restricción de cupo máximo 70.
5. Crear tabla `aula` con ubicación.
6. Crear tabla puente `docente_materia_grupo`.
7. Crear tabla puente `grupo_alumno`.

### Materias base obligatorias

```text
Física
Matemáticas
Computación
Inglés
```

### Resultado esperado

La organización académica queda representada en base de datos.

## Subfase 2.8: Creación de tablas de horario

### Tablas

- `dia`
- `turno`
- `periodo`
- `horario_clase`

### Tareas

1. Crear tabla `dia`.
2. Crear tabla `turno`.
3. Crear tabla `periodo`.
4. Validar que cada periodo dure 45 minutos.
5. Crear tabla `horario_clase`.
6. Relacionar horario con día, turno, periodo, aula, docente, grupo y materia.

### Resultado esperado

Los horarios quedan listos para controlar clases y asistencia.

## Subfase 2.9: Creación de tablas de asistencia

### Tablas

- `asistencia_docente`
- `asistencia_alumno`

### Tareas

1. Crear tabla `asistencia_docente`.
2. Crear tabla `asistencia_alumno`.
3. Relacionar asistencia con `horario_clase`.
4. Relacionar asistencia docente con `docente`.
5. Relacionar asistencia alumno con `alumno`.
6. Permitir referencia al docente que registra asistencia de alumnos.
7. Definir estados de asistencia.

### Estados de asistencia

```text
presente
retraso
falta
salida_registrada
justificado
```

### Nota

`salida_registrada` aplica principalmente a asistencia docente. `justificado` puede aplicar a asistencia de alumno si el sistema lo permite desde la lógica administrativa.

### Resultado esperado

La base permite registrar asistencia de docentes y alumnos según horario.

## Subfase 2.10: Creación de tablas de exámenes y notas

### Tablas

- `examen`
- `examen_materia`
- `pregunta`
- `opcion_respuesta`
- `respuesta_alumno`
- `nota_parcial`
- `promedio_final`

### Tareas

1. Crear tabla `examen`.
2. Crear tabla `examen_materia`.
3. Crear tabla `pregunta`.
4. Crear tabla `opcion_respuesta`.
5. Crear tabla `respuesta_alumno`.
6. Crear tabla `nota_parcial`.
7. Crear tabla `promedio_final`.
8. Crear restricciones para notas entre 0 y 100.
9. Crear restricción para número de parcial 1, 2 o 3.
10. Preparar relación con gestión académica.

### Resultado esperado

La base permite construir y resolver exámenes de selección múltiple.

## Subfase 2.11: Creación de tablas de reportes y carga masiva

### Tablas

- `reporte_generado`
- `carga_masiva`
- `detalle_carga_masiva`

### Tareas

1. Crear tabla `reporte_generado`.
2. Crear tabla `carga_masiva`.
3. Crear tabla `detalle_carga_masiva`.
4. Relacionar reportes con usuario que los genera.
5. Relacionar carga masiva con usuario responsable.
6. Relacionar detalle con carga masiva.

### Resultado esperado

La base queda lista para registrar reportes generados y cargas masivas.

## Subfase 2.12: Índices, restricciones y datos base

### Tareas

1. Crear índices para CI.
2. Crear índices para email.
3. Crear índices para código de alumno.
4. Crear índices para búsqueda de postulantes.
5. Crear índices para reportes por gestión.
6. Crear índices para asistencias por fecha.
7. Crear índices para pagos por estado.
8. Insertar roles base.
9. Insertar materias base.
10. Insertar días si se usarán valores iniciales.

### Resultado esperado

La base queda optimizada para consultas frecuentes y con datos mínimos iniciales.

---

# Fase 3: Configuración base del backend PHP

## Objetivo

Crear la estructura inicial del backend PHP, conexión a PostgreSQL y mecanismo base de rutas y respuestas.

## Subfase 3.1: Creación de estructura de carpetas

### Tareas

1. Crear carpeta `public`.
2. Crear carpeta `app`.
3. Crear subcarpetas `Controllers`, `Models`, `Services`, `Middlewares`, `Validators`, `Helpers`, `Routes`.
4. Crear carpeta `config`.
5. Crear carpeta `database`.
6. Crear carpeta `storage`.
7. Crear carpeta `storage/logs`.
8. Crear carpeta `storage/reports`.
9. Crear carpeta `storage/temp`.

### Resultado esperado

El backend tiene una estructura ordenada.

## Subfase 3.2: Configuración de Composer

### Tareas

1. Crear `composer.json`.
2. Configurar autoload PSR-4 para `app`.
3. Instalar dependencias necesarias.
4. Ejecutar `composer dump-autoload`.
5. Verificar que las clases se carguen correctamente.

### Resultado esperado

El backend puede cargar clases automáticamente.

## Subfase 3.3: Configuración de entrada única

### Archivo principal

```text
public/index.php
```

### Tareas

1. Cargar autoload de Composer.
2. Cargar variables de entorno.
3. Configurar headers JSON.
4. Configurar CORS.
5. Capturar método HTTP.
6. Capturar URI solicitada.
7. Enviar petición al router.
8. Manejar errores globales.

### Resultado esperado

Todas las peticiones del backend entran por `public/index.php`.

## Subfase 3.4: Creación del router básico

### Tareas

1. Crear clase o archivo de rutas.
2. Registrar rutas GET.
3. Registrar rutas POST.
4. Registrar rutas PUT.
5. Registrar rutas PATCH.
6. Registrar rutas DELETE.
7. Permitir parámetros en rutas.
8. Asociar rutas con controladores.
9. Responder 404 si la ruta no existe.

### Resultado esperado

El backend puede manejar rutas API.

## Subfase 3.5: Conexión a PostgreSQL con PDO

### Tareas

1. Crear archivo `config/database.php`.
2. Leer credenciales desde `.env`.
3. Crear clase de conexión.
4. Configurar PDO para PostgreSQL.
5. Activar excepciones de PDO.
6. Probar consulta simple.
7. Manejar error de conexión sin exponer contraseña.

### Resultado esperado

El backend se conecta correctamente a PostgreSQL 16.

## Subfase 3.6: Helpers base

### Helpers necesarios

- `ResponseHelper`.
- `ErrorHelper`.
- `AuthHelper`.
- `DateHelper`.
- `ValidationHelper`.

### Tareas

1. Crear helper de respuesta JSON.
2. Crear helper de errores.
3. Crear helper de fechas.
4. Crear helper para validar datos comunes.
5. Crear helper para obtener usuario autenticado.

### Resultado esperado

El backend usa funciones comunes y evita repetir código.

---

# Fase 4: Módulo de autenticación y sesiones

## Objetivo

Implementar login, logout, perfil, control de sesión y acceso por rol.

## Subfase 4.1: Modelo de autenticación

### Tablas afectadas

- `usuario`
- `rol`
- `persona`
- `administrador`
- `docente`
- `alumno`

### Tareas

1. Crear modelo `UsuarioModel`.
2. Crear modelo `RolModel`.
3. Buscar usuario por nombre de usuario, correo o código según corresponda.
4. Obtener rol del usuario.
5. Obtener persona asociada.
6. Verificar si el usuario está activo.

### Resultado esperado

El backend puede consultar usuarios y roles.

## Subfase 4.2: Login tradicional

### Endpoint

```text
POST /api/auth/login
```

### Tareas

1. Recibir usuario/código/correo.
2. Recibir contraseña.
3. Validar usuario obligatorio.
4. Validar contraseña obligatoria.
5. Buscar usuario en base de datos.
6. Validar contraseña con `password_verify`.
7. Validar rol.
8. Generar token interno.
9. Responder datos mínimos del usuario autenticado.

### Resultado esperado

Administrador, docente y alumno pueden iniciar sesión.

## Subfase 4.3: Login de alumno con código automático

### Regla

El alumno inicia sesión usando su código generado automáticamente.

### Tareas

1. Recibir código del alumno.
2. Validar que el código exista.
3. Validar que el usuario asociado tenga rol alumno.
4. Validar contraseña si el flujo la requiere.
5. Generar token interno.
6. Devolver perfil del alumno.

### Resultado esperado

El alumno puede iniciar sesión con su código.

## Subfase 4.4: Firebase Authentication

### Endpoint

```text
POST /api/auth/firebase
```

### Tareas

1. Recibir token de Firebase enviado desde frontend.
2. Verificar token.
3. Obtener correo validado.
4. Verificar que el correo exista en usuarios permitidos.
5. Asociar login con usuario del sistema.
6. Generar token interno.
7. Responder datos del usuario.

### Regla

Firebase se usa para simplificar login con Google/correo. El backend no debe depender solo del correo recibido sin verificar el token.

### Resultado esperado

El backend acepta autenticación validada desde Firebase.

## Subfase 4.5: Logout

### Endpoint

```text
POST /api/auth/logout
```

### Tareas

1. Recibir token actual.
2. Invalidar token si se maneja lista de tokens.
3. Cerrar sesión lógica.
4. Responder confirmación.

### Resultado esperado

El usuario puede cerrar sesión.

## Subfase 4.6: Perfil autenticado

### Endpoint

```text
GET /api/auth/perfil
```

### Tareas

1. Validar token.
2. Obtener usuario autenticado.
3. Obtener rol.
4. Obtener datos de persona.
5. Obtener datos específicos según rol.
6. Responder perfil.

### Resultado esperado

Cada usuario puede consultar su perfil.

## Subfase 4.7: Middleware de autenticación

### Tareas

1. Leer encabezado `Authorization`.
2. Validar token.
3. Obtener usuario autenticado.
4. Bloquear si no hay token.
5. Bloquear si el token es inválido.
6. Bloquear si el usuario está desactivado.

### Resultado esperado

Las rutas privadas quedan protegidas.

## Subfase 4.8: Middleware de autorización por rol

### Tareas

1. Recibir rol requerido.
2. Verificar rol del usuario autenticado.
3. Permitir acceso si coincide.
4. Bloquear acceso si no coincide.
5. Permitir rutas exclusivas para administrador.
6. Permitir rutas exclusivas para docente.
7. Permitir rutas exclusivas para alumno.

### Resultado esperado

El backend respeta los permisos de los tres roles.

---

# Fase 5: Módulo de usuarios, roles y administradores

## Objetivo

Permitir que el administrador gestione usuarios y cree otros administradores sin crear roles fuera del alcance.

## Subfase 5.1: Seed de roles base

### Tareas

1. Insertar rol administrador.
2. Insertar rol docente.
3. Insertar rol alumno.
4. Verificar que no existan roles duplicados.
5. Bloquear creación de roles adicionales desde backend.

### Resultado esperado

Solo existen tres roles.

## Subfase 5.2: Creación del administrador inicial

### Tareas

1. Crear persona inicial.
2. Crear usuario administrador inicial.
3. Encriptar contraseña con `password_hash`.
4. Crear registro en `administrador`.
5. Verificar inicio de sesión.

### Resultado esperado

Existe un administrador inicial para operar el sistema.

## Subfase 5.3: Crear administradores desde el sistema

### Endpoint

```text
POST /api/usuarios/administradores
```

### Tareas

1. Validar que quien solicita sea administrador.
2. Recibir datos de persona.
3. Validar CI único.
4. Validar correo.
5. Crear persona.
6. Crear usuario.
7. Asociar rol administrador.
8. Crear registro en `administrador`.

### Resultado esperado

Un administrador puede crear otros administradores.

## Subfase 5.4: Listado y consulta de usuarios

### Endpoints

```text
GET /api/usuarios
GET /api/usuarios/{id}
```

### Tareas

1. Listar usuarios.
2. Filtrar por rol.
3. Filtrar por estado.
4. Buscar por CI o correo.
5. Consultar detalle de usuario.

### Resultado esperado

El administrador puede consultar usuarios del sistema.

## Subfase 5.5: Activar, desactivar y actualizar usuarios

### Endpoints

```text
PUT   /api/usuarios/{id}
PATCH /api/usuarios/{id}/estado
```

### Tareas

1. Validar rol administrador.
2. Actualizar datos básicos.
3. Activar usuario.
4. Desactivar usuario.
5. Evitar eliminar datos necesarios para trazabilidad.

### Resultado esperado

El administrador controla el estado de usuarios.

---

# Fase 6: Módulo de postulantes

## Objetivo

Implementar el registro, edición, eliminación lógica, búsqueda y listado de postulantes.

## Subfase 6.1: Registro de postulante

### Endpoint

```text
POST /api/postulantes
```

### Datos obligatorios

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

### Tareas

1. Validar campos obligatorios.
2. Validar CI único.
3. Validar correo electrónico.
4. Validar primera opción de carrera.
5. Validar segunda opción de carrera.
6. Validar que ambas carreras existan.
7. Crear persona.
8. Crear postulante.
9. Crear postulación con primera y segunda opción.
10. Asignar estado inicial del postulante.

### Resultado esperado

El postulante queda registrado con sus datos y opciones de carrera.

## Subfase 6.2: Listado de postulantes

### Endpoint

```text
GET /api/postulantes
```

### Tareas

1. Listar postulantes.
2. Permitir filtro por gestión.
3. Permitir filtro por estado.
4. Permitir búsqueda por CI.
5. Permitir búsqueda por nombre.
6. Paginación si hay muchos registros.

### Resultado esperado

El administrador puede listar postulantes.

## Subfase 6.3: Consulta individual de postulante

### Endpoint

```text
GET /api/postulantes/{id}
```

### Tareas

1. Buscar postulante por ID.
2. Obtener datos personales.
3. Obtener requisitos.
4. Obtener documentos.
5. Obtener pagos.
6. Obtener postulación.
7. Responder detalle completo.

### Resultado esperado

El administrador puede revisar un postulante completo.

## Subfase 6.4: Edición de postulante

### Endpoint

```text
PUT /api/postulantes/{id}
```

### Tareas

1. Validar que el postulante exista.
2. Validar cambios de CI sin duplicar.
3. Validar correo.
4. Actualizar datos personales.
5. Actualizar datos de postulante.
6. Actualizar opciones de carrera si corresponde.

### Resultado esperado

Los datos del postulante pueden corregirse.

## Subfase 6.5: Eliminación lógica de postulante

### Endpoint

```text
DELETE /api/postulantes/{id}
```

### Tareas

1. Validar que el postulante exista.
2. Evitar borrar físicamente información importante.
3. Cambiar estado a inactivo, eliminado o rechazado según estructura definida.
4. Registrar fecha de baja si corresponde.

### Resultado esperado

El postulante queda desactivado sin perder trazabilidad.

---

# Fase 7: Requisitos, documentos y Cloudinary

## Objetivo

Permitir subir la imagen del título de bachiller a Cloudinary y que el administrador valide requisitos.

## Subfase 7.1: Configuración de Cloudinary

### Tareas

1. Crear archivo `config/cloudinary.php`.
2. Leer credenciales desde `.env`.
3. Configurar `CLOUDINARY_CLOUD_NAME`.
4. Configurar `CLOUDINARY_API_KEY`.
5. Configurar `CLOUDINARY_API_SECRET`.
6. Crear servicio `CloudinaryService`.

### Resultado esperado

El backend puede conectarse con Cloudinary.

## Subfase 7.2: Subida del título de bachiller

### Endpoint

```text
POST /api/postulantes/{id}/documentos
```

### Tareas

1. Validar que el postulante exista.
2. Validar que el archivo sea imagen.
3. Validar tamaño de archivo.
4. Subir imagen a Cloudinary.
5. Obtener URL segura.
6. Obtener `public_id` si corresponde.
7. Guardar registro en `documento_postulante`.
8. Asociar documento al requisito de título de bachiller.

### Resultado esperado

La imagen del título queda guardada en Cloudinary y referenciada en la base de datos.

## Subfase 7.3: Listado de documentos del postulante

### Endpoint

```text
GET /api/postulantes/{id}/documentos
```

### Tareas

1. Validar postulante.
2. Consultar documentos.
3. Devolver URL de Cloudinary.
4. Mostrar estado del documento.

### Resultado esperado

El administrador puede revisar los documentos del postulante.

## Subfase 7.4: Validación de requisitos por administrador

### Endpoint

```text
PATCH /api/postulantes/{id}/requisitos/validar
```

### Tareas

1. Validar que quien solicita sea administrador.
2. Verificar que exista imagen del título de bachiller.
3. Aprobar requisito.
4. Rechazar requisito si corresponde.
5. Guardar observación si corresponde.
6. Actualizar estado del postulante.
7. Si todos los requisitos están aprobados, habilitar paso a pago.

### Resultado esperado

El postulante solo puede pasar a pago si cumple requisitos aprobados.

---

# Fase 8: Pagos con Stripe

## Objetivo

Implementar el flujo obligatorio de pago mediante Stripe.

## Subfase 8.1: Configuración de Stripe

### Tareas

1. Instalar librería `stripe/stripe-php`.
2. Crear archivo `config/stripe.php`.
3. Leer `STRIPE_SECRET_KEY` desde `.env`.
4. Leer `STRIPE_WEBHOOK_SECRET` desde `.env`.
5. Crear servicio `StripeService`.

### Resultado esperado

El backend puede comunicarse con Stripe.

## Subfase 8.2: Creación de sesión de pago

### Endpoint

```text
POST /api/pagos/stripe/crear-sesion
```

### Tareas

1. Validar que el postulante exista.
2. Validar que los requisitos estén aprobados.
3. Validar que el postulante no esté convertido en alumno.
4. Crear sesión de pago en Stripe.
5. Guardar registro en tabla `pago` con estado pendiente.
6. Guardar ID de sesión Stripe.
7. Devolver URL de pago al frontend.

### Resultado esperado

El postulante puede iniciar pago solo si cumple requisitos.

## Subfase 8.3: Webhook de Stripe

### Endpoint

```text
POST /api/pagos/stripe/webhook
```

### Tareas

1. Recibir evento de Stripe.
2. Validar firma del webhook.
3. Identificar sesión de pago.
4. Actualizar estado del pago a pagado, fallido o cancelado.
5. Guardar fecha de confirmación.
6. Evitar procesar dos veces el mismo evento.

### Resultado esperado

El backend recibe confirmaciones reales de Stripe.

## Subfase 8.4: Consulta de pagos por postulante

### Endpoint

```text
GET /api/pagos/postulante/{id}
```

### Tareas

1. Validar postulante.
2. Consultar pagos registrados.
3. Mostrar estado de cada pago.
4. Mostrar si existe pago pagado.
5. Mostrar si existe pago validado por administrador.

### Resultado esperado

El administrador puede revisar pagos del postulante.

## Subfase 8.5: Validación administrativa del pago

### Endpoint

```text
PATCH /api/pagos/{id}/validar-admin
```

### Tareas

1. Validar rol administrador.
2. Validar que el pago exista.
3. Validar que Stripe haya confirmado el pago.
4. Marcar pago como `validado_admin`.
5. Guardar usuario administrador que validó.
6. Guardar fecha de validación.
7. Actualizar estado del postulante a pago validado.

### Resultado esperado

Solo con pago validado por administrador se puede crear alumno.

---

# Fase 9: Conversión de postulante a alumno y generación de código

## Objetivo

Permitir que el administrador convierta a un postulante en alumno después de requisitos aprobados y pago validado.

## Subfase 9.1: Validaciones previas

### Tareas

1. Validar que quien solicita sea administrador.
2. Validar que el postulante exista.
3. Validar requisitos aprobados.
4. Validar pago Stripe confirmado.
5. Validar pago aprobado por administrador.
6. Validar que el postulante aún no sea alumno.

### Resultado esperado

Solo postulantes aptos pueden convertirse en alumnos.

## Subfase 9.2: Generación automática del código

### Formato

```text
AÑO + GESTIÓN + CÉDULA DE IDENTIDAD
```

### Ejemplo

```text
Año: 2026
Gestión: 1
CI: 13541539
Código: 2026113541539
```

### Tareas

1. Obtener año actual.
2. Obtener gestión actual.
3. Validar que gestión sea 1 o 2.
4. Obtener CI del postulante desde `persona`.
5. Concatenar año + gestión + CI.
6. Validar que el código no exista.
7. Guardar código en `alumno`.

### Resultado esperado

El alumno recibe un código único generado automáticamente.

## Subfase 9.3: Creación de alumno y usuario

### Endpoint

```text
POST /api/postulantes/{id}/convertir-alumno
```

### Tareas

1. Crear registro en `alumno`.
2. Crear usuario con rol alumno.
3. Asociar usuario con persona.
4. Asociar alumno con gestión académica.
5. Guardar código generado.
6. Actualizar estado del postulante a convertido_alumno.
7. Registrar fecha de conversión.

### Resultado esperado

El postulante queda convertido oficialmente en alumno.

---

# Fase 10: Gestión académica, carreras y cupos

## Objetivo

Gestionar gestiones académicas, carreras y cupos por carrera.

## Subfase 10.1: Gestión académica

### Endpoints

```text
GET  /api/gestiones
POST /api/gestiones
```

### Tareas

1. Crear gestión académica.
2. Validar año.
3. Validar que gestión sea 1 o 2.
4. Definir si la gestión está activa.
5. Listar gestiones.
6. Consultar gestión actual.

### Resultado esperado

El sistema maneja gestiones por semestre.

## Subfase 10.2: Carreras

### Endpoints

```text
GET  /api/carreras
POST /api/carreras
PUT  /api/carreras/{id}
```

### Tareas

1. Crear carrera.
2. Editar carrera.
3. Listar carreras.
4. Activar o desactivar carrera si corresponde.
5. Evitar nombres duplicados si se define como regla.

### Resultado esperado

El administrador puede gestionar carreras.

## Subfase 10.3: Cupos por carrera y gestión

### Endpoints

```text
GET  /api/cupos
POST /api/cupos
PUT  /api/cupos/{id}
```

### Tareas

1. Crear cupo por carrera.
2. Relacionar cupo con gestión.
3. Definir cantidad de cupos.
4. Consultar cupos ocupados.
5. Consultar cupos disponibles.
6. Evitar cupos negativos.

### Resultado esperado

Cada carrera tiene cupos por gestión.

## Subfase 10.4: Postulación con dos carreras

### Tareas

1. Validar primera opción obligatoria.
2. Validar segunda opción obligatoria.
3. Validar que ambas carreras existan.
4. Registrar ambas opciones en `postulacion`.
5. Mantener relación con postulante y gestión.

### Resultado esperado

Todo postulante queda asociado a dos opciones de carrera.

---

# Fase 11: Módulo de docentes

## Objetivo

Gestionar docentes y validar requisitos de contratación.

## Subfase 11.1: Registro de docente

### Endpoint

```text
POST /api/docentes
```

### Datos obligatorios

- Nombre.
- Apellido paterno.
- Apellido materno.
- Cédula de identidad.
- Celular.
- Correo.
- Profesional en el área.
- Maestría.
- Diplomado en educación superior.

### Tareas

1. Validar campos obligatorios.
2. Validar CI único.
3. Validar correo.
4. Validar profesional en el área.
5. Validar maestría.
6. Validar diplomado en educación superior.
7. Crear persona.
8. Crear docente.
9. Crear usuario docente.
10. Asociar rol docente.

### Resultado esperado

El docente queda registrado si cumple requisitos.

## Subfase 11.2: Listado, búsqueda y consulta de docentes

### Endpoints

```text
GET /api/docentes
GET /api/docentes/{id}
GET /api/docentes/buscar?ci=...
```

### Tareas

1. Listar docentes.
2. Buscar por CI.
3. Buscar por nombre.
4. Consultar detalle.
5. Consultar materias asignadas.
6. Consultar grupos asignados.
7. Consultar horarios.

### Resultado esperado

El administrador puede consultar docentes.

## Subfase 11.3: Edición y desactivación de docentes

### Endpoints

```text
PUT    /api/docentes/{id}
DELETE /api/docentes/{id}
```

### Tareas

1. Editar datos personales.
2. Editar datos de docente.
3. Validar cambios de CI.
4. Validar cambios de correo.
5. Desactivar docente si corresponde.
6. Evitar borrar historial de asistencia y asignaciones.

### Resultado esperado

Los docentes pueden mantenerse sin perder trazabilidad.

---

# Fase 12: Materias, grupos y aulas

## Objetivo

Administrar materias, grupos y aulas.

## Subfase 12.1: Materias base

### Materias obligatorias

```text
Física
Matemáticas
Computación
Inglés
```

### Tareas

1. Insertar materias base.
2. Listar materias.
3. Validar que existan antes de crear exámenes.

### Endpoint

```text
GET /api/materias
```

### Resultado esperado

Las materias obligatorias están disponibles.

## Subfase 12.2: Creación y gestión de grupos

### Endpoints

```text
POST /api/grupos
GET  /api/grupos
GET  /api/grupos/{id}/alumnos
```

### Tareas

1. Crear grupo.
2. Asociar grupo a gestión.
3. Definir cupo máximo 70.
4. Listar grupos.
5. Consultar alumnos del grupo.
6. Validar que no supere 70 alumnos.

### Resultado esperado

Los grupos se gestionan con cupo máximo de 70.

## Subfase 12.3: Cálculo automático de grupos necesarios

### Fórmula

```text
Cantidad de grupos = techo(total de inscritos / 70)
```

### Tareas

1. Contar alumnos inscritos.
2. Dividir entre 70.
3. Aplicar redondeo hacia arriba.
4. Devolver cantidad de grupos necesarios.
5. Permitir al administrador crear grupos según resultado.

### Resultado esperado

El sistema calcula cuántos grupos se deben habilitar.

## Subfase 12.4: Asignación de alumnos a grupos

### Endpoint

```text
POST /api/grupos/asignar-alumnos
```

### Tareas

1. Validar gestión.
2. Validar alumnos disponibles.
3. Validar grupo existente.
4. Validar cupo máximo 70.
5. Insertar en `grupo_alumno`.
6. Evitar duplicidad del mismo alumno en el mismo grupo.

### Resultado esperado

Los alumnos quedan organizados en grupos.

## Subfase 12.5: Aulas

### Endpoints

```text
GET  /api/aulas
POST /api/aulas
PUT  /api/aulas/{id}
```

### Regla

El aula solo tendrá ubicación.

### Ejemplo

```text
Módulo 236, Aula 11
```

### Tareas

1. Crear aula con ubicación.
2. Editar ubicación.
3. Listar aulas.
4. Asociar aula a horarios.

### Resultado esperado

El sistema maneja aulas mediante ubicación.

---

# Fase 13: Horarios, días, turnos y periodos

## Objetivo

Permitir que el administrador defina horarios que controlarán clases y asistencias.

## Subfase 13.1: Días

### Endpoint

```text
GET /api/dias
```

### Tareas

1. Crear días si se manejan como catálogo.
2. Listar días disponibles.
3. Usarlos para horarios.

### Resultado esperado

Los horarios pueden asociarse a días.

## Subfase 13.2: Turnos

### Endpoints

```text
POST /api/turnos
GET  /api/turnos
```

### Tareas

1. Crear turno.
2. Definir nombre del turno.
3. Definir hora de inicio y fin si corresponde.
4. Listar turnos.

### Resultado esperado

El administrador define turnos.

## Subfase 13.3: Periodos de 45 minutos

### Endpoints

```text
POST /api/periodos
GET  /api/periodos
```

### Regla

Cada periodo dura 45 minutos.

### Tareas

1. Crear periodo.
2. Definir hora de inicio.
3. Definir hora de fin.
4. Validar que la diferencia sea 45 minutos.
5. Asociar periodo a turno.

### Resultado esperado

Los periodos respetan duración obligatoria de 45 minutos.

## Subfase 13.4: Creación de horario de clase

### Endpoints

```text
POST /api/horarios
GET  /api/horarios
```

### Tareas

1. Validar día.
2. Validar turno.
3. Validar periodo.
4. Validar aula.
5. Validar grupo.
6. Validar materia.
7. Validar docente.
8. Crear horario.

### Resultado esperado

Existen horarios completos para clases.

## Subfase 13.5: Validación de choques de horario

### Tareas

1. Evitar que un docente tenga dos clases al mismo tiempo.
2. Evitar que un grupo tenga dos clases al mismo tiempo.
3. Evitar que un aula tenga dos clases al mismo tiempo.
4. Validar coincidencia de día, periodo y gestión.

### Resultado esperado

El sistema evita conflictos de horarios.

## Subfase 13.6: Consulta de horarios por usuario

### Endpoints

```text
GET /api/horarios/docente/{id}
GET /api/horarios/alumno/{id}
```

### Tareas

1. Docente consulta su carga horaria.
2. Alumno consulta sus horarios.
3. Administrador consulta horarios generales.

### Resultado esperado

Cada rol ve los horarios permitidos.

---

# Fase 14: Asignación de docentes a materias y grupos

## Objetivo

Asignar docentes a materias y grupos respetando límites definidos.

## Subfase 14.1: Crear asignación docente-materia-grupo

### Endpoint

```text
POST /api/asignaciones/docente-materia-grupo
```

### Tareas

1. Validar docente.
2. Validar materia.
3. Validar grupo.
4. Validar gestión.
5. Crear asignación.

### Resultado esperado

El docente queda asignado a una materia y grupo.

## Subfase 14.2: Validar máximo 4 grupos por docente

### Tareas

1. Contar grupos distintos asignados al docente.
2. Validar que no supere 4.
3. Bloquear nueva asignación si supera el límite.

### Resultado esperado

Un docente no supera 4 grupos.

## Subfase 14.3: Validar máximo 4 materias por docente

### Tareas

1. Contar materias distintas asignadas al docente.
2. Validar que no supere 4.
3. Bloquear nueva asignación si supera el límite.

### Resultado esperado

Un docente no supera 4 materias.

## Subfase 14.4: Consultas de asignaciones

### Endpoints

```text
GET    /api/asignaciones/docente/{id}
GET    /api/asignaciones/grupo/{id}
DELETE /api/asignaciones/{id}
```

### Tareas

1. Listar asignaciones por docente.
2. Listar asignaciones por grupo.
3. Listar asignaciones por materia.
4. Eliminar o desactivar asignación si corresponde.

### Resultado esperado

El administrador puede controlar asignaciones académicas.

---

# Fase 15: Asistencia docente

## Objetivo

Permitir que el docente marque entrada y salida según horario, y que el administrador visualice asistencia docente.

## Subfase 15.1: Detección de horario activo del docente

### Tareas

1. Obtener docente autenticado.
2. Obtener fecha y hora actual.
3. Consultar horario correspondiente al docente.
4. Validar día.
5. Validar periodo.
6. Determinar si hay clase activa o próxima dentro del margen.

### Resultado esperado

El sistema sabe si el docente puede marcar asistencia.

## Subfase 15.2: Marcar entrada docente

### Endpoint

```text
POST /api/asistencia-docente/marcar-entrada
```

### Tareas

1. Validar docente autenticado.
2. Validar horario activo.
3. Comparar hora actual con inicio de clase.
4. Si está dentro del margen permitido, registrar presente.
5. Si pasaron más de 30 minutos, registrar retraso.
6. Evitar doble marcado de entrada.
7. Guardar fecha y hora.

### Resultado esperado

El docente registra su llegada a clase.

## Subfase 15.3: Marcar salida docente

### Endpoint

```text
POST /api/asistencia-docente/marcar-salida
```

### Tareas

1. Validar docente autenticado.
2. Buscar asistencia de entrada.
3. Registrar hora de salida.
4. Marcar salida registrada.
5. Evitar salida sin entrada previa si la lógica lo requiere.

### Resultado esperado

El docente registra finalización de clase.

## Subfase 15.4: Falta automática docente

### Regla

Pasado el horario de la clase, si el docente no marcó asistencia, se marcará automáticamente como falta.

### Tareas

1. Crear servicio que revise horarios vencidos.
2. Buscar docentes con clase y sin asistencia.
3. Registrar falta.
4. Evitar duplicar faltas.
5. Ejecutar el proceso desde endpoint administrativo o tarea programada si luego se habilita.

### Resultado esperado

El sistema genera faltas automáticas para docentes ausentes.

## Subfase 15.5: Consulta visual de asistencia docente

### Endpoints

```text
GET /api/asistencia-docente
GET /api/asistencia-docente/docente/{id}
```

### Filtros

- Docente.
- Fecha.
- Grupo.
- Materia.
- Estado.

### Resultado esperado

El administrador puede ver qué días vinieron o faltaron los docentes.

---

# Fase 16: Asistencia de alumnos

## Objetivo

Permitir que el alumno marque su asistencia y que el docente tome asistencia a sus alumnos.

## Subfase 16.1: Validar horario del alumno

### Tareas

1. Obtener alumno autenticado.
2. Obtener grupo del alumno.
3. Obtener horario activo.
4. Validar día.
5. Validar periodo.
6. Validar materia y grupo.

### Resultado esperado

El sistema determina si el alumno puede marcar asistencia.

## Subfase 16.2: Alumno marca asistencia

### Endpoint

```text
POST /api/asistencia-alumno/marcar
```

### Tareas

1. Validar alumno autenticado.
2. Validar horario activo.
3. Validar margen de 30 minutos.
4. Registrar presente si corresponde.
5. Registrar retraso si pasaron más de 30 minutos.
6. Evitar doble asistencia.

### Resultado esperado

El alumno registra su asistencia.

## Subfase 16.3: Docente toma asistencia a sus alumnos

### Endpoint

```text
POST /api/asistencia-alumno/docente/registrar
```

### Tareas

1. Validar docente autenticado.
2. Validar que el docente tenga asignado el grupo.
3. Validar que el docente tenga asignada la materia.
4. Validar horario activo.
5. Recibir lista de alumnos.
6. Registrar presente, retraso o falta según corresponda.
7. Guardar docente que registró la asistencia.

### Resultado esperado

El docente puede tomar asistencia de sus alumnos asignados.

## Subfase 16.4: Falta automática de alumnos

### Regla

Pasado el horario de clase, si el alumno no marcó asistencia y el docente no lo registró, se marcará falta automática.

### Tareas

1. Consultar horarios vencidos.
2. Obtener alumnos del grupo.
3. Verificar quiénes no tienen asistencia.
4. Registrar falta.
5. Evitar duplicados.

### Resultado esperado

El sistema genera faltas automáticas para alumnos ausentes.

## Subfase 16.5: Consultas por rol

### Endpoints

```text
GET /api/asistencia-alumno/mis-asistencias
GET /api/asistencia-alumno/docente/mis-alumnos
GET /api/asistencia-alumno
```

### Reglas

1. Administrador ve toda la asistencia.
2. Docente ve solo asistencia de sus alumnos.
3. Alumno ve solo su propia asistencia.

### Resultado esperado

Cada rol accede solo a la asistencia permitida.

---

# Fase 17: Exámenes

## Objetivo

Permitir que el administrador cree exámenes, cargue preguntas de selección múltiple y habilite exámenes.

## Subfase 17.1: Crear examen

### Endpoint

```text
POST /api/examenes
```

### Tareas

1. Validar administrador.
2. Asociar examen a gestión académica.
3. Definir parcial 1, 2 o 3.
4. Definir nombre o descripción.
5. Crear examen en estado no habilitado.
6. Validar que no se supere la regla de 3 parciales por gestión.

### Resultado esperado

El administrador crea exámenes parciales.

## Subfase 17.2: Asociar materias y porcentajes

### Endpoint

```text
POST /api/examenes/{id}/materias
```

### Porcentajes definidos como ejemplo

```text
Física: 25%
Matemáticas: 30%
Computación: 30%
Inglés: 15%
Total: 100%
```

### Tareas

1. Asociar Física.
2. Asociar Matemáticas.
3. Asociar Computación.
4. Asociar Inglés.
5. Registrar porcentaje por materia.
6. Validar que la suma sea 100.
7. Evitar duplicar materia en el mismo examen.

### Resultado esperado

El examen tiene materias y ponderaciones válidas.

## Subfase 17.3: Crear preguntas

### Endpoint

```text
POST /api/examenes/{id}/preguntas
```

### Tareas

1. Validar examen.
2. Validar materia.
3. Crear pregunta.
4. Asociar pregunta a examen.
5. Asociar pregunta a materia.
6. Registrar enunciado.
7. Definir estado activo.

### Resultado esperado

El administrador carga preguntas por materia.

## Subfase 17.4: Crear opciones de respuesta

### Endpoint

```text
POST /api/preguntas/{id}/opciones
```

### Tareas

1. Validar pregunta.
2. Crear opciones de selección múltiple.
3. Marcar cuál es correcta.
4. Validar que exista al menos una respuesta correcta.
5. Validar que no todas las opciones estén marcadas como correctas si se maneja una sola correcta.

### Resultado esperado

Cada pregunta tiene opciones de respuesta.

## Subfase 17.5: Habilitar y deshabilitar examen

### Endpoints

```text
PATCH /api/examenes/{id}/habilitar
PATCH /api/examenes/{id}/deshabilitar
```

### Tareas

1. Validar que el examen exista.
2. Validar que tenga materias.
3. Validar que los porcentajes sumen 100.
4. Validar que tenga preguntas.
5. Validar que las preguntas tengan opciones.
6. Cambiar estado a habilitado.
7. Permitir deshabilitar si corresponde.

### Resultado esperado

El alumno solo puede rendir exámenes habilitados.

---

# Fase 18: Resolución de examen por alumno

## Objetivo

Permitir que el alumno rinda un examen habilitado y que el backend calcule su nota.

## Subfase 18.1: Consulta de exámenes habilitados

### Endpoint

```text
GET /api/alumno/examenes/habilitados
```

### Tareas

1. Validar alumno autenticado.
2. Obtener gestión del alumno.
3. Consultar exámenes habilitados.
4. Verificar si ya respondió.
5. Devolver exámenes disponibles.

### Resultado esperado

El alumno ve solo exámenes que puede rendir.

## Subfase 18.2: Mostrar examen

### Endpoint

```text
GET /api/alumno/examenes/{id}
```

### Tareas

1. Validar alumno.
2. Validar examen habilitado.
3. Obtener preguntas.
4. Obtener opciones.
5. No enviar cuál opción es correcta.
6. Devolver estructura del examen.

### Resultado esperado

El alumno recibe el examen para responder.

## Subfase 18.3: Enviar respuestas

### Endpoint

```text
POST /api/alumno/examenes/{id}/responder
```

### Tareas

1. Validar alumno.
2. Validar examen habilitado.
3. Validar respuestas enviadas.
4. Guardar respuestas en `respuesta_alumno`.
5. Evitar doble respuesta del mismo examen si esa regla se aplica.
6. Calcular respuestas correctas por materia.
7. Calcular nota por materia.
8. Aplicar ponderación.
9. Calcular nota final del parcial.
10. Guardar en `nota_parcial`.

### Resultado esperado

El alumno responde y se registra su nota parcial.

## Subfase 18.4: Consulta de resultado del examen

### Endpoint

```text
GET /api/alumno/examenes/{id}/resultado
```

### Tareas

1. Validar alumno.
2. Consultar nota parcial.
3. Mostrar resultado permitido.
4. No exponer información que el administrador no haya permitido.

### Resultado esperado

El alumno puede consultar su resultado si el sistema lo permite.

---

# Fase 19: Notas, promedios y estado final

## Objetivo

Calcular notas parciales, promedio final y estado aprobado/reprobado.

## Subfase 19.1: Registro de notas parciales

### Tareas

1. Registrar nota del parcial 1.
2. Registrar nota del parcial 2.
3. Registrar nota del parcial 3.
4. Validar que cada nota esté entre 0 y 100.
5. Relacionar nota con examen.
6. Relacionar nota con alumno.

### Resultado esperado

Cada alumno tiene sus notas parciales registradas.

## Subfase 19.2: Cálculo de promedio final

### Fórmula

```text
Promedio final = (Parcial 1 + Parcial 2 + Parcial 3) / 3
```

### Endpoint

```text
POST /api/promedios/calcular
```

### Tareas

1. Validar que existan 3 parciales.
2. Sumar notas parciales.
3. Dividir entre 3.
4. Redondear si se define formato.
5. Guardar promedio en `promedio_final`.

### Resultado esperado

El promedio final se calcula correctamente.

## Subfase 19.3: Estado aprobado/reprobado

### Regla

```text
APROBADO  -> promedio >= 60
REPROBADO -> promedio < 60
```

### Tareas

1. Obtener promedio final.
2. Comparar con 60.
3. Guardar estado aprobado o reprobado.
4. Permitir consulta del estado.

### Resultado esperado

El sistema determina automáticamente si el alumno aprobó o reprobó.

## Subfase 19.4: Consultas de notas y promedios

### Endpoints

```text
GET /api/notas/alumno/{id}
GET /api/promedios
GET /api/promedios/aprobados
GET /api/promedios/reprobados
```

### Tareas

1. Consultar notas por alumno.
2. Consultar promedios generales.
3. Filtrar aprobados.
4. Filtrar reprobados.
5. Respetar permisos por rol.

### Resultado esperado

Administrador y alumno pueden consultar información según permisos.

---

# Fase 20: Asignación final de carrera por nota y cupo

## Objetivo

Asignar carrera final a alumnos aprobados respetando nota y cupos.

## Subfase 20.1: Obtener alumnos aprobados

### Tareas

1. Consultar promedios finales.
2. Filtrar estado aprobado.
3. Filtrar por gestión.
4. Ordenar de mayor a menor nota.

### Resultado esperado

El sistema tiene la lista ordenada por prioridad.

## Subfase 20.2: Asignar primera opción

### Tareas

1. Tomar alumno aprobado según orden.
2. Consultar primera opción.
3. Verificar cupo disponible.
4. Asignar carrera si hay cupo.
5. Actualizar cupo ocupado.

### Resultado esperado

Se respeta la primera opción cuando hay cupo.

## Subfase 20.3: Asignar segunda opción

### Tareas

1. Si primera opción está llena, consultar segunda opción.
2. Verificar cupo disponible.
3. Asignar segunda opción si hay cupo.
4. Actualizar cupo ocupado.

### Resultado esperado

Se respeta la segunda opción cuando la primera está llena.

## Subfase 20.4: Asignar carrera con menos personas

### Regla

Si ambas opciones están llenas, el alumno será asignado a la carrera que tenga menos personas.

### Tareas

1. Consultar carreras disponibles.
2. Contar personas asignadas por carrera.
3. Identificar carrera con menos personas.
4. Asignar alumno a esa carrera.
5. Actualizar postulación.

### Resultado esperado

El alumno aprobado queda asignado incluso si sus dos opciones están llenas.

## Subfase 20.5: Endpoint de asignación

### Endpoint

```text
POST /api/admisiones/asignar-carreras
```

### Tareas

1. Validar administrador.
2. Ejecutar asignación completa por gestión.
3. Evitar reasignar alumnos ya asignados si no corresponde.
4. Devolver resumen de asignación.

### Resultado esperado

La admisión final queda procesada según nota y cupos.

---

# Fase 21: Reportes administrativos

## Objetivo

Crear reportes obligatorios del sistema.

## Subfase 21.1: Reporte de lista general de postulantes

### Endpoint

```text
GET /api/reportes/postulantes
```

### Datos esperados

- CI.
- Nombres.
- Apellidos.
- Correo.
- Celular.
- Estado.
- Gestión.
- Primera opción.
- Segunda opción.

### Resultado esperado

El administrador obtiene lista general de postulantes.

## Subfase 21.2: Reporte de aprobados

### Endpoint

```text
GET /api/reportes/aprobados
```

### Resultado esperado

El administrador obtiene alumnos aprobados.

## Subfase 21.3: Reporte de reprobados

### Endpoint

```text
GET /api/reportes/reprobados
```

### Resultado esperado

El administrador obtiene alumnos reprobados.

## Subfase 21.4: Reporte de promedios generales

### Endpoint

```text
GET /api/reportes/promedios
```

### Resultado esperado

El administrador obtiene notas parciales y promedio final.

## Subfase 21.5: Reporte de grupos habilitados

### Endpoint

```text
GET /api/reportes/grupos
```

### Resultado esperado

El administrador obtiene cantidad de grupos y alumnos por grupo.

## Subfase 21.6: Estadísticas por materia

### Endpoint

```text
GET /api/reportes/estadisticas-materia
```

### Resultado esperado

El administrador obtiene estadísticas de rendimiento por Física, Matemáticas, Computación e Inglés.

## Subfase 21.7: Reporte de docentes por grupos

### Endpoint

```text
GET /api/reportes/docentes-grupos
```

### Resultado esperado

El administrador ve docentes asignados a grupos y materias.

## Subfase 21.8: Grupos con mayor cantidad de aprobados

### Endpoint

```text
GET /api/reportes/grupos-mayor-aprobados
```

### Resultado esperado

El administrador identifica grupos con más aprobados.

## Subfase 21.9: Asistencia de docentes

### Endpoint

```text
GET /api/reportes/asistencia-docentes
```

### Resultado esperado

El administrador ve asistencia, retrasos y faltas de docentes.

## Subfase 21.10: Asistencia de alumnos

### Endpoint

```text
GET /api/reportes/asistencia-alumnos
```

### Resultado esperado

El administrador ve asistencia, retrasos y faltas de alumnos.

## Subfase 21.11: Registro de reportes generados

### Tareas

1. Guardar tipo de reporte.
2. Guardar usuario que generó.
3. Guardar fecha y hora.
4. Guardar formato si se exporta.
5. Guardar ruta del archivo si corresponde.

### Resultado esperado

El sistema mantiene historial de reportes generados.

---

# Fase 22: Exportación PDF y Excel

## Objetivo

Permitir exportar reportes en PDF y Excel.

## Subfase 22.1: Configuración de librerías

### Librerías recomendadas

- Dompdf para PDF.
- PhpSpreadsheet para Excel.

### Tareas

1. Instalar Dompdf.
2. Instalar PhpSpreadsheet.
3. Crear `PdfExportService`.
4. Crear `ExcelExportService`.
5. Crear carpeta `storage/reports`.

### Resultado esperado

El backend puede generar archivos PDF y Excel.

## Subfase 22.2: Exportación PDF

### Endpoint

```text
GET /api/reportes/{tipo}/exportar?formato=pdf
```

### Tareas

1. Recibir tipo de reporte.
2. Obtener datos del reporte.
3. Crear plantilla HTML.
4. Generar PDF.
5. Guardar archivo.
6. Registrar reporte generado.
7. Devolver descarga o URL.

### Resultado esperado

El administrador descarga reportes PDF.

## Subfase 22.3: Exportación Excel

### Endpoint

```text
GET /api/reportes/{tipo}/exportar?formato=excel
```

### Tareas

1. Recibir tipo de reporte.
2. Obtener datos.
3. Crear hoja Excel.
4. Crear encabezados.
5. Insertar filas.
6. Guardar archivo.
7. Registrar reporte generado.
8. Devolver descarga o URL.

### Resultado esperado

El administrador descarga reportes Excel.

---

# Fase 23: Reportes por comando de voz

## Objetivo

Permitir que el backend reciba texto interpretado desde el frontend y genere reportes.

## Subfase 23.1: Aclaración técnica

La voz será capturada en el frontend usando Web Speech API.

El backend:

- No procesa audio.
- No recibe archivo de audio.
- Recibe texto ya interpretado.
- Interpreta ese texto.
- Devuelve el reporte correspondiente.

## Subfase 23.2: Endpoint de comando de voz

### Endpoint

```text
POST /api/reportes/comando-voz
```

### Tareas

1. Validar administrador.
2. Recibir texto.
3. Limpiar texto.
4. Convertir texto a minúsculas.
5. Comparar con comandos permitidos.
6. Identificar reporte solicitado.
7. Devolver datos o tipo de reporte detectado.

### Resultado esperado

El backend reconoce comandos escritos a partir de voz.

## Subfase 23.3: Comandos iniciales permitidos

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

Los comandos iniciales quedan definidos y controlados.

## Subfase 23.4: Selección de formato PDF o Excel

### Tareas

1. El backend detecta el reporte.
2. El frontend muestra opción PDF o Excel.
3. El backend recibe formato elegido.
4. Genera exportación correspondiente.

### Resultado esperado

El administrador puede generar reportes por voz y exportarlos.

---

# Fase 24: Carga masiva Excel/CSV

## Objetivo

Permitir carga de datos por lotes desde archivos Excel o CSV entregados por administración.

## Subfase 24.1: Subida de archivo

### Endpoints

```text
POST /api/cargas/csv
POST /api/cargas/excel
```

### Tareas

1. Validar administrador.
2. Validar archivo recibido.
3. Validar extensión CSV o Excel.
4. Validar tamaño.
5. Guardar temporalmente en `storage/temp`.

### Resultado esperado

El backend recibe archivos de carga masiva.

## Subfase 24.2: Lectura de archivo

### Tareas

1. Leer CSV.
2. Leer Excel con PhpSpreadsheet.
3. Obtener encabezados.
4. Recorrer filas.
5. Ignorar filas vacías.

### Resultado esperado

El backend puede interpretar filas del archivo.

## Subfase 24.3: Validación por fila

### Tareas

1. Validar campos obligatorios.
2. Validar CI duplicado.
3. Validar correo.
4. Validar rol permitido.
5. Validar datos según tipo de carga.
6. Registrar errores por fila.

### Resultado esperado

El sistema detecta registros válidos e inválidos.

## Subfase 24.4: Inserción de registros válidos

### Tareas

1. Insertar registros válidos.
2. Crear usuarios si corresponde.
3. Crear docentes si corresponde.
4. Crear alumnos si corresponde.
5. Asociar gestión si corresponde.
6. Evitar duplicados.

### Resultado esperado

La carga masiva inserta datos válidos.

## Subfase 24.5: Registro de carga y detalle

### Endpoints

```text
GET /api/cargas
GET /api/cargas/{id}/detalle
```

### Tareas

1. Registrar carga en `carga_masiva`.
2. Registrar errores en `detalle_carga_masiva`.
3. Guardar cantidad de registros leídos.
4. Guardar cantidad de registros insertados.
5. Guardar cantidad de errores.
6. Mostrar resumen.

### Resultado esperado

El administrador puede revisar historial de cargas masivas.

---

# Fase 25: Dashboard administrativo

## Objetivo

Crear endpoints que alimenten el panel administrativo.

## Subfase 25.1: Resumen general

### Endpoint

```text
GET /api/dashboard/resumen
```

### Indicadores obligatorios

- Total inscritos.
- Total aprobados.
- Total reprobados.
- Total grupos habilitados.

### Resultado esperado

El dashboard muestra indicadores principales.

## Subfase 25.2: Indicadores de pagos

### Datos

- Total pagos pendientes.
- Total pagos validados.
- Total pagos fallidos.
- Total postulantes listos para convertirse en alumnos.

### Resultado esperado

El administrador ve estado de pagos.

## Subfase 25.3: Indicadores de asistencia

### Endpoint

```text
GET /api/dashboard/asistencia
```

### Datos

- Total asistencias docentes.
- Total faltas docentes.
- Total retrasos docentes.
- Total asistencias alumnos.
- Total faltas alumnos.
- Total retrasos alumnos.

### Resultado esperado

El administrador ve asistencia de forma resumida y visual.

## Subfase 25.4: Indicadores de cupos

### Endpoint

```text
GET /api/dashboard/cupos
```

### Datos

- Cupos por carrera.
- Cupos ocupados.
- Cupos disponibles.

### Resultado esperado

El administrador ve disponibilidad de cupos por carrera.

## Subfase 25.5: Indicadores de exámenes

### Endpoint

```text
GET /api/dashboard/examenes
```

### Datos

- Exámenes creados.
- Exámenes habilitados.
- Alumnos que rindieron.
- Alumnos pendientes.

### Resultado esperado

El administrador visualiza el estado de exámenes.

---

# Fase 26: Seguridad del backend

## Objetivo

Proteger rutas, datos, archivos, pagos y accesos del sistema.

## Subfase 26.1: Seguridad en base de datos

### Tareas

1. Usar PDO.
2. Usar consultas preparadas.
3. Evitar SQL Injection.
4. Validar tipos de datos.
5. Manejar errores sin exponer SQL sensible.

### Resultado esperado

El backend reduce riesgos de inyección SQL.

## Subfase 26.2: Seguridad de contraseñas

### Tareas

1. Encriptar con `password_hash`.
2. Validar con `password_verify`.
3. No guardar contraseñas en texto plano.
4. No devolver contraseñas en respuestas JSON.

### Resultado esperado

Las contraseñas se almacenan de forma segura.

## Subfase 26.3: Seguridad de roles

### Tareas

1. Proteger rutas de administrador.
2. Proteger rutas de docente.
3. Proteger rutas de alumno.
4. Evitar acceso cruzado entre roles.
5. Verificar permisos en cada endpoint crítico.

### Resultado esperado

Cada rol accede solo a lo permitido.

## Subfase 26.4: Seguridad en archivos

### Tareas

1. Validar que el título de bachiller sea imagen.
2. Validar extensión.
3. Validar MIME type.
4. Validar tamaño.
5. No ejecutar archivos subidos.
6. Subir a Cloudinary.

### Resultado esperado

La subida de documentos es segura.

## Subfase 26.5: Seguridad en Stripe y Firebase

### Tareas

1. Validar firma de webhook Stripe.
2. No confiar en confirmaciones del frontend para pago final.
3. Validar token Firebase desde backend.
4. No exponer claves secretas.
5. Leer claves desde `.env`.

### Resultado esperado

Los servicios externos se integran de forma segura.

## Subfase 26.6: Logs internos

### Tareas

1. Crear logs de errores.
2. Registrar fallos de login.
3. Registrar errores de Stripe.
4. Registrar errores de Cloudinary.
5. Registrar errores de carga masiva.
6. No guardar contraseñas en logs.

### Resultado esperado

El backend permite diagnosticar errores sin exponer datos sensibles.

---

# Fase 27: Validaciones generales del backend

## Objetivo

Centralizar validaciones para mantener consistencia.

## Subfase 27.1: Validaciones de persona

- CI obligatorio.
- CI único.
- Nombres obligatorios.
- Apellidos obligatorios.
- Correo válido.
- Teléfono o celular válido.

## Subfase 27.2: Validaciones de postulante

- Primera opción de carrera obligatoria.
- Segunda opción de carrera obligatoria.
- Título de bachiller obligatorio.
- Requisitos aprobados antes del pago.

## Subfase 27.3: Validaciones de pago

- Pago obligatorio.
- Pago asociado a postulante.
- Pago confirmado por Stripe.
- Pago validado por administrador antes de crear alumno.

## Subfase 27.4: Validaciones de alumno

- Código único.
- Código generado automáticamente.
- Usuario con rol alumno.

## Subfase 27.5: Validaciones de docente

- Profesional en el área.
- Maestría.
- Diplomado en educación superior.
- Máximo 4 grupos.
- Máximo 4 materias.

## Subfase 27.6: Validaciones de grupo

- Máximo 70 alumnos.

## Subfase 27.7: Validaciones de horario

- Periodo de 45 minutos.
- No choque de docente.
- No choque de grupo.
- No choque de aula.

## Subfase 27.8: Validaciones de asistencia

- Debe existir horario.
- Máximo 30 minutos después del inicio.
- Después de 30 minutos es retraso.
- Pasado el horario es falta automática.

## Subfase 27.9: Validaciones de examen

- Solo 3 parciales.
- Examen habilitado para que el alumno lo rinda.
- Preguntas de selección múltiple.
- Porcentajes de materias deben sumar 100.
- Notas entre 0 y 100.

## Subfase 27.10: Validaciones de promedio

- Debe calcularse con 3 parciales.
- Aprobado si promedio >= 60.
- Reprobado si promedio < 60.

---

# Fase 28: Pruebas del backend

## Objetivo

Probar cada módulo antes de integrarlo completamente al frontend.

## Subfase 28.1: Pruebas de entorno

1. Probar PHP 8.2.12.
2. Probar Apache.
3. Probar PostgreSQL 16.
4. Probar conexión PDO.

## Subfase 28.2: Pruebas de autenticación

1. Login administrador.
2. Login docente.
3. Login alumno con código.
4. Logout.
5. Perfil autenticado.
6. Bloqueo por rol.

## Subfase 28.3: Pruebas de postulantes

1. Crear postulante.
2. Editar postulante.
3. Buscar postulante.
4. Validar CI duplicado.
5. Validar carreras obligatorias.

## Subfase 28.4: Pruebas de Cloudinary

1. Subir imagen.
2. Validar tipo de archivo.
3. Guardar URL.
4. Consultar documento.

## Subfase 28.5: Pruebas de Stripe

1. Crear sesión de pago.
2. Simular pago.
3. Recibir webhook.
4. Validar pago por administrador.
5. Bloquear alumno sin pago validado.

## Subfase 28.6: Pruebas de conversión a alumno

1. Convertir postulante válido.
2. Generar código.
3. Validar formato año + gestión + CI.
4. Crear usuario alumno.
5. Login con código.

## Subfase 28.7: Pruebas académicas

1. Crear gestión.
2. Crear carrera.
3. Crear cupos.
4. Crear docente.
5. Crear grupo.
6. Crear aula.
7. Crear horario.
8. Asignar docente a materia y grupo.

## Subfase 28.8: Pruebas de asistencia

1. Marcar entrada docente.
2. Marcar salida docente.
3. Generar retraso docente.
4. Generar falta docente.
5. Alumno marca asistencia.
6. Docente toma asistencia.
7. Generar retraso alumno.
8. Generar falta alumno.
9. Verificar permisos de consulta.

## Subfase 28.9: Pruebas de exámenes

1. Crear examen.
2. Asignar materias y porcentajes.
3. Validar suma 100.
4. Crear preguntas.
5. Crear opciones.
6. Habilitar examen.
7. Alumno responde.
8. Calcular nota.

## Subfase 28.10: Pruebas de notas, promedios y admisión

1. Registrar tres parciales.
2. Calcular promedio.
3. Determinar aprobado.
4. Determinar reprobado.
5. Asignar carrera por nota y cupo.
6. Probar primera opción.
7. Probar segunda opción.
8. Probar carrera con menos personas.

## Subfase 28.11: Pruebas de reportes y exportación

1. Probar reporte de postulantes.
2. Probar aprobados.
3. Probar reprobados.
4. Probar promedios.
5. Probar grupos.
6. Probar asistencia docente.
7. Probar asistencia alumno.
8. Exportar PDF.
9. Exportar Excel.
10. Probar comando de voz como texto.

## Subfase 28.12: Pruebas de carga masiva

1. Subir CSV.
2. Subir Excel.
3. Validar filas correctas.
4. Registrar errores por fila.
5. Insertar registros válidos.
6. Consultar detalle de carga.

---

# Fase 29: Documentación técnica del backend

## Objetivo

Documentar el backend para desarrollo, mantenimiento y defensa del proyecto.

## Subfase 29.1: Documentación de instalación

Debe incluir:

- Requisitos.
- Instalación de XAMPP.
- PHP 8.2.12.
- PostgreSQL 16.
- pgAdmin 4.
- Composer.
- Configuración `.env`.

## Subfase 29.2: Documentación de estructura del proyecto

Debe explicar:

- `public`.
- `app/Controllers`.
- `app/Models`.
- `app/Services`.
- `app/Middlewares`.
- `app/Validators`.
- `app/Helpers`.
- `app/Routes`.
- `config`.
- `database`.
- `storage`.

## Subfase 29.3: Documentación de base de datos

Debe explicar:

- Tablas.
- Relaciones.
- Cardinalidades.
- Restricciones.
- Datos base.

## Subfase 29.4: Documentación de endpoints

Debe incluir por cada endpoint:

- Método HTTP.
- Ruta.
- Rol permitido.
- Datos de entrada.
- Respuesta esperada.
- Errores posibles.

## Subfase 29.5: Documentación de reglas de negocio

Debe incluir:

- Requisitos antes del pago.
- Pago Stripe obligatorio.
- Validación administrativa del pago.
- Código automático.
- Roles.
- Asistencia.
- Exámenes.
- Promedio.
- Cupos.
- Reportes.

---

# Fase 30: Preparación para despliegue futuro en Railway

## Objetivo

Preparar el backend para un futuro despliegue en Railway, manteniendo PostgreSQL remoto en Clever Cloud y frontend en Vercel.

## Subfase 30.1: Separación de configuración local y producción

### Tareas

1. Usar `.env` local.
2. Usar variables de entorno en Railway.
3. No subir claves reales al repositorio.
4. Evitar rutas absolutas locales.

### Resultado esperado

El backend puede cambiar de ambiente sin modificar código fuente.

## Subfase 30.2: Configuración de base de datos remota

### Tareas

1. Preparar credenciales de Clever Cloud.
2. Configurar host remoto.
3. Configurar puerto.
4. Configurar usuario.
5. Configurar contraseña.
6. Probar conexión desde producción.

### Resultado esperado

El backend podrá conectarse a PostgreSQL remoto cuando se despliegue.

## Subfase 30.3: Configuración de CORS para Vercel

### Tareas

1. Permitir origen local en desarrollo.
2. Permitir origen de Vercel en producción.
3. Bloquear orígenes no autorizados si corresponde.

### Resultado esperado

React en Vercel podrá comunicarse con el backend.

## Subfase 30.4: Configuración de servicios externos en producción

### Tareas

1. Configurar llaves reales de Stripe.
2. Configurar webhook real de Stripe.
3. Configurar Cloudinary.
4. Configurar Firebase.
5. Probar endpoints principales.

### Resultado esperado

El backend queda preparado para producción.

---

# 8. Orden definitivo recomendado de implementación

El orden recomendado para implementar el backend es:

1. Preparar entorno PHP, Apache, PostgreSQL 16 y pgAdmin 4.
2. Crear base de datos.
3. Crear scripts SQL.
4. Crear conexión PHP con PostgreSQL.
5. Crear estructura API REST.
6. Crear autenticación y roles.
7. Crear usuarios y administradores.
8. Crear postulantes.
9. Crear requisitos, documentos y Cloudinary.
10. Crear pagos con Stripe.
11. Crear conversión de postulante a alumno.
12. Crear gestión académica, carreras y cupos.
13. Crear docentes.
14. Crear materias, grupos y aulas.
15. Crear días, turnos, periodos y horarios.
16. Crear asignación docente-materia-grupo.
17. Crear asistencia docente.
18. Crear asistencia alumno.
19. Crear exámenes.
20. Crear resolución de exámenes.
21. Crear notas, promedios y estado final.
22. Crear asignación final de carrera.
23. Crear reportes.
24. Crear exportación PDF y Excel.
25. Crear reportes por comando de voz.
26. Crear carga masiva Excel/CSV.
27. Crear dashboard administrativo.
28. Aplicar seguridad general.
29. Realizar pruebas.
30. Documentar.
31. Preparar despliegue futuro.

---

# 9. Reglas que nunca deben romperse

1. El backend debe ser PHP.
2. La base de datos debe ser PostgreSQL 16.
3. La administración de la base de datos debe hacerse con pgAdmin 4.
4. Solo existen tres roles: administrador, docente y alumno.
5. El administrador tiene acceso completo.
6. El docente solo accede a sus funciones permitidas.
7. El alumno solo accede a su información.
8. El postulante debe cumplir requisitos antes de pagar.
9. El pago debe hacerse mediante Stripe.
10. El administrador valida el pago antes de dar acceso como alumno.
11. El código del alumno se genera automáticamente.
12. El código tiene formato año + gestión + CI.
13. La gestión solo puede ser 1 o 2.
14. Gestión 1 corresponde al primer semestre.
15. Gestión 2 corresponde al segundo semestre.
16. La imagen del título de bachiller se guarda en Cloudinary.
17. Firebase Authentication se usa para simplificar login con Google/correo.
18. Web Speech API se usa en frontend para comandos de voz.
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

# 10. Resultado final esperado

Al finalizar todas las fases y subfases, el backend debe permitir administrar completamente el proceso de admisión universitaria del CUP para la FICCT.

El backend final debe permitir:

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
- Preparación para despliegue futuro.

---

# 11. Nota final

Este archivo `backend_subfases.md` debe usarse como la guía definitiva para construir el backend. Cada fase debe implementarse y probarse antes de pasar a la siguiente, porque los módulos dependen unos de otros.

No se debe eliminar ningún requisito ya definido en `contexto.md`, `base_de_datos.md` ni `backend_fases.md`.

# Base de Datos - Aplicación Web de Admisión Universitaria (CUP) para la FICCT

## 1. Objetivo del archivo

Este archivo define la estructura de base de datos para el backend de la **Aplicación Web de Admisión Universitaria (CUP) para la FICCT**.

El objetivo es dejar documentadas las tablas necesarias, sus atributos, tipos de datos, relaciones, cardinalidades y reglas principales para poder construir el backend en **PHP 8.2.12** usando **PostgreSQL 16**.

Esta base de datos está diseñada a partir del contexto definido para el sistema, respetando estas decisiones obligatorias:

- Solo existen 3 roles: **Administrador**, **Docente** y **Alumno**.
- El administrador tiene acceso completo al sistema.
- El administrador puede crear otros administradores.
- El administrador valida requisitos y pago antes de dar acceso al postulante como alumno.
- El pago se realizará con **Stripe**.
- Las imágenes del título de bachiller se guardarán en **Cloudinary**.
- Se usará **Firebase Authentication** para simplificar login con Google y validación de correo.
- Se usará **Web Speech API** para comandos de voz en reportes.
- El código del alumno se genera automáticamente con el formato: `AÑO + GESTION + CEDULA`.
- Cada año tiene 2 gestiones: gestión 1 y gestión 2.
- Cada grupo admite máximo 70 alumnos.
- Cada periodo dura 45 minutos.
- La asistencia es obligatoria para docentes y alumnos.
- Docentes y alumnos solo pueden marcar asistencia según el horario definido por el administrador.
- La asistencia se puede marcar máximo 30 minutos después del inicio de clase.
- Después de los 30 minutos será retraso.
- Pasado el horario de la clase será falta automática.
- El alumno debe rendir 3 exámenes por gestión.
- Las preguntas son de selección múltiple.
- Las materias evaluadas son: Física, Matemáticas, Computación e Inglés.
- Las materias tienen porcentajes dentro del examen.
- Ejemplo de porcentajes: Física 25%, Matemáticas 30%, Computación 30%, Inglés 15%.
- La suma de porcentajes debe ser 100%.
- El promedio final se calcula con la suma de los 3 parciales dividida entre 3.
- Si el promedio final es mayor o igual a 60, el alumno queda aprobado.
- Si el promedio final es menor a 60, el alumno queda reprobado.
- Cada carrera tiene cupos por gestión.
- El alumno debe elegir dos carreras obligatoriamente.
- Se prioriza la admisión por mayor nota.
- Primero se intenta asignar al alumno a su primera opción de carrera.
- Si la primera opción está llena, se intenta asignar a la segunda opción.
- Si ambas están llenas, se añade a la carrera que tenga menos personas.

---

## 2. Convenciones generales de la base de datos

### 2.1. Motor de base de datos

```text
PostgreSQL 16
```

### 2.2. Convención de nombres

Todas las tablas y columnas se nombran en español, en minúsculas y usando guion bajo.

Ejemplos:

```text
persona
usuario
postulante
gestion_academica
asistencia_alumno
```

### 2.3. Tipos de datos recomendados

| Tipo | Uso recomendado |
|---|---|
| `BIGSERIAL` | Llaves primarias autoincrementales |
| `BIGINT` | Llaves foráneas que apuntan a `BIGSERIAL` |
| `VARCHAR(n)` | Textos cortos con longitud limitada |
| `TEXT` | Textos largos |
| `DATE` | Fechas sin hora |
| `TIME` | Hora sin fecha |
| `TIMESTAMP` | Fecha y hora |
| `BOOLEAN` | Valores verdadero/falso |
| `INTEGER` | Números enteros |
| `NUMERIC(5,2)` | Notas, porcentajes y montos pequeños con decimales |
| `NUMERIC(10,2)` | Montos de pago |
| `JSONB` | Datos estructurados flexibles, como respuestas de APIs externas |

### 2.4. Estados recomendados como texto controlado

Para simplificar la implementación en PHP, se recomienda usar `VARCHAR` con restricciones `CHECK`, en lugar de crear tipos `ENUM` propios de PostgreSQL.

Ejemplos:

```sql
estado IN ('pendiente', 'aprobado', 'rechazado')
rol IN ('administrador', 'docente', 'alumno')
```

---

## 3. Módulos cubiertos por la base de datos

La base de datos cubre los siguientes módulos:

1. Personas.
2. Usuarios.
3. Roles.
4. Administradores.
5. Docentes.
6. Alumnos.
7. Postulantes.
8. Validación de requisitos.
9. Documentos del postulante en Cloudinary.
10. Pagos con Stripe.
11. Gestión académica.
12. Carreras.
13. Cupos por carrera.
14. Postulaciones.
15. Admisión por nota y cupo.
16. Materias.
17. Grupos.
18. Aulas.
19. Días.
20. Turnos.
21. Periodos.
22. Horarios.
23. Asignación docente-materia-grupo-horario.
24. Asignación alumno-grupo.
25. Asistencia docente.
26. Asistencia alumno.
27. Exámenes.
28. Porcentajes de materias en exámenes.
29. Preguntas de selección múltiple.
30. Opciones de respuesta.
31. Respuestas de alumnos.
32. Notas por materia.
33. Notas por parcial.
34. Promedios finales.
35. Reportes.
36. Comandos de voz.
37. Carga masiva Excel/CSV.

---

# 4. Tablas de seguridad, personas y usuarios

---

## 4.1. Tabla: `rol`

### Objetivo

Guardar los únicos 3 roles permitidos en el sistema.

### Registros permitidos

| id | nombre |
|---|---|
| 1 | administrador |
| 2 | docente |
| 3 | alumno |

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador del rol |
| nombre | VARCHAR(30) | NOT NULL, UNIQUE | Nombre del rol |
| descripcion | TEXT | NULL | Descripción del rol |
| activo | BOOLEAN | NOT NULL DEFAULT TRUE | Indica si el rol está activo |

### Restricción importante

```sql
CHECK (nombre IN ('administrador', 'docente', 'alumno'))
```

### Relaciones

- `rol` 1..* `usuario`
- Un rol puede estar asignado a muchos usuarios.
- Un usuario solo tiene un rol.

---

## 4.2. Tabla: `persona`

### Objetivo

Guardar los datos personales comunes de administradores, docentes, alumnos y postulantes.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador de la persona |
| cedula_identidad | VARCHAR(20) | NOT NULL, UNIQUE | Cédula de identidad de la persona |
| nombres | VARCHAR(100) | NOT NULL | Nombres de la persona |
| apellido_paterno | VARCHAR(100) | NOT NULL | Apellido paterno |
| apellido_materno | VARCHAR(100) | NULL | Apellido materno |
| fecha_nacimiento | DATE | NULL | Fecha de nacimiento, requerida para postulantes/alumnos |
| sexo | VARCHAR(20) | NULL | Sexo de la persona |
| direccion | TEXT | NULL | Dirección de la persona |
| telefono | VARCHAR(30) | NULL | Teléfono fijo si corresponde |
| celular | VARCHAR(30) | NULL | Número de celular |
| correo | VARCHAR(150) | NOT NULL, UNIQUE | Correo electrónico |
| ciudad | VARCHAR(100) | NULL | Ciudad de residencia o procedencia |
| creado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| actualizado_en | TIMESTAMP | NULL | Fecha de actualización |

### Relaciones

- `persona` 1..0..1 `usuario`
- `persona` 1..0..1 `administrador`
- `persona` 1..0..1 `docente`
- `persona` 1..0..1 `postulante`
- `persona` 1..0..1 `alumno`

### Notas

Una persona puede existir primero como postulante y luego convertirse en alumno. Para evitar duplicación de datos personales, los datos comunes se centralizan en esta tabla.

---

## 4.3. Tabla: `usuario`

### Objetivo

Guardar las cuentas de acceso al sistema para administradores, docentes y alumnos.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador del usuario |
| persona_id | BIGINT | NOT NULL, FK, UNIQUE | Persona asociada al usuario |
| rol_id | BIGINT | NOT NULL, FK | Rol del usuario |
| nombre_usuario | VARCHAR(100) | NOT NULL, UNIQUE | Nombre de usuario o identificador de acceso |
| codigo_acceso | VARCHAR(30) | NULL, UNIQUE | Código de acceso para alumnos |
| correo_verificado | BOOLEAN | NOT NULL DEFAULT FALSE | Indica si el correo fue verificado |
| firebase_uid | VARCHAR(150) | NULL, UNIQUE | UID de Firebase si usa Google/Firebase Authentication |
| password_hash | TEXT | NULL | Contraseña cifrada si se maneja acceso tradicional |
| activo | BOOLEAN | NOT NULL DEFAULT TRUE | Estado de la cuenta |
| ultimo_inicio_sesion | TIMESTAMP | NULL | Último inicio de sesión |
| creado_por_usuario_id | BIGINT | NULL, FK | Usuario administrador que creó esta cuenta |
| creado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| actualizado_en | TIMESTAMP | NULL | Fecha de actualización |

### Relaciones

- `persona` 1..1 `usuario`
- `rol` 1..* `usuario`
- `usuario` 1..* `usuario` mediante `creado_por_usuario_id`

### Reglas

- Solo pueden existir usuarios con rol administrador, docente o alumno.
- El administrador puede crear otros administradores.
- El alumno puede iniciar sesión usando su `codigo_acceso`.
- Firebase Authentication se usa para login con Google y validación de correo.
- El backend PHP puede verificar el token de Firebase usando `firebase_uid`.

---

## 4.4. Tabla: `administrador`

### Objetivo

Identificar a las personas que son administradores del sistema.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador del administrador |
| persona_id | BIGINT | NOT NULL, FK, UNIQUE | Persona asociada |
| usuario_id | BIGINT | NOT NULL, FK, UNIQUE | Usuario asociado |
| activo | BOOLEAN | NOT NULL DEFAULT TRUE | Estado del administrador |
| creado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

### Relaciones

- `persona` 1..0..1 `administrador`
- `usuario` 1..0..1 `administrador`

### Reglas

- El administrador tiene acceso completo al sistema.
- El administrador puede crear otros administradores.

---

## 4.5. Tabla: `docente`

### Objetivo

Guardar los datos propios de los docentes y sus requisitos de contratación.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador del docente |
| persona_id | BIGINT | NOT NULL, FK, UNIQUE | Persona asociada |
| usuario_id | BIGINT | NULL, FK, UNIQUE | Usuario asociado, si ya tiene cuenta |
| es_profesional_area | BOOLEAN | NOT NULL DEFAULT FALSE | Indica si es profesional en el área |
| tiene_maestria | BOOLEAN | NOT NULL DEFAULT FALSE | Indica si tiene maestría |
| tiene_diplomado_educacion_superior | BOOLEAN | NOT NULL DEFAULT FALSE | Indica si tiene diplomado en educación superior |
| contratado | BOOLEAN | NOT NULL DEFAULT FALSE | Indica si fue contratado |
| activo | BOOLEAN | NOT NULL DEFAULT TRUE | Estado del docente |
| creado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de registro |
| actualizado_en | TIMESTAMP | NULL | Fecha de actualización |

### Relaciones

- `persona` 1..0..1 `docente`
- `usuario` 1..0..1 `docente`
- `docente` 1..* `asignacion_docente`
- `docente` 1..* `asistencia_docente`

### Reglas

- Para ser contratado debe cumplir:
  - Ser profesional en el área.
  - Tener maestría.
  - Tener diplomado en educación superior.
- Puede ser asignado de 1 a 4 grupos.
- Puede dar de 1 a 4 materias como máximo.
- Solo puede ver su perfil, carga horaria, grupos, materias, horarios y asistencia de sus alumnos.

---

## 4.6. Tabla: `alumno`

### Objetivo

Guardar los datos del alumno que ya recibió acceso después de cumplir requisitos y pagar mediante Stripe.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador del alumno |
| persona_id | BIGINT | NOT NULL, FK, UNIQUE | Persona asociada |
| usuario_id | BIGINT | NOT NULL, FK, UNIQUE | Usuario asociado |
| postulante_id | BIGINT | NOT NULL, FK, UNIQUE | Postulante del cual proviene |
| gestion_academica_id | BIGINT | NOT NULL, FK | Gestión en la que participa |
| codigo_alumno | VARCHAR(30) | NOT NULL, UNIQUE | Código automático del alumno |
| estado_academico | VARCHAR(30) | NOT NULL DEFAULT 'activo' | Estado académico del alumno |
| creado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha en la que fue habilitado como alumno |

### Restricciones recomendadas

```sql
CHECK (estado_academico IN ('activo', 'aprobado', 'reprobado'))
```

### Relaciones

- `persona` 1..0..1 `alumno`
- `usuario` 1..0..1 `alumno`
- `postulante` 1..0..1 `alumno`
- `gestion_academica` 1..* `alumno`
- `alumno` 1..* `grupo_alumno`
- `alumno` 1..* `asistencia_alumno`
- `alumno` 1..* `intento_examen`
- `alumno` 1..0..1 `promedio_final`

### Reglas

- El alumno se crea solo después de validar requisitos y pago.
- El código del alumno se genera automáticamente.
- El código se forma con año + gestión + cédula de identidad.
- El alumno puede iniciar sesión con ese código.

---

# 5. Tablas de gestión académica, carreras y postulaciones

---

## 5.1. Tabla: `gestion_academica`

### Objetivo

Representar las gestiones académicas del CUP por año y semestre.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador de la gestión |
| anio | INTEGER | NOT NULL | Año de la gestión |
| numero_gestion | INTEGER | NOT NULL | Gestión 1 o 2 |
| nombre | VARCHAR(100) | NOT NULL | Nombre descriptivo, ejemplo: 2026-1 |
| fecha_inicio | DATE | NULL | Fecha de inicio |
| fecha_fin | DATE | NULL | Fecha de fin |
| activa | BOOLEAN | NOT NULL DEFAULT TRUE | Indica si la gestión está activa |
| creado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

### Restricciones

```sql
CHECK (numero_gestion IN (1, 2))
UNIQUE (anio, numero_gestion)
```

### Relaciones

- `gestion_academica` 1..* `postulante`
- `gestion_academica` 1..* `alumno`
- `gestion_academica` 1..* `cupo_carrera`
- `gestion_academica` 1..* `grupo`
- `gestion_academica` 1..* `examen`

### Reglas

- Gestión 1 corresponde al primer semestre.
- Gestión 2 corresponde al segundo semestre.
- La gestión forma parte del código automático del alumno.

---

## 5.2. Tabla: `carrera`

### Objetivo

Guardar las carreras a las que los postulantes pueden postularse.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador de la carrera |
| nombre | VARCHAR(150) | NOT NULL, UNIQUE | Nombre de la carrera |
| descripcion | TEXT | NULL | Descripción opcional |
| activa | BOOLEAN | NOT NULL DEFAULT TRUE | Estado de la carrera |
| creado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

### Relaciones

- `carrera` 1..* `cupo_carrera`
- `carrera` 1..* `postulacion` como primera opción
- `carrera` 1..* `postulacion` como segunda opción
- `carrera` 1..* `postulacion` como carrera asignada

---

## 5.3. Tabla: `cupo_carrera`

### Objetivo

Guardar la cantidad de cupos de cada carrera por gestión académica.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador del cupo |
| carrera_id | BIGINT | NOT NULL, FK | Carrera asociada |
| gestion_academica_id | BIGINT | NOT NULL, FK | Gestión académica asociada |
| cantidad_cupos | INTEGER | NOT NULL | Cantidad de cupos disponibles |
| creado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| actualizado_en | TIMESTAMP | NULL | Fecha de actualización |

### Restricciones

```sql
CHECK (cantidad_cupos >= 0)
UNIQUE (carrera_id, gestion_academica_id)
```

### Relaciones

- `carrera` 1..* `cupo_carrera`
- `gestion_academica` 1..* `cupo_carrera`

### Reglas

- Cada carrera maneja una cantidad de cupos por gestión.
- La admisión se prioriza por mayor nota.

---

## 5.4. Tabla: `postulante`

### Objetivo

Guardar los datos del postulante antes de convertirse en alumno.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador del postulante |
| persona_id | BIGINT | NOT NULL, FK, UNIQUE | Persona asociada |
| gestion_academica_id | BIGINT | NOT NULL, FK | Gestión a la que postula |
| colegio_procedencia | VARCHAR(150) | NOT NULL | Colegio de procedencia |
| estado_requisitos | VARCHAR(30) | NOT NULL DEFAULT 'pendiente' | Estado de revisión de requisitos |
| estado_pago | VARCHAR(30) | NOT NULL DEFAULT 'pendiente' | Estado del pago |
| estado_postulante | VARCHAR(30) | NOT NULL DEFAULT 'registrado' | Estado general del postulante |
| observacion | TEXT | NULL | Observación administrativa |
| creado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de registro |
| actualizado_en | TIMESTAMP | NULL | Fecha de actualización |

### Restricciones recomendadas

```sql
CHECK (estado_requisitos IN ('pendiente', 'aprobado', 'rechazado'))
CHECK (estado_pago IN ('pendiente', 'pagado', 'rechazado'))
CHECK (estado_postulante IN ('registrado', 'pendiente_pago', 'pagado', 'habilitado_alumno', 'rechazado'))
```

### Relaciones

- `persona` 1..0..1 `postulante`
- `gestion_academica` 1..* `postulante`
- `postulante` 1..* `documento_postulante`
- `postulante` 1..0..1 `pago_stripe`
- `postulante` 1..1 `postulacion`
- `postulante` 1..0..1 `alumno`

### Reglas

- La cédula no puede duplicarse porque está controlada en `persona.cedula_identidad`.
- El correo no puede duplicarse porque está controlado en `persona.correo`.
- Debe validar campos vacíos.
- Debe validar correo electrónico.
- Puede validar cuenta con Google usando Firebase.
- Debe subir imagen de título de bachiller a Cloudinary.
- Si cumple requisitos, debe pasar al pago con Stripe.

---

## 5.5. Tabla: `documento_postulante`

### Objetivo

Guardar los documentos del postulante almacenados en Cloudinary.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador del documento |
| postulante_id | BIGINT | NOT NULL, FK | Postulante propietario del documento |
| tipo_documento | VARCHAR(50) | NOT NULL | Tipo de documento |
| cloudinary_public_id | VARCHAR(200) | NOT NULL | ID público de Cloudinary |
| cloudinary_url | TEXT | NOT NULL | URL del archivo en Cloudinary |
| formato_archivo | VARCHAR(30) | NULL | Formato del archivo |
| subido_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de subida |
| estado_revision | VARCHAR(30) | NOT NULL DEFAULT 'pendiente' | Estado de revisión del documento |
| observacion | TEXT | NULL | Observación del administrador |

### Restricciones recomendadas

```sql
CHECK (tipo_documento IN ('titulo_bachiller'))
CHECK (estado_revision IN ('pendiente', 'aprobado', 'rechazado'))
```

### Relaciones

- `postulante` 1..* `documento_postulante`

### Reglas

- El documento obligatorio definido por el contexto es la imagen del título de bachiller.
- La imagen se guarda en Cloudinary.

---

## 5.6. Tabla: `pago_stripe`

### Objetivo

Registrar el pago obligatorio realizado mediante Stripe.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador del pago |
| postulante_id | BIGINT | NOT NULL, FK, UNIQUE | Postulante que realiza el pago |
| stripe_payment_intent_id | VARCHAR(200) | NULL, UNIQUE | ID del Payment Intent de Stripe |
| stripe_checkout_session_id | VARCHAR(200) | NULL, UNIQUE | ID de sesión de Stripe Checkout si se usa Checkout |
| monto | NUMERIC(10,2) | NOT NULL | Monto pagado |
| moneda | VARCHAR(10) | NOT NULL DEFAULT 'BOB' | Moneda del pago |
| estado_pago | VARCHAR(30) | NOT NULL DEFAULT 'pendiente' | Estado del pago |
| fecha_pago | TIMESTAMP | NULL | Fecha de pago confirmado |
| respuesta_stripe | JSONB | NULL | Respuesta completa o parcial de Stripe |
| validado_por_usuario_id | BIGINT | NULL, FK | Administrador que valida el pago |
| validado_en | TIMESTAMP | NULL | Fecha de validación administrativa |
| creado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

### Restricciones recomendadas

```sql
CHECK (monto >= 0)
CHECK (estado_pago IN ('pendiente', 'pagado', 'rechazado', 'fallido'))
```

### Relaciones

- `postulante` 1..0..1 `pago_stripe`
- `usuario` 1..* `pago_stripe` como administrador validador

### Reglas

- El pago es obligatorio para que el postulante pueda convertirse en alumno.
- El administrador recibe o revisa la información del pago.
- El administrador valida el pago antes de dar acceso como alumno.

---

## 5.7. Tabla: `postulacion`

### Objetivo

Guardar las dos carreras obligatorias a las que postula el alumno y la carrera final asignada después del proceso de admisión.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador de la postulación |
| postulante_id | BIGINT | NOT NULL, FK, UNIQUE | Postulante asociado |
| primera_carrera_id | BIGINT | NOT NULL, FK | Primera opción de carrera |
| segunda_carrera_id | BIGINT | NOT NULL, FK | Segunda opción de carrera |
| carrera_asignada_id | BIGINT | NULL, FK | Carrera final asignada |
| motivo_asignacion | VARCHAR(100) | NULL | Motivo de asignación |
| promedio_final | NUMERIC(5,2) | NULL | Promedio final usado para priorizar admisión |
| estado_final | VARCHAR(30) | NULL | Aprobado o reprobado |
| orden_prioridad | INTEGER | NULL | Posición según mayor nota |
| asignado_en | TIMESTAMP | NULL | Fecha de asignación de carrera |

### Restricciones recomendadas

```sql
CHECK (primera_carrera_id <> segunda_carrera_id)
CHECK (promedio_final IS NULL OR (promedio_final >= 0 AND promedio_final <= 100))
CHECK (estado_final IS NULL OR estado_final IN ('aprobado', 'reprobado'))
CHECK (motivo_asignacion IS NULL OR motivo_asignacion IN ('primera_opcion', 'segunda_opcion'))
```

### Relaciones

- `postulante` 1..1 `postulacion`
- `carrera` 1..* `postulacion` como primera opción
- `carrera` 1..* `postulacion` como segunda opción
- `carrera` 1..* `postulacion` como carrera asignada

### Reglas

- El alumno debe elegir dos carreras obligatoriamente.
- Siempre se prioriza por mayor nota.
- Primero se intenta asignar a la primera opción.
- Si la primera opción está llena, se intenta asignar a la segunda opción.
- Si ambas están llenas, se asigna a la carrera que tenga menos personas.

---

# 6. Tablas de materias, grupos, aulas y horarios

---

## 6.1. Tabla: `materia`

### Objetivo

Guardar las materias o áreas evaluadas en el CUP.

### Registros iniciales requeridos

| nombre |
|---|
| Física |
| Matemáticas |
| Computación |
| Inglés |

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador de la materia |
| nombre | VARCHAR(100) | NOT NULL, UNIQUE | Nombre de la materia |
| activa | BOOLEAN | NOT NULL DEFAULT TRUE | Estado de la materia |
| creado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

### Relaciones

- `materia` 1..* `asignacion_docente`
- `materia` 1..* `examen_materia_porcentaje`
- `materia` 1..* `pregunta`
- `materia` 1..* `nota_examen_materia`

---

## 6.2. Tabla: `grupo`

### Objetivo

Guardar los grupos habilitados para el curso preuniversitario.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador del grupo |
| gestion_academica_id | BIGINT | NOT NULL, FK | Gestión académica |
| nombre | VARCHAR(100) | NOT NULL | Nombre o código del grupo |
| cupo_maximo | INTEGER | NOT NULL DEFAULT 70 | Cupo máximo del grupo |
| activo | BOOLEAN | NOT NULL DEFAULT TRUE | Estado del grupo |
| creado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

### Restricciones recomendadas

```sql
CHECK (cupo_maximo <= 70)
CHECK (cupo_maximo > 0)
UNIQUE (gestion_academica_id, nombre)
```

### Relaciones

- `gestion_academica` 1..* `grupo`
- `grupo` 1..* `grupo_alumno`
- `grupo` 1..* `asignacion_docente`
- `grupo` 1..* `horario_clase`

### Reglas

- Cada grupo admite máximo 70 alumnos.
- El sistema calcula automáticamente la cantidad de grupos necesarios según total de inscritos.

---

## 6.3. Tabla: `grupo_alumno`

### Objetivo

Asignar alumnos a grupos.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador de la asignación |
| grupo_id | BIGINT | NOT NULL, FK | Grupo asignado |
| alumno_id | BIGINT | NOT NULL, FK | Alumno asignado |
| fecha_asignacion | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de asignación |
| activo | BOOLEAN | NOT NULL DEFAULT TRUE | Estado de la asignación |

### Restricciones recomendadas

```sql
UNIQUE (grupo_id, alumno_id)
```

### Relaciones

- `grupo` 1..* `grupo_alumno`
- `alumno` 1..* `grupo_alumno`

### Cardinalidad real

- `grupo` *..* `alumno`, resuelta mediante `grupo_alumno`.
- Un grupo tiene muchos alumnos.
- Un alumno puede estar relacionado a un grupo dentro de una gestión. Se deja la tabla puente para mantener trazabilidad.

---

## 6.4. Tabla: `aula`

### Objetivo

Guardar la ubicación del aula.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador del aula |
| ubicacion | VARCHAR(200) | NOT NULL, UNIQUE | Ubicación del aula |
| activa | BOOLEAN | NOT NULL DEFAULT TRUE | Estado del aula |
| creado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

### Ejemplo de ubicación

```text
Módulo 236, Aula 11
```

### Relaciones

- `aula` 1..* `horario_clase`

### Regla

- Por ahora, el aula solo maneja ubicación.
- No se registra capacidad, equipamiento ni otros atributos.

---

## 6.5. Tabla: `dia`

### Objetivo

Guardar los días disponibles para definir horarios.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador del día |
| nombre | VARCHAR(30) | NOT NULL, UNIQUE | Nombre del día |
| orden | INTEGER | NOT NULL, UNIQUE | Orden del día en la semana |
| activo | BOOLEAN | NOT NULL DEFAULT TRUE | Estado del día |

### Relaciones

- `dia` 1..* `horario_clase`

---

## 6.6. Tabla: `turno`

### Objetivo

Guardar los turnos definidos por el administrador.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador del turno |
| nombre | VARCHAR(50) | NOT NULL, UNIQUE | Nombre del turno |
| hora_inicio | TIME | NOT NULL | Hora de inicio del turno |
| hora_fin | TIME | NOT NULL | Hora de fin del turno |
| activo | BOOLEAN | NOT NULL DEFAULT TRUE | Estado del turno |

### Relaciones

- `turno` 1..* `periodo`
- `turno` 1..* `horario_clase`

### Regla

- El administrador define los turnos.

---

## 6.7. Tabla: `periodo`

### Objetivo

Guardar los periodos de clase definidos por el administrador.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador del periodo |
| turno_id | BIGINT | NOT NULL, FK | Turno al que pertenece |
| numero_periodo | INTEGER | NOT NULL | Número del periodo dentro del turno |
| hora_inicio | TIME | NOT NULL | Hora de inicio del periodo |
| hora_fin | TIME | NOT NULL | Hora de fin del periodo |
| duracion_minutos | INTEGER | NOT NULL DEFAULT 45 | Duración del periodo |
| activo | BOOLEAN | NOT NULL DEFAULT TRUE | Estado del periodo |

### Restricciones recomendadas

```sql
CHECK (duracion_minutos = 45)
UNIQUE (turno_id, numero_periodo)
```

### Relaciones

- `turno` 1..* `periodo`
- `periodo` 1..* `horario_clase`

### Regla

- Cada periodo dura 45 minutos.

---

## 6.8. Tabla: `horario_clase`

### Objetivo

Definir cuándo se dicta una materia para un grupo, en un aula, día, turno y periodo.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador del horario |
| gestion_academica_id | BIGINT | NOT NULL, FK | Gestión académica |
| grupo_id | BIGINT | NOT NULL, FK | Grupo de la clase |
| materia_id | BIGINT | NOT NULL, FK | Materia de la clase |
| aula_id | BIGINT | NOT NULL, FK | Aula asignada |
| dia_id | BIGINT | NOT NULL, FK | Día de la clase |
| turno_id | BIGINT | NOT NULL, FK | Turno de la clase |
| periodo_id | BIGINT | NOT NULL, FK | Periodo de la clase |
| hora_inicio | TIME | NOT NULL | Hora de inicio de clase |
| hora_fin | TIME | NOT NULL | Hora de fin de clase |
| activo | BOOLEAN | NOT NULL DEFAULT TRUE | Estado del horario |
| creado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

### Relaciones

- `gestion_academica` 1..* `horario_clase`
- `grupo` 1..* `horario_clase`
- `materia` 1..* `horario_clase`
- `aula` 1..* `horario_clase`
- `dia` 1..* `horario_clase`
- `turno` 1..* `horario_clase`
- `periodo` 1..* `horario_clase`
- `horario_clase` 1..* `asignacion_docente`
- `horario_clase` 1..* `asistencia_docente`
- `horario_clase` 1..* `asistencia_alumno`

### Reglas

- El administrador define días, turnos y periodos.
- El horario controla cuándo se puede marcar asistencia.
- Si se marca asistencia después de 30 minutos, se considera retraso.
- Pasado el horario de la clase, se marca falta automática.

---

## 6.9. Tabla: `asignacion_docente`

### Objetivo

Asignar docentes a materias, grupos y horarios.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador de la asignación |
| docente_id | BIGINT | NOT NULL, FK | Docente asignado |
| materia_id | BIGINT | NOT NULL, FK | Materia asignada |
| grupo_id | BIGINT | NOT NULL, FK | Grupo asignado |
| horario_clase_id | BIGINT | NOT NULL, FK | Horario asignado |
| gestion_academica_id | BIGINT | NOT NULL, FK | Gestión académica |
| activo | BOOLEAN | NOT NULL DEFAULT TRUE | Estado de la asignación |
| asignado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de asignación |

### Restricciones recomendadas

```sql
UNIQUE (docente_id, materia_id, grupo_id, horario_clase_id)
```

### Relaciones

- `docente` 1..* `asignacion_docente`
- `materia` 1..* `asignacion_docente`
- `grupo` 1..* `asignacion_docente`
- `horario_clase` 1..* `asignacion_docente`
- `gestion_academica` 1..* `asignacion_docente`

### Cardinalidad real

- `docente` *..* `materia`, resuelta mediante `asignacion_docente`.
- `docente` *..* `grupo`, resuelta mediante `asignacion_docente`.
- `materia` *..* `grupo`, relacionada mediante horario y asignación.

### Reglas

- Un docente puede ser asignado de 1 a 4 grupos.
- Un docente puede dar de 1 a 4 materias como máximo.
- El administrador asigna materias y grupos al docente.

---

# 7. Tablas de asistencia

---

## 7.1. Tabla: `asistencia_docente`

### Objetivo

Registrar la asistencia del docente al llegar y al finalizar su clase.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador de la asistencia |
| docente_id | BIGINT | NOT NULL, FK | Docente que marca asistencia |
| horario_clase_id | BIGINT | NOT NULL, FK | Horario de clase correspondiente |
| fecha | DATE | NOT NULL | Fecha de la clase |
| hora_entrada | TIMESTAMP | NULL | Hora exacta de llegada |
| hora_salida | TIMESTAMP | NULL | Hora exacta de finalización |
| estado_entrada | VARCHAR(30) | NOT NULL DEFAULT 'pendiente' | Estado de entrada |
| estado_salida | VARCHAR(30) | NULL | Estado de salida |
| marcado_por_usuario_id | BIGINT | NULL, FK | Usuario que registró la marca |
| observacion | TEXT | NULL | Observación |
| creado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| actualizado_en | TIMESTAMP | NULL | Fecha de actualización |

### Restricciones recomendadas

```sql
CHECK (estado_entrada IN ('pendiente', 'presente', 'retraso', 'falta'))
CHECK (estado_salida IS NULL OR estado_salida IN ('pendiente', 'finalizado'))
UNIQUE (docente_id, horario_clase_id, fecha)
```

### Relaciones

- `docente` 1..* `asistencia_docente`
- `horario_clase` 1..* `asistencia_docente`
- `usuario` 1..* `asistencia_docente` como usuario que registra

### Reglas

- El docente debe marcar su asistencia cuando llega a dar clase.
- El docente debe marcar la finalización de la clase.
- Solo puede marcar según el horario definido por el administrador.
- Puede marcar máximo 30 minutos después de empezar la clase.
- Después de los 30 minutos se registra como retraso.
- Pasado el horario se registra falta automática.
- El administrador puede ver todas las asistencias docentes de forma visual.

---

## 7.2. Tabla: `asistencia_alumno`

### Objetivo

Registrar la asistencia de los alumnos según el horario de clase.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador de la asistencia |
| alumno_id | BIGINT | NOT NULL, FK | Alumno asociado |
| horario_clase_id | BIGINT | NOT NULL, FK | Horario de clase correspondiente |
| docente_id | BIGINT | NULL, FK | Docente que toma asistencia, si corresponde |
| fecha | DATE | NOT NULL | Fecha de la clase |
| hora_marcada | TIMESTAMP | NULL | Hora exacta de asistencia |
| estado_asistencia | VARCHAR(30) | NOT NULL DEFAULT 'pendiente' | Estado de asistencia |
| registrado_por_usuario_id | BIGINT | NULL, FK | Usuario que registró la asistencia |
| observacion | TEXT | NULL | Observación |
| creado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| actualizado_en | TIMESTAMP | NULL | Fecha de actualización |

### Restricciones recomendadas

```sql
CHECK (estado_asistencia IN ('pendiente', 'presente', 'retraso', 'falta'))
UNIQUE (alumno_id, horario_clase_id, fecha)
```

### Relaciones

- `alumno` 1..* `asistencia_alumno`
- `horario_clase` 1..* `asistencia_alumno`
- `docente` 1..* `asistencia_alumno`
- `usuario` 1..* `asistencia_alumno` como usuario registrador

### Reglas

- El docente puede tomar asistencia a sus alumnos.
- El alumno puede marcar su asistencia.
- El docente solo puede ver asistencia de sus propios alumnos.
- El administrador puede ver asistencia de todos los alumnos.
- El alumno solo puede ver sus propias asistencias.
- La asistencia se basa en el horario definido por el administrador.
- Se puede marcar máximo 30 minutos después de empezar la clase.
- Después de los 30 minutos se registra retraso.
- Pasado el horario de la clase se registra falta automática.

---

# 8. Tablas de exámenes, preguntas, respuestas y notas

---

## 8.1. Tabla: `examen`

### Objetivo

Guardar los exámenes/parciales de una gestión académica.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador del examen |
| gestion_academica_id | BIGINT | NOT NULL, FK | Gestión académica |
| numero_parcial | INTEGER | NOT NULL | Número de parcial: 1, 2 o 3 |
| titulo | VARCHAR(150) | NOT NULL | Título del examen |
| descripcion | TEXT | NULL | Descripción del examen |
| habilitado | BOOLEAN | NOT NULL DEFAULT FALSE | Indica si el examen está habilitado |
| fecha_inicio | TIMESTAMP | NULL | Fecha y hora de inicio habilitada |
| fecha_fin | TIMESTAMP | NULL | Fecha y hora de cierre |
| creado_por_usuario_id | BIGINT | NOT NULL, FK | Administrador que crea el examen |
| creado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| actualizado_en | TIMESTAMP | NULL | Fecha de actualización |

### Restricciones recomendadas

```sql
CHECK (numero_parcial IN (1, 2, 3))
UNIQUE (gestion_academica_id, numero_parcial)
```

### Relaciones

- `gestion_academica` 1..* `examen`
- `usuario` 1..* `examen` como creador
- `examen` 1..* `examen_materia_porcentaje`
- `examen` 1..* `pregunta`
- `examen` 1..* `intento_examen`

### Reglas

- El alumno debe dar 3 exámenes en una gestión.
- Solo existen 3 parciales por gestión.
- El administrador crea y habilita exámenes.
- El alumno solo puede dar examen si está habilitado.

---

## 8.2. Tabla: `examen_materia_porcentaje`

### Objetivo

Guardar el porcentaje que cada materia tiene dentro de un examen.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador |
| examen_id | BIGINT | NOT NULL, FK | Examen asociado |
| materia_id | BIGINT | NOT NULL, FK | Materia asociada |
| porcentaje | NUMERIC(5,2) | NOT NULL | Porcentaje de la materia dentro del examen |

### Restricciones recomendadas

```sql
CHECK (porcentaje >= 0 AND porcentaje <= 100)
UNIQUE (examen_id, materia_id)
```

### Relaciones

- `examen` 1..* `examen_materia_porcentaje`
- `materia` 1..* `examen_materia_porcentaje`

### Regla

- La suma de porcentajes por examen debe ser exactamente 100%.
- Ejemplo definido: Física 25%, Matemáticas 30%, Computación 30%, Inglés 15%.
- Esta validación puede implementarse con lógica en backend o trigger en PostgreSQL.

---

## 8.3. Tabla: `pregunta`

### Objetivo

Guardar las preguntas de selección múltiple cargadas por el administrador.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador de la pregunta |
| examen_id | BIGINT | NOT NULL, FK | Examen al que pertenece |
| materia_id | BIGINT | NOT NULL, FK | Materia de la pregunta |
| enunciado | TEXT | NOT NULL | Texto de la pregunta |
| tipo_pregunta | VARCHAR(50) | NOT NULL DEFAULT 'seleccion_multiple' | Tipo de pregunta |
| puntaje | NUMERIC(5,2) | NOT NULL DEFAULT 1 | Puntaje de la pregunta |
| activa | BOOLEAN | NOT NULL DEFAULT TRUE | Estado de la pregunta |
| creado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

### Restricciones recomendadas

```sql
CHECK (tipo_pregunta = 'seleccion_multiple')
CHECK (puntaje > 0)
```

### Relaciones

- `examen` 1..* `pregunta`
- `materia` 1..* `pregunta`
- `pregunta` 1..* `opcion_pregunta`
- `pregunta` 1..* `respuesta_alumno`

### Reglas

- Las respuestas serán siempre de selección múltiple.
- Cada pregunta debe tener opciones de respuesta.
- Debe existir una opción correcta.
- Cada examen debe tener preguntas de Física, Matemáticas, Computación e Inglés.
- El documento original indica 10 preguntas por cada área.

---

## 8.4. Tabla: `opcion_pregunta`

### Objetivo

Guardar las opciones de respuesta de cada pregunta.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador de la opción |
| pregunta_id | BIGINT | NOT NULL, FK | Pregunta asociada |
| texto_opcion | TEXT | NOT NULL | Texto de la opción |
| es_correcta | BOOLEAN | NOT NULL DEFAULT FALSE | Indica si es la respuesta correcta |
| orden | INTEGER | NOT NULL | Orden de visualización |

### Restricciones recomendadas

```sql
UNIQUE (pregunta_id, orden)
```

### Relaciones

- `pregunta` 1..* `opcion_pregunta`
- `opcion_pregunta` 1..* `respuesta_alumno`

### Regla

- Cada pregunta debe tener opciones de selección múltiple.
- Debe existir una opción correcta por pregunta.
- La regla de una sola opción correcta puede validarse con lógica del backend o índice parcial en PostgreSQL.

---

## 8.5. Tabla: `intento_examen`

### Objetivo

Registrar el examen rendido por cada alumno para cada parcial.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador del intento |
| alumno_id | BIGINT | NOT NULL, FK | Alumno que rinde el examen |
| examen_id | BIGINT | NOT NULL, FK | Examen rendido |
| fecha_inicio | TIMESTAMP | NULL | Fecha y hora en que inició el examen |
| fecha_fin | TIMESTAMP | NULL | Fecha y hora en que finalizó el examen |
| estado | VARCHAR(30) | NOT NULL DEFAULT 'pendiente' | Estado del intento |
| nota_total | NUMERIC(5,2) | NULL | Nota total del examen/parcial |
| creado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

### Restricciones recomendadas

```sql
CHECK (estado IN ('pendiente', 'en_progreso', 'finalizado', 'anulado'))
CHECK (nota_total IS NULL OR (nota_total >= 0 AND nota_total <= 100))
UNIQUE (alumno_id, examen_id)
```

### Relaciones

- `alumno` 1..* `intento_examen`
- `examen` 1..* `intento_examen`
- `intento_examen` 1..* `respuesta_alumno`
- `intento_examen` 1..* `nota_examen_materia`
- `intento_examen` 1..0..1 `nota_parcial`

### Reglas

- Un alumno solo puede tener un intento por examen definido.
- Solo puede dar examen si el examen está habilitado.
- El alumno debe rendir 3 exámenes por gestión.

---

## 8.6. Tabla: `respuesta_alumno`

### Objetivo

Guardar las respuestas seleccionadas por el alumno en un examen.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador de la respuesta |
| intento_examen_id | BIGINT | NOT NULL, FK | Intento de examen |
| pregunta_id | BIGINT | NOT NULL, FK | Pregunta respondida |
| opcion_pregunta_id | BIGINT | NOT NULL, FK | Opción seleccionada |
| es_correcta | BOOLEAN | NULL | Resultado de la respuesta |
| respondido_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha y hora de respuesta |

### Restricciones recomendadas

```sql
UNIQUE (intento_examen_id, pregunta_id)
```

### Relaciones

- `intento_examen` 1..* `respuesta_alumno`
- `pregunta` 1..* `respuesta_alumno`
- `opcion_pregunta` 1..* `respuesta_alumno`

### Regla

- Cada pregunta debe tener una respuesta seleccionada por el alumno.
- La respuesta seleccionada debe pertenecer a la pregunta correspondiente.

---

## 8.7. Tabla: `nota_examen_materia`

### Objetivo

Guardar la nota obtenida por el alumno en cada materia dentro de un examen.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador |
| intento_examen_id | BIGINT | NOT NULL, FK | Intento de examen |
| materia_id | BIGINT | NOT NULL, FK | Materia evaluada |
| nota | NUMERIC(5,2) | NOT NULL | Nota obtenida en la materia |
| porcentaje_aplicado | NUMERIC(5,2) | NOT NULL | Porcentaje aplicado a esa materia |
| nota_ponderada | NUMERIC(5,2) | NOT NULL | Nota ponderada resultante |

### Restricciones recomendadas

```sql
CHECK (nota >= 0 AND nota <= 100)
CHECK (porcentaje_aplicado >= 0 AND porcentaje_aplicado <= 100)
CHECK (nota_ponderada >= 0 AND nota_ponderada <= 100)
UNIQUE (intento_examen_id, materia_id)
```

### Relaciones

- `intento_examen` 1..* `nota_examen_materia`
- `materia` 1..* `nota_examen_materia`

### Regla

- La nota del parcial se calcula aplicando los porcentajes de las materias.
- Las materias evaluadas son Física, Matemáticas, Computación e Inglés.

---

## 8.8. Tabla: `nota_parcial`

### Objetivo

Guardar la nota final de cada parcial por alumno.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador de la nota parcial |
| alumno_id | BIGINT | NOT NULL, FK | Alumno evaluado |
| examen_id | BIGINT | NOT NULL, FK | Examen/parcial asociado |
| intento_examen_id | BIGINT | NOT NULL, FK, UNIQUE | Intento de examen asociado |
| numero_parcial | INTEGER | NOT NULL | Número de parcial |
| nota | NUMERIC(5,2) | NOT NULL | Nota del parcial |
| registrado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de registro |

### Restricciones recomendadas

```sql
CHECK (numero_parcial IN (1, 2, 3))
CHECK (nota >= 0 AND nota <= 100)
UNIQUE (alumno_id, numero_parcial, examen_id)
```

### Relaciones

- `alumno` 1..* `nota_parcial`
- `examen` 1..* `nota_parcial`
- `intento_examen` 1..0..1 `nota_parcial`

### Regla

- Solo existen 3 parciales por alumno en una gestión.

---

## 8.9. Tabla: `promedio_final`

### Objetivo

Guardar el promedio final del alumno y su estado aprobado/reprobado.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador |
| alumno_id | BIGINT | NOT NULL, FK | Alumno evaluado |
| gestion_academica_id | BIGINT | NOT NULL, FK | Gestión académica |
| parcial_1 | NUMERIC(5,2) | NULL | Nota del primer parcial |
| parcial_2 | NUMERIC(5,2) | NULL | Nota del segundo parcial |
| parcial_3 | NUMERIC(5,2) | NULL | Nota del tercer parcial |
| promedio | NUMERIC(5,2) | NULL | Promedio final |
| estado_final | VARCHAR(30) | NULL | Aprobado o reprobado |
| calculado_en | TIMESTAMP | NULL | Fecha de cálculo |

### Restricciones recomendadas

```sql
CHECK (parcial_1 IS NULL OR (parcial_1 >= 0 AND parcial_1 <= 100))
CHECK (parcial_2 IS NULL OR (parcial_2 >= 0 AND parcial_2 <= 100))
CHECK (parcial_3 IS NULL OR (parcial_3 >= 0 AND parcial_3 <= 100))
CHECK (promedio IS NULL OR (promedio >= 0 AND promedio <= 100))
CHECK (estado_final IS NULL OR estado_final IN ('aprobado', 'reprobado'))
UNIQUE (alumno_id, gestion_academica_id)
```

### Relaciones

- `alumno` 1..0..1 `promedio_final` por gestión
- `gestion_academica` 1..* `promedio_final`

### Reglas

- El promedio final se calcula así:

```text
Promedio final = (Parcial 1 + Parcial 2 + Parcial 3) / 3
```

- Si promedio >= 60, el estado es aprobado.
- Si promedio < 60, el estado es reprobado.
- El promedio se usa para priorizar admisión por mayor nota.

---

# 9. Tablas de reportes, comandos de voz y carga masiva

---

## 9.1. Tabla: `reporte_generado`

### Objetivo

Guardar el historial de reportes generados desde el sistema.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador del reporte |
| usuario_id | BIGINT | NOT NULL, FK | Usuario que generó el reporte |
| tipo_reporte | VARCHAR(100) | NOT NULL | Tipo de reporte generado |
| formato_exportacion | VARCHAR(20) | NULL | PDF o Excel |
| parametros | JSONB | NULL | Parámetros usados para generar el reporte |
| archivo_url | TEXT | NULL | URL o ruta del archivo exportado si se guarda |
| generado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de generación |

### Restricciones recomendadas

```sql
CHECK (formato_exportacion IS NULL OR formato_exportacion IN ('pdf', 'excel'))
```

### Relaciones

- `usuario` 1..* `reporte_generado`

### Reportes obligatorios

Debe permitir reportes de:

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

---

## 9.2. Tabla: `comando_voz_reporte`

### Objetivo

Guardar comandos de voz usados para generar reportes.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador del comando |
| usuario_id | BIGINT | NOT NULL, FK | Usuario administrador que usó el comando |
| texto_detectado | TEXT | NOT NULL | Texto convertido desde voz por Web Speech API |
| intencion_detectada | VARCHAR(100) | NULL | Intención interpretada por el sistema |
| reporte_generado_id | BIGINT | NULL, FK | Reporte generado a partir del comando |
| creado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de uso del comando |

### Relaciones

- `usuario` 1..* `comando_voz_reporte`
- `reporte_generado` 1..0..1 `comando_voz_reporte`

### Regla

- Se usará Web Speech API en el frontend para convertir voz a texto.
- El sistema interpretará el texto para generar reportes.

---

## 9.3. Tabla: `carga_masiva`

### Objetivo

Registrar cargas masivas realizadas desde archivos Excel o CSV.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador de la carga |
| usuario_id | BIGINT | NOT NULL, FK | Usuario administrador que realizó la carga |
| tipo_carga | VARCHAR(50) | NOT NULL | Tipo de carga realizada |
| nombre_archivo | VARCHAR(200) | NOT NULL | Nombre del archivo cargado |
| formato_archivo | VARCHAR(20) | NOT NULL | Excel o CSV |
| total_registros | INTEGER | NOT NULL DEFAULT 0 | Total de registros leídos |
| registros_exitosos | INTEGER | NOT NULL DEFAULT 0 | Registros cargados correctamente |
| registros_error | INTEGER | NOT NULL DEFAULT 0 | Registros con error |
| estado | VARCHAR(30) | NOT NULL DEFAULT 'procesando' | Estado de la carga |
| creado_en | TIMESTAMP | NOT NULL DEFAULT CURRENT_TIMESTAMP | Fecha de carga |
| finalizado_en | TIMESTAMP | NULL | Fecha de finalización |

### Restricciones recomendadas

```sql
CHECK (formato_archivo IN ('excel', 'csv'))
CHECK (estado IN ('procesando', 'finalizado', 'con_errores', 'fallido'))
```

### Relaciones

- `usuario` 1..* `carga_masiva`
- `carga_masiva` 1..* `detalle_carga_masiva`

### Regla

- La administración puede entregar datos por lotes mediante Excel o CSV.
- La carga debe poder realizarse desde la aplicación web.

---

## 9.4. Tabla: `detalle_carga_masiva`

### Objetivo

Guardar el detalle de cada fila procesada en una carga masiva.

### Atributos

| Campo | Tipo de dato | Restricción | Descripción |
|---|---|---|---|
| id | BIGSERIAL | PK | Identificador del detalle |
| carga_masiva_id | BIGINT | NOT NULL, FK | Carga masiva asociada |
| numero_fila | INTEGER | NOT NULL | Número de fila procesada |
| estado | VARCHAR(30) | NOT NULL | Estado de la fila |
| mensaje_error | TEXT | NULL | Mensaje de error si corresponde |
| datos_fila | JSONB | NULL | Datos originales de la fila |

### Restricciones recomendadas

```sql
CHECK (estado IN ('exitoso', 'error'))
```

### Relaciones

- `carga_masiva` 1..* `detalle_carga_masiva`

---

# 10. Resumen general de relaciones y cardinalidades

## 10.1. Seguridad y usuarios

| Relación | Cardinalidad | Descripción |
|---|---|---|
| `rol` - `usuario` | 1..* | Un rol puede tener muchos usuarios; cada usuario tiene un rol |
| `persona` - `usuario` | 1..0..1 | Una persona puede tener una cuenta de usuario |
| `usuario` - `usuario` | 1..* | Un administrador puede crear muchos usuarios |
| `persona` - `administrador` | 1..0..1 | Una persona puede ser administrador |
| `persona` - `docente` | 1..0..1 | Una persona puede ser docente |
| `persona` - `postulante` | 1..0..1 | Una persona puede ser postulante |
| `persona` - `alumno` | 1..0..1 | Una persona puede ser alumno |

---

## 10.2. Postulación, pago y admisión

| Relación | Cardinalidad | Descripción |
|---|---|---|
| `gestion_academica` - `postulante` | 1..* | Una gestión tiene muchos postulantes |
| `postulante` - `documento_postulante` | 1..* | Un postulante puede tener documentos |
| `postulante` - `pago_stripe` | 1..0..1 | Un postulante puede tener un pago Stripe |
| `postulante` - `postulacion` | 1..1 | Todo postulante debe tener postulación |
| `postulante` - `alumno` | 1..0..1 | Un postulante puede convertirse en alumno |
| `carrera` - `cupo_carrera` | 1..* | Una carrera tiene cupos por gestión |
| `gestion_academica` - `cupo_carrera` | 1..* | Una gestión define cupos por carrera |
| `carrera` - `postulacion` | 1..* | Una carrera puede ser primera opción, segunda opción o carrera asignada |

---

## 10.3. Grupos, horarios y asignaciones

| Relación | Cardinalidad | Descripción |
|---|---|---|
| `gestion_academica` - `grupo` | 1..* | Una gestión tiene muchos grupos |
| `grupo` - `alumno` | *..* | Un grupo tiene muchos alumnos y se resuelve con `grupo_alumno` |
| `docente` - `materia` | *..* | Un docente puede dar varias materias y una materia puede tener varios docentes, se resuelve con `asignacion_docente` |
| `docente` - `grupo` | *..* | Un docente puede estar en varios grupos y un grupo puede tener varios docentes |
| `grupo` - `horario_clase` | 1..* | Un grupo tiene muchos horarios |
| `materia` - `horario_clase` | 1..* | Una materia aparece en muchos horarios |
| `aula` - `horario_clase` | 1..* | Un aula puede tener muchos horarios |
| `dia` - `horario_clase` | 1..* | Un día puede tener muchos horarios |
| `turno` - `periodo` | 1..* | Un turno tiene muchos periodos |
| `periodo` - `horario_clase` | 1..* | Un periodo puede usarse en muchos horarios |
| `horario_clase` - `asignacion_docente` | 1..* | Un horario puede tener asignaciones docentes |

---

## 10.4. Asistencia

| Relación | Cardinalidad | Descripción |
|---|---|---|
| `docente` - `asistencia_docente` | 1..* | Un docente tiene muchas asistencias |
| `alumno` - `asistencia_alumno` | 1..* | Un alumno tiene muchas asistencias |
| `horario_clase` - `asistencia_docente` | 1..* | Un horario genera registros de asistencia docente |
| `horario_clase` - `asistencia_alumno` | 1..* | Un horario genera registros de asistencia alumno |
| `docente` - `asistencia_alumno` | 1..* | Un docente puede tomar asistencia de muchos alumnos |

---

## 10.5. Exámenes y notas

| Relación | Cardinalidad | Descripción |
|---|---|---|
| `gestion_academica` - `examen` | 1..* | Una gestión tiene hasta 3 exámenes/parciales |
| `examen` - `materia` | *..* | Un examen tiene varias materias y cada materia está en varios exámenes, se resuelve con `examen_materia_porcentaje` |
| `examen` - `pregunta` | 1..* | Un examen tiene muchas preguntas |
| `materia` - `pregunta` | 1..* | Una materia tiene muchas preguntas |
| `pregunta` - `opcion_pregunta` | 1..* | Una pregunta tiene varias opciones |
| `alumno` - `intento_examen` | 1..* | Un alumno puede tener intentos de examen |
| `examen` - `intento_examen` | 1..* | Un examen puede ser rendido por muchos alumnos |
| `intento_examen` - `respuesta_alumno` | 1..* | Un intento tiene muchas respuestas |
| `intento_examen` - `nota_examen_materia` | 1..* | Un intento tiene notas por materia |
| `intento_examen` - `nota_parcial` | 1..0..1 | Un intento genera una nota parcial |
| `alumno` - `promedio_final` | 1..0..1 por gestión | Un alumno tiene un promedio final por gestión |

---

# 11. Reglas de negocio que debe respetar el backend

## 11.1. Reglas de roles

1. Solo existen 3 roles: administrador, docente y alumno.
2. No deben crearse roles adicionales.
3. El administrador tiene acceso completo.
4. El administrador puede crear otros administradores.
5. El docente tiene acceso limitado.
6. El alumno tiene acceso limitado.

---

## 11.2. Reglas de postulante y alumno

1. El postulante debe registrar sus datos personales.
2. La cédula de identidad no puede duplicarse.
3. El correo no puede duplicarse.
4. Debe subir imagen del título de bachiller.
5. La imagen se guarda en Cloudinary.
6. El administrador valida los requisitos.
7. Si cumple requisitos, debe pagar mediante Stripe.
8. El pago es obligatorio.
9. El administrador valida el pago.
10. Solo después de validar requisitos y pago, se da acceso como alumno.
11. El sistema genera automáticamente el código del alumno.

---

## 11.3. Reglas del código de alumno

Formato:

```text
AÑO + GESTION + CEDULA
```

Ejemplo:

```text
2026 + 1 + 13541539 = 2026113541539
```

Reglas:

1. El año se toma del año actual.
2. La gestión solo puede ser 1 o 2.
3. Gestión 1 corresponde al primer semestre.
4. Gestión 2 corresponde al segundo semestre.
5. La cédula se concatena al final.
6. El código no se escribe manualmente.
7. El código debe ser único.
8. El alumno usa ese código para iniciar sesión.

---

## 11.4. Reglas de carreras y cupos

1. Cada carrera tiene cupos por gestión.
2. El alumno debe elegir dos carreras obligatoriamente.
3. Las dos carreras deben ser diferentes.
4. Se prioriza siempre por mayor nota.
5. Primero se intenta asignar a la primera opción.
6. Si la primera opción está llena, se intenta asignar a la segunda opción.
7. Si ambas están llenas, se asigna a la carrera que tenga menos personas.

---

## 11.5. Reglas de grupos

1. Cada grupo admite máximo 70 alumnos.
2. El sistema calcula la cantidad de grupos necesarios.
3. Fórmula:

```text
Cantidad de grupos = techo(total inscritos / 70)
```

---

## 11.6. Reglas de docentes

1. El docente debe ser profesional en el área.
2. El docente debe tener maestría.
3. El docente debe tener diplomado en educación superior.
4. El docente puede ser asignado de 1 a 4 grupos.
5. El docente puede dar de 1 a 4 materias como máximo.
6. El docente marca asistencia de entrada.
7. El docente marca salida o finalización de clase.
8. El docente puede tomar asistencia a sus alumnos.
9. El docente solo ve la asistencia de sus propios alumnos.

---

## 11.7. Reglas de horarios y asistencia

1. El administrador define días, turnos y periodos.
2. Cada periodo dura 45 minutos.
3. El aula solo guarda ubicación.
4. La asistencia depende del horario definido por el administrador.
5. Docentes y alumnos pueden marcar asistencia máximo 30 minutos después de iniciar clase.
6. Después de 30 minutos se marca como retraso.
7. Pasado el horario de la clase se marca automáticamente como falta.
8. El administrador puede ver asistencia de todos los docentes y alumnos.
9. El docente solo puede ver asistencia de sus alumnos.
10. El alumno solo puede ver su propia asistencia.

---

## 11.8. Reglas de exámenes

1. El administrador crea las preguntas.
2. Las preguntas son de selección múltiple.
3. Cada pregunta tiene opciones de respuesta.
4. Cada pregunta debe tener una respuesta correcta.
5. El administrador habilita el examen.
6. El alumno solo rinde examen si está habilitado.
7. El alumno debe rendir 3 exámenes por gestión.
8. Solo existen 3 parciales por gestión.
9. Cada examen contiene preguntas de Física, Matemáticas, Computación e Inglés.
10. El documento original indica 10 preguntas por cada área.
11. Las notas deben estar entre 0 y 100.
12. Las materias tienen porcentajes dentro del examen.
13. La suma de porcentajes debe ser 100%.

---

## 11.9. Reglas de notas y promedio

1. Se registra nota por materia en cada examen.
2. Se calcula nota ponderada según el porcentaje de cada materia.
3. Se calcula una nota total del parcial.
4. El promedio final se calcula así:

```text
Promedio final = (Parcial 1 + Parcial 2 + Parcial 3) / 3
```

5. Si el promedio final es mayor o igual a 60, el alumno queda aprobado.
6. Si el promedio final es menor a 60, el alumno queda reprobado.
7. El promedio final se usa para priorizar la admisión por cupos.

---

# 12. Orden recomendado de creación de tablas

Para evitar errores por llaves foráneas, se recomienda crear las tablas en este orden:

1. `rol`
2. `persona`
3. `usuario`
4. `administrador`
5. `docente`
6. `gestion_academica`
7. `carrera`
8. `cupo_carrera`
9. `postulante`
10. `documento_postulante`
11. `pago_stripe`
12. `postulacion`
13. `alumno`
14. `materia`
15. `grupo`
16. `grupo_alumno`
17. `aula`
18. `dia`
19. `turno`
20. `periodo`
21. `horario_clase`
22. `asignacion_docente`
23. `asistencia_docente`
24. `asistencia_alumno`
25. `examen`
26. `examen_materia_porcentaje`
27. `pregunta`
28. `opcion_pregunta`
29. `intento_examen`
30. `respuesta_alumno`
31. `nota_examen_materia`
32. `nota_parcial`
33. `promedio_final`
34. `reporte_generado`
35. `comando_voz_reporte`
36. `carga_masiva`
37. `detalle_carga_masiva`

---

# 13. Tablas finales del diseño

La base de datos queda compuesta por las siguientes tablas:

1. `rol`
2. `persona`
3. `usuario`
4. `administrador`
5. `docente`
6. `alumno`
7. `gestion_academica`
8. `carrera`
9. `cupo_carrera`
10. `postulante`
11. `documento_postulante`
12. `pago_stripe`
13. `postulacion`
14. `materia`
15. `grupo`
16. `grupo_alumno`
17. `aula`
18. `dia`
19. `turno`
20. `periodo`
21. `horario_clase`
22. `asignacion_docente`
23. `asistencia_docente`
24. `asistencia_alumno`
25. `examen`
26. `examen_materia_porcentaje`
27. `pregunta`
28. `opcion_pregunta`
29. `intento_examen`
30. `respuesta_alumno`
31. `nota_examen_materia`
32. `nota_parcial`
33. `promedio_final`
34. `reporte_generado`
35. `comando_voz_reporte`
36. `carga_masiva`
37. `detalle_carga_masiva`

---

# 14. Observaciones importantes para implementación del backend

1. La validación de que un docente tenga máximo 4 grupos debe controlarse desde el backend o mediante trigger.
2. La validación de que un docente tenga máximo 4 materias debe controlarse desde el backend o mediante trigger.
3. La validación de que un grupo no supere 70 alumnos debe controlarse desde el backend o mediante trigger.
4. La validación de que los porcentajes de materias sumen 100% por examen debe controlarse desde el backend o mediante trigger.
5. La generación automática del código del alumno debe realizarse cuando el administrador habilite al postulante como alumno.
6. El estado de asistencia como presente, retraso o falta debe calcularse comparando la hora marcada con el horario de clase.
7. Las faltas automáticas pueden generarse mediante tarea programada en backend o proceso interno ejecutado después del horario de clase.
8. Stripe debe integrarse con `pago_stripe`.
9. Cloudinary debe integrarse con `documento_postulante`.
10. Firebase Authentication debe integrarse con `usuario.firebase_uid`.
11. Web Speech API debe integrarse con `comando_voz_reporte` y `reporte_generado`.
12. Los reportes en PDF y Excel se pueden generar desde consultas que usen las tablas principales.
13. El backend debe controlar permisos según el rol del usuario autenticado.

---

# 15. Resumen final

Esta base de datos permite construir la estructura del backend para el sistema CUP de la FICCT.

Cubre:

- Los 3 roles permitidos.
- Administradores con acceso completo.
- Docentes con permisos limitados.
- Alumnos con acceso a perfil, horarios, asistencia y exámenes habilitados.
- Postulantes con requisitos, documentos en Cloudinary y pago obligatorio con Stripe.
- Generación automática del código de alumno.
- Gestiones académicas por semestre.
- Carreras y cupos por gestión.
- Priorización de admisión por mayor nota.
- Asignación por primera opción, segunda opción o carrera con menos personas.
- Grupos con máximo 70 alumnos.
- Horarios definidos por días, turnos y periodos de 45 minutos.
- Aulas con ubicación.
- Asistencia obligatoria para docentes y alumnos.
- Control de presente, retraso y falta automática.
- Exámenes de selección múltiple.
- Preguntas por materia.
- Porcentajes por materia.
- Notas por materia, nota parcial y promedio final.
- Estado final aprobado o reprobado.
- Reportes obligatorios.
- Exportación a PDF y Excel.
- Comandos de voz usando Web Speech API.
- Cargas masivas mediante Excel o CSV.

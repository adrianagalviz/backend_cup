# Instrucciones del Proyecto: Admisión Universitaria (CUP) - FICCT

Este documento describe de manera breve el proyecto de Admisión Universitaria (CUP) para la FICCT, su estructura organizativa, los actores involucrados (roles) y los pasos necesarios para levantar el sistema en tu entorno local.

---

## 1. Descripción del Proyecto

La **Aplicación Web de Admisión Universitaria (CUP) para la FICCT** (Facultad de Ingeniería en Ciencias de la Computación y Telecomunicaciones) es un sistema diseñado para automatizar, controlar y administrar de manera íntegra el proceso de ingreso preuniversitario.

El sistema gestiona de extremo a extremo las siguientes funciones clave:
*   **Registro y Requisitos**: Formulario de postulación con validaciones, subida de la foto del título de bachiller a Cloudinary y vinculación de cuentas de Google usando Firebase Authentication.
*   **Pasarela de Pago**: Integración obligatoria con Stripe para procesar los pagos de inscripción.
*   **Admisión y Código del Alumno**: Asignación automática de acceso a postulantes verificados y generación de su código institucional único con el formato: `AÑO + GESTIÓN + CÉDULA DE IDENTIDAD` (ej. `2026113541539`).
*   **Gestión Académica**: Distribución de alumnos en grupos (máximo 70 estudiantes por aula), turnos, periodos de clase de 45 minutos y asignación de carga docente.
*   **Asistencia**: Registro estricto de asistencia para docentes y alumnos con control de tolerancia (hasta 30 minutos a tiempo, posterior es retraso, y faltas automáticas tras finalizar la sesión).
*   **Exámenes y Calificaciones**: Registro de notas para tres parciales en las materias de Física, Matemáticas, Computación e Inglés, calculando el promedio final para definir el estado de aprobación (APROBADO >= 60).
*   **Asignación de Cupos**: Algoritmo para asignar vacantes a carreras por prioridad de opciones y orden de mérito académico (calificación más alta primero).
*   **Módulo de Reportes**: Exportación a PDF y Excel de reportes generales, estadísticas y un sistema interactivo de reportes mediante comandos de voz utilizando la Web Speech API.

---

## 2. Actores del Sistema (Roles)

La aplicación maneja estrictamente **tres roles** de usuario, cada uno con responsabilidades definidas:

1.  **Administrador**:
    *   Acceso total e irrestricto al panel y funciones del sistema.
    *   Gestión de administradores, docentes, alumnos y postulantes.
    *   Validación de requisitos (documentación) y confirmación de pagos de postulantes.
    *   Habilitación del código institucional para convertir a un postulante en alumno oficial.
    *   Definición de turnos, horarios, periodos, materias, grupos y asignaciones de docentes.
    *   Gestión de exámenes: creación de bancos de preguntas, asignación de pesos/porcentajes y activación de evaluaciones.
    *   Visualización de asistencias de todos los docentes y alumnos.
    *   Generación de reportes administrativos y consultas por voz.

2.  **Docente**:
    *   Acceso al perfil, carga horaria, materias y grupos asignados.
    *   Autogestión de su asistencia diaria (registro de entrada y salida de clases).
    *   Control y toma de asistencia individual de los alumnos de sus respectivos grupos.
    *   Visualización del récord de asistencia exclusivo de sus alumnos asignados.

3.  **Alumno**:
    *   Inicio de sesión seguro mediante su código institucional generado automáticamente.
    *   Visualización de su perfil personal, horarios de clase y su historial de asistencia.
    *   Registro de su propia asistencia a clases en el horario permitido.
    *   Resolución en línea de los exámenes parciales habilitados por el administrador.

---

## 3. Estructura del Proyecto

El backend está desarrollado sobre **Laravel 12** y utiliza **PostgreSQL** como motor de base de datos. La organización de carpetas y archivos clave es la siguiente:

*   [`app/Http/Controllers/Api/`](file:///c:/Users/PERSONAL/backend_cup/app/Http/Controllers/Api): Contiene los controladores que exponen los endpoints JSON de la API:
    *   `AuthController.php`: Manejo del login (tradicional, alumnos y Firebase) y logout.
    *   `ApplicantController.php` & `ApplicantDocumentController.php`: Procesamiento de datos y requisitos de postulantes.
    *   `PaymentController.php`: Flujo de integración y recepción de Webhooks de Stripe.
    *   `ApplicantConversionController.php`: Algoritmo de generación de código y conversión a alumno.
    *   `ClassroomGroupController.php` & `ClassScheduleController.php`: Creación y control de aulas, grupos, y horarios de clase.
    *   `TeacherController.php` & `TeacherAssignmentController.php`: CRUD de docentes y asignaciones de carga horaria.
    *   `StudentAttendanceController.php` & `TeacherAttendanceController.php`: Lógica de marcación y control de asistencias.
    *   `ExamController.php` & `StudentExamController.php`: Bancos de preguntas, habilitación y respuesta a exámenes.
    *   `ReportController.php`: Exportación a PDF/Excel y reconocimiento de comandos de voz.
*   [`app/Models/`](file:///c:/Users/PERSONAL/backend_cup/app/Models): Modelos Eloquent de la base de datos (ej. `PostulanteModel`, `AlumnoModel`, `DocenteModel`, `ExamenModel`, `AsistenciaAlumnoModel`, etc.).
*   [`routes/api.php`](file:///c:/Users/PERSONAL/backend_cup/routes/api.php): Declaración de rutas de la API bajo el prefijo `v1`. Cuenta con protecciones de autenticación (`auth.internal`) y restricción de accesos por roles (`role:administrador,docente,alumno`).
*   [`database/migrations/`](file:///c:/Users/PERSONAL/backend_cup/database/migrations): Estructura relacional de las tablas y llaves foráneas.
*   [`database/seeders/`](file:///c:/Users/PERSONAL/backend_cup/database/seeders): Inicializadores de datos (roles del sistema, materias obligatorias e información del Administrador inicial).
*   [`resources/`](file:///c:/Users/PERSONAL/backend_cup/resources): Archivos frontend (JS/CSS) utilizando TailwindCSS para assets precompilados mediante Vite.
*   [`docs/`](file:///c:/Users/PERSONAL/backend_cup/docs): Documentación auxiliar de requerimientos y guías de fases.

---

## 4. Requisitos del Entorno de Desarrollo

Antes de levantar el proyecto, asegúrate de tener instalados los siguientes componentes:
*   **PHP >= 8.2** (con soporte para PDO, PGSQL, BCMath, GD, OpenSSL y JSON).
*   **Composer** (gestor de paquetes de PHP).
*   **Node.js & npm** (para compilar y ejecutar los assets del frontend).
*   **PostgreSQL** (base de datos relacional).

---

## 5. Pasos para Levantar el Proyecto Localmente

Sigue esta guía secuencial de comandos para dejar el backend listo para su ejecución:

### Paso 1: Configurar el Archivo de Entorno
Crea tu archivo de variables de entorno `.env` copiando el archivo de ejemplo:
```powershell
copy .env.example .env
```

### Paso 2: Crear y Configurar la Base de Datos
1.  Ingresa a tu gestor de base de datos PostgreSQL.
2.  Crea una nueva base de datos llamada `cup_ficct`.
3.  Abre el archivo `.env` recién creado en la raíz del proyecto y ajusta las variables de conexión con tus credenciales locales:
    ```ini
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=cup_ficct
    DB_USERNAME=tu_usuario_postgres
    DB_PASSWORD=tu_contrasena_postgres
    ```

### Paso 3: Configurar Credenciales Externas (Opcional)
En tu `.env`, proporciona credenciales válidas en caso de que necesites probar servicios externos de manera local:
*   **Stripe**: `STRIPE_SECRET_KEY` y `STRIPE_WEBHOOK_SECRET`
*   **Cloudinary**: `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY` y `CLOUDINARY_API_SECRET`
*   **Firebase**: `FIREBASE_PROJECT_ID`

### Paso 4: Ejecutar la Configuración Automatizada (Setup)
El archivo `composer.json` cuenta con un script que realiza de forma automática: la descarga de dependencias PHP, generación de la clave de encriptación (`APP_KEY`), ejecución de las migraciones, descarga de paquetes npm y compilación de recursos frontend.
Corre el siguiente comando:
```powershell
composer run setup
```

### Paso 5: Poblar Datos Base (Seeders)
Ejecuta el comando para inicializar el sistema con los roles, materias predeterminadas y el usuario administrador inicial:
```powershell
php artisan db:seed
```

> [!NOTE]
> Por defecto, el Administrador Inicial se crea con las siguientes credenciales configuradas en tu `.env`:
> *   **Usuario (username)**: `admin`
> *   **Contraseña (password)**: `admin12345`
> *   **Email**: `admin@cupficct.local`

### Paso 6: Iniciar el Servidor de Desarrollo
Para arrancar el backend junto a todos los servicios auxiliares concurrentemente (el servidor Laravel, el escuchador de colas de trabajos en segundo plano, el visor interactivo de logs `pail` y el servidor de desarrollo de Vite), simplemente ejecuta:
```powershell
composer run dev
```

### Paso 7: Comprobar el Funcionamiento del Backend
Abre tu navegador o cliente de peticiones API y valida los siguientes endpoints para confirmar la operatividad:
*   **Verificar salud general**: [http://localhost:8000/api/v1/salud](http://localhost:8000/api/v1/salud)
*   **Verificar conexión a base de datos**: [http://localhost:8000/api/v1/conexion-postgresql](http://localhost:8000/api/v1/conexion-postgresql)

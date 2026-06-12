# Admisión Universitaria CUP — FICCT

Sistema de administración académica y del proceso de admisión preuniversitario (CUP) para la Facultad de Ingeniería en Ciencias de la Computación y Telecomunicaciones (FICCT).

---

## Stack Tecnológico

| Capa          | Tecnología                        | Puerto Dev     |
| ------------- | --------------------------------- | -------------- |
| Backend       | PHP 8.2+ (Laravel 12)             | :8000          |
| Frontend      | Vite + TailwindCSS v4             | :5173          |
| Base de Datos | PostgreSQL                        | :5432          |
| Pagos         | Stripe API                        | —              |
| Imágenes      | Cloudinary API                    | —              |
| Auth Externo  | Firebase Authentication (Google)  | —              |

---

## Requisitos Previos

*   **PHP >= 8.2** (con extensiones `pdo_pgsql`, `pgsql`, `bcmath`, `gd`, `openssl`, etc.)
*   **Composer** (gestor de dependencias PHP)
*   **Node.js >= 20.x & npm** (para compilar recursos con Vite)
*   **PostgreSQL** (servicio iniciado localmente en el puerto 5432)
*   Acceso a Internet (para validar credenciales de Stripe, Firebase y Cloudinary)

---

## Setup Rápido

> [¡IMPORTANTE!]
> **Creación de la Base de Datos:** El paso de crear la base de datos `cup_ficct` en PostgreSQL es obligatorio y **solo se realiza la primera vez** que configuras el proyecto en tu máquina local. En ejecuciones posteriores, solo necesitas iniciar el servidor de desarrollo (Paso 6).

```bash
# 1. Clonar el repositorio
git clone <repo-url>
cd backend_cup

# 2. Copiar y configurar variables de entorno
copy .env.example .env

# NOTA: Abre tu editor y edita el archivo .env con tus credenciales de PostgreSQL
# (DB_DATABASE=cup_ficct, DB_USERNAME, DB_PASSWORD) y de los servicios externos.

# 3. Crear la base de datos en PostgreSQL
# Abre tu cliente de PostgreSQL (pgAdmin, psql, DBeaver) y crea la base de datos:
# CREATE DATABASE cup_ficct;

# 4. Ejecutar script de configuración inicial (Instala Composer, NPM, genera APP_KEY y corre migraciones)
# Nota: Este paso requiere que la base de datos "cup_ficct" ya esté creada en tu servidor PostgreSQL.
composer run setup

# 5. Poblar base de datos (Seeders) con roles, materias e información del administrador inicial
php artisan db:seed

#6. Iniciar el Servidor de Desarrollo
composer run dev
```

---

## Acceso

*   **Backend API:** [http://localhost:8000/api/v1/](http://localhost:8000/api/v1/)
*   **Frontend Dev Server (Vite):** [http://localhost:5173](http://localhost:5173) (una vez iniciado `npm run dev`)
*   **Verificar salud general:** [http://localhost:8000/api/v1/salud](http://localhost:8000/api/v1/salud)
*   **Verificar conexión base de datos:** [http://localhost:8000/api/v1/conexion-postgresql](http://localhost:8000/api/v1/conexion-postgresql)

---

## Estructura del Proyecto

```text
backend_cup/
├── app/              # Código fuente (Modelos Eloquent, Controladores de la API, Helpers y Validadores)
│   ├── Http/Controllers/Api/  # Controladores REST del negocio (auth, pagos, alumnos, docentes, asistencias, reportes, etc.)
│   └── Models/       # Modelos relacionales ORM (Postulante, Alumno, Docente, Examen, etc.)
├── bootstrap/        # Archivos de arranque del Framework
├── config/           # Configuraciones de Laravel (database, filesystems, app, etc.)
├── database/         # Migraciones SQL y Seeders iniciales
├── docs/             # Requerimientos detallados y documentación de fases
├── public/           # Directorio público (Assets ya compilados)
├── resources/        # Archivos fuente del frontend (JS/CSS con TailwindCSS v4)
├── routes/           # Rutas del sistema (api.php para endpoints REST)
├── storage/          # Almacenamiento local (logs, caché, subidas temporales)
├── tests/            # Pruebas unitarias e integración
├── .env.example      # Plantilla de variables de entorno
├── composer.json     # Dependencias de PHP y scripts personalizados
├── package.json      # Dependencias de Node.js y compilador Vite
└── README.md         # Documentación Laravel original
```

---

## Módulos del Sistema

| Módulo | Descripción |
| --- | --- |
| **Autenticación (Auth)** | Inicio de sesión seguro tradicional, login de alumnos mediante código, login externo con Firebase/Google y control de sesiones. |
| **Postulantes** | Registro, modificación de postulantes y carga de requisitos (imagen del título de bachiller enviada a Cloudinary). |
| **Pagos (Stripe)** | Pasarela obligatoria para postulantes. Gestión de sesiones de pago, webhooks e historial de confirmación. |
| **Conversión a Alumno** | Validador automático de pago y requisitos que otorga acceso y autogenera el código de alumno (`AÑO + GESTIÓN + CI`). |
| **Gestión Académica** | Definición de periodos, turnos, aulas, materias, carreras, cupos por gestión y asignación automática de grupos (máximo 70 alumnos). |
| **Docentes y Asignaciones** | Contratación de profesionales con diplomado/maestría y asignación a grupos y materias. |
| **Asistencias** | Registro obligatorio para alumnos y docentes con control estricto de retrasos (30 min de tolerancia) y faltas automáticas. |
| **Exámenes y Notas** | Carga de preguntas, habilitación de exámenes por el administrador, realización por el alumno (opción múltiple) y cálculo del promedio de 3 parciales. |
| **Reportes y Comandos Voz** | Descarga en PDF y Excel de reportes y conversión de voz a texto (Web Speech API) para generar reportes dinámicos. |
| **Carga Masiva** | Procesamiento e importación masiva de postulantes/docentes desde archivos CSV y Excel. |
| **Dashboard** | Panel general que provee métricas clave del estado de cupos, asistencia y resultados de exámenes en tiempo real. |

---

## Comandos Esenciales

### Entorno de Desarrollo
```bash
# Levantar servidor Laravel, Vite, escuchador de colas y pail de forma concurrente
composer run dev

# Levantar de manera independiente el servidor de la API Laravel
php artisan serve

# Levantar el servidor de desarrollo de Vite para assets frontend
npm run dev

# Ejecutar el escuchador de trabajos en segundo plano (colas)
php artisan queue:listen
```

### Operación de Base de Datos y Caché
```bash
# Recrear todas las tablas ejecutando migraciones desde cero
php artisan migrate:fresh

# Recrear tablas y correr seeders iniciales
php artisan migrate:fresh --seed

# Ejecutar seeders para poblar datos iniciales únicamente
php artisan db:seed

# Limpiar caché de configuraciones cargadas de Laravel
php artisan config:clear

# Limpiar caché general de la aplicación
php artisan cache:clear

# Correr pruebas unitarias e integración de PHPUnit
composer run test
```

---

## Solución de Problemas Comunes

### Error de Conexión a Base de Datos (`PDOException: SQLSTATE[08006]`)
Ocurre cuando las credenciales de conexión en tu `.env` son incorrectas o el servicio de PostgreSQL está apagado.
**Solución:**
1.  Verifica que PostgreSQL esté ejecutándose en tu puerto local `5432`.
2.  Corrobora que las credenciales (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) coincidan con tu base de datos local.
3.  Haz una consulta al endpoint de diagnóstico: [http://localhost:8000/api/v1/conexion-postgresql](http://localhost:8000/api/v1/conexion-postgresql).

### Cambios en Estilos Frontend no se Reflejan
Si haces cambios en TailwindCSS v4 y el navegador no los muestra:
**Solución:**
1.  Asegúrate de que `npm run dev` esté activo para compilar en tiempo real.
2.  Si estás desplegando o finalizando cambios, genera la compilación final optimizada:
    ```bash
    npm run build
    ```

---

## Licencia

Proyecto académico — Facultad de Ingeniería en Ciencias de la Computación y Telecomunicaciones (FICCT). Admisión Universitaria (CUP).

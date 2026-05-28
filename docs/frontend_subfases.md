# frontend_subfases.md

# Plan definitivo por fases y subfases del Frontend - Aplicación Web de Admisión Universitaria (CUP) para la FICCT

## 1. Objetivo del archivo

Este archivo define el plan definitivo, detallado y profesional para construir el frontend de la **Aplicación Web de Admisión Universitaria (CUP) para la FICCT**.

El frontend será desarrollado con:

- React JS.
- Vite.
- Node 22.22.2.
- Tailwind CSS versión 4.
- Librerías de apoyo para acelerar la creación de vistas, componentes, formularios, tablas, validaciones, peticiones HTTP, gráficos, iconos, notificaciones, estados de carga, modales y manejo de estados.

El backend estará desarrollado obligatoriamente con:

- PHP.
- Laravel.
- API REST.
- PostgreSQL 16.
- pgAdmin 4.

El frontend debe conectarse obligatoriamente con el backend Laravel mediante endpoints API REST.

Este archivo toma como base el alcance definido en `frontend_fases.md` y lo desglosa con mayor detalle en fases, subfases, tareas concretas, componentes, rutas, servicios, hooks, validaciones, conexiones con backend, criterios de aceptación y resultados esperados.

El objetivo de este archivo es servir como guía definitiva para implementar el frontend del sistema, evitando improvisaciones, duplicación de código y pérdida de requisitos.

---

## 2. Alcance obligatorio del frontend

El frontend debe cubrir todos los módulos funcionales definidos para el sistema CUP-FICCT:

1. Autenticación.
2. Control de sesión.
3. Gestión visual de roles: administrador, docente y alumno.
4. Panel administrativo.
5. Gestión de administradores.
6. Gestión de postulantes.
7. Validación de requisitos.
8. Subida y visualización de título de bachiller mediante Cloudinary a través del backend.
9. Pagos mediante Stripe.
10. Conversión de postulante a alumno.
11. Generación y visualización del código automático del alumno.
12. Gestión académica.
13. Carreras.
14. Cupos por carrera y gestión.
15. Docentes.
16. Materias.
17. Grupos.
18. Aulas.
19. Días.
20. Turnos.
21. Periodos de 45 minutos.
22. Horarios.
23. Asignaciones de docentes a materias y grupos.
24. Asistencia docente.
25. Asistencia de alumnos.
26. Exámenes.
27. Preguntas de selección múltiple.
28. Opciones de respuesta.
29. Resolución de examen por alumno.
30. Notas.
31. Promedios.
32. Estado aprobado/reprobado.
33. Asignación final de carrera por mayor nota y cupos.
34. Reportes administrativos.
35. Exportación PDF.
36. Exportación Excel.
37. Reportes mediante comandos de voz.
38. Carga masiva Excel/CSV.
39. Perfil de usuario.
40. Horarios por usuario.
41. Visualización de asistencias.
42. Seguridad de rutas.
43. Manejo de errores.
44. Reutilización obligatoria de componentes.
45. Conexión completa con backend Laravel.

---

## 3. Reglas obligatorias del frontend

El desarrollo del frontend debe cumplir obligatoriamente con las siguientes reglas:

1. Debe estar hecho con React JS.
2. Debe usar Vite.
3. Debe usar Node 22.22.2.
4. Debe usar Tailwind CSS versión 4.
5. Debe consumir el backend hecho con PHP y Laravel.
6. Debe conectarse al backend mediante API REST.
7. Debe centralizar las peticiones HTTP para evitar código repetido.
8. Debe reutilizar componentes obligatoriamente.
9. No se deben duplicar formularios, tablas, botones, modales, layouts ni lógica común.
10. Debe separar el sistema por módulos.
11. Debe tener rutas protegidas por autenticación.
12. Debe tener rutas protegidas por rol.
13. Solo existirán tres roles visibles en el sistema: administrador, docente y alumno.
14. El administrador tendrá acceso completo.
15. El docente solo verá sus funciones permitidas.
16. El alumno solo verá sus funciones permitidas.
17. El alumno iniciará sesión usando su código automático.
18. El código automático del alumno se genera en backend, pero debe mostrarse en frontend.
19. El sistema debe permitir login tradicional y login con Google mediante Firebase Authentication.
20. El frontend debe enviar el token de Firebase al backend Laravel cuando corresponda.
21. El frontend debe integrarse con Stripe para iniciar o redirigir el flujo de pago.
22. El frontend debe mostrar estados de pago.
23. El frontend debe permitir subir imagen del título de bachiller en el flujo conectado con Cloudinary mediante backend.
24. El frontend debe permitir dar examen solo cuando el backend indique que el examen está habilitado.
25. El frontend debe impedir visualmente acciones que el rol no tiene permitidas.
26. La validación final de permisos siempre será del backend.
27. El frontend debe manejar errores del backend de forma clara.
28. El frontend debe mostrar mensajes de éxito, advertencia y error.
29. El frontend debe ser responsive para PC y dispositivos móviles.
30. El frontend debe estar preparado para futuro despliegue en Vercel.
31. El frontend debe consumir variables de entorno para la URL de la API y claves públicas necesarias.
32. Puede usar datos simulados únicamente durante maquetación inicial, pero ningún módulo puede considerarse terminado hasta consumir endpoints reales de Laravel.
33. Todas las pantallas deben usar componentes comunes reutilizables.
34. Los nombres de carpetas, archivos, funciones, variables y comentarios deben ser claros y entendibles.
35. Se recomienda mantener nombres en español para facilitar comprensión del proyecto.

---

## 4. Librerías recomendadas

Las librerías deben facilitar el desarrollo, reducir código repetido y mantener una estructura profesional.

### 4.1 Peticiones HTTP

Librería recomendada:

- Axios.

Uso obligatorio dentro del proyecto:

- Centralizar llamadas al backend Laravel.
- Configurar `baseURL` desde variable de entorno.
- Agregar token automáticamente.
- Manejar errores globales.
- Manejar respuestas JSON.
- Descargar archivos PDF y Excel generados por backend.

### 4.2 Rutas

Librería recomendada:

- React Router DOM.

Uso:

- Rutas públicas.
- Rutas privadas.
- Rutas por rol.
- Redirección por sesión.
- Redirección por rol.
- Rutas del panel administrador.
- Rutas del panel docente.
- Rutas del panel alumno.

### 4.3 Formularios

Librerías recomendadas:

- React Hook Form.
- Zod o equivalente para validaciones.

Uso:

- Formularios reutilizables.
- Validación de campos obligatorios.
- Validación de correos.
- Validación de fechas.
- Validación de notas entre 0 y 100.
- Validación de porcentajes que deben sumar 100%.
- Validación de archivos.
- Validación de selección de dos carreras obligatorias.
- Validación de credenciales.
- Validación de código automático del alumno.

### 4.4 Tablas

Librería recomendada:

- TanStack Table o equivalente.

Uso:

- Listados administrativos.
- Paginación.
- Búsqueda.
- Filtros.
- Ordenamiento.
- Acciones por fila.
- Estado vacío.
- Estado cargando.
- Tabla base reutilizable.

### 4.5 Estado del servidor

Librería recomendada:

- TanStack Query o equivalente.

Uso:

- Consultar datos del backend.
- Cachear datos.
- Refrescar listados después de crear, editar o eliminar.
- Manejar estados de carga.
- Manejar estados de error.
- Evitar repetir lógica de peticiones en cada vista.

### 4.6 Componentes visuales

Librerías recomendadas compatibles con React y Tailwind CSS:

- shadcn/ui.
- Headless UI.
- Radix UI.
- Otra equivalente compatible con Tailwind CSS.

Uso:

- Modales.
- Menús.
- Dropdowns.
- Tabs.
- Popovers.
- Dialogs.
- Inputs.
- Selectores.
- Componentes accesibles.

### 4.7 Iconos

Librería recomendada:

- Lucide React o equivalente.

Uso:

- Sidebar.
- Navbar.
- Botones.
- Tarjetas de dashboard.
- Estados visuales.
- Acciones de tabla.

### 4.8 Notificaciones

Librerías recomendadas:

- Sonner.
- React Hot Toast.
- Otra equivalente.

Uso:

- Mensajes de éxito.
- Mensajes de error.
- Mensajes de advertencia.
- Confirmaciones de acciones.

### 4.9 Gráficos

Librería recomendada:

- Recharts o equivalente.

Uso:

- Dashboard administrativo.
- Estadísticas de inscritos.
- Estadísticas de aprobados.
- Estadísticas de reprobados.
- Estadísticas por materia.
- Estadísticas de asistencia.
- Estadísticas de cupos.

### 4.10 Fechas y horas

Librerías recomendadas:

- date-fns.
- Day.js.

Uso:

- Formatear fechas.
- Formatear horas.
- Comparar horarios.
- Mostrar horarios de clase.
- Mostrar asistencia por fecha.
- Mostrar estados de retraso o falta según respuesta del backend.

### 4.11 PDF, Excel y descargas

La generación de PDF y Excel será realizada principalmente por el backend Laravel.

El frontend debe:

- Enviar filtros al backend.
- Solicitar exportación PDF.
- Solicitar exportación Excel.
- Descargar el archivo generado.
- Mostrar estados de carga.
- Mostrar errores si la descarga falla.

### 4.12 Comandos de voz

Tecnología definida:

- Web Speech API del navegador.

Uso:

- Capturar voz del administrador.
- Convertir voz a texto.
- Mostrar texto reconocido.
- Enviar texto al backend si corresponde.
- Generar reportes por comando de voz.

---

## 5. Estructura definitiva recomendada de carpetas

La estructura debe ser modular, clara, mantenible y preparada para crecimiento.

```text
src/
  app/
    App.jsx
    router.jsx
    providers.jsx
  assets/
  components/
    common/
      Boton.jsx
      Input.jsx
      Select.jsx
      Textarea.jsx
      Modal.jsx
      Tabla.jsx
      BadgeEstado.jsx
      Loader.jsx
      MensajeError.jsx
      ConfirmDialog.jsx
      EmptyState.jsx
      CardIndicador.jsx
    layout/
      DashboardLayout.jsx
      AuthLayout.jsx
      Sidebar.jsx
      Navbar.jsx
      Breadcrumb.jsx
    forms/
      CampoTexto.jsx
      CampoSelect.jsx
      CampoFecha.jsx
      CampoArchivo.jsx
      CampoPassword.jsx
    tables/
      TablaBase.jsx
      AccionesTabla.jsx
      PaginacionTabla.jsx
      FiltrosTabla.jsx
  config/
    api.config.js
    firebase.config.js
    stripe.config.js
  hooks/
    useAuth.js
    useRol.js
    usePermisos.js
    useApi.js
    useFormulario.js
    useTabla.js
    useComandoVoz.js
  lib/
    api.js
    auth.js
    errores.js
    fechas.js
    formatos.js
    validaciones.js
    storage.js
  modules/
    auth/
    dashboard/
    usuarios/
    postulantes/
    requisitos/
    pagos/
    alumnos/
    docentes/
    gestion-academica/
    carreras/
    cupos/
    materias/
    grupos/
    aulas/
    horarios/
    asignaciones/
    asistencia-docente/
    asistencia-alumnos/
    examenes/
    notas/
    admision/
    reportes/
    carga-masiva/
    perfil/
  services/
    auth.service.js
    usuarios.service.js
    postulantes.service.js
    requisitos.service.js
    pagos.service.js
    alumnos.service.js
    docentes.service.js
    gestionAcademica.service.js
    carreras.service.js
    cupos.service.js
    materias.service.js
    grupos.service.js
    aulas.service.js
    horarios.service.js
    asistencia.service.js
    examenes.service.js
    notas.service.js
    reportes.service.js
    cargaMasiva.service.js
  styles/
    index.css
  main.jsx
```

Reglas de estructura:

- `components/common` debe contener componentes visuales reutilizables.
- `components/forms` debe contener campos reutilizables para formularios.
- `components/tables` debe contener la tabla base y elementos asociados.
- `modules` debe contener cada módulo funcional.
- `services` debe contener llamadas al backend Laravel.
- `hooks` debe contener lógica reutilizable.
- `lib` debe contener utilidades generales.
- `config` debe contener configuraciones de API, Firebase y Stripe.

---

## 6. Convención de nombres

Se recomienda usar nombres claros y en español para carpetas, archivos, funciones y componentes.

Ejemplos correctos:

```text
modules/postulantes/pages/ListarPostulantes.jsx
modules/postulantes/pages/RegistrarPostulante.jsx
modules/postulantes/components/FormularioPostulante.jsx
services/postulantes.service.js
hooks/useAuth.js
components/common/Tabla.jsx
components/common/Boton.jsx
```

Ejemplos incorrectos:

```text
page1.jsx
crud.jsx
misc.js
data.jsx
cosas.jsx
```

---

# 7. Fases y subfases definitivas

---

# Fase 0: Planificación técnica del frontend

## Objetivo

Definir la arquitectura inicial del frontend y asegurar que el proyecto React esté alineado con el backend Laravel, la base de datos PostgreSQL 16 y el contexto funcional del sistema CUP-FICCT.

## Dependencias

- Contexto funcional del sistema CUP-FICCT.
- Backend definido con PHP y Laravel.
- API REST como mecanismo de comunicación.
- PostgreSQL 16 como base de datos del backend.
- pgAdmin 4 como herramienta de administración de base de datos.

## Subfase 0.1: Confirmación del alcance técnico

### Tareas

1. Confirmar que el frontend será desarrollado con React JS.
2. Confirmar que se usará Vite.
3. Confirmar que se usará Node 22.22.2.
4. Confirmar que se usará Tailwind CSS versión 4.
5. Confirmar que el backend será PHP con Laravel.
6. Confirmar que el backend expondrá API REST.
7. Confirmar que el frontend consumirá endpoints reales del backend.
8. Confirmar que PostgreSQL 16 será la base de datos del backend.
9. Confirmar que pgAdmin 4 se usará para administración de base de datos.
10. Confirmar que solo existen tres roles: administrador, docente y alumno.
11. Confirmar que el frontend será modular.
12. Confirmar que los componentes deben reutilizarse obligatoriamente.

### Resultado esperado

Alcance técnico confirmado y alineado con el backend Laravel.

### Criterio de aceptación

El equipo sabe exactamente qué tecnologías usará el frontend y cómo se conectará con el backend.

## Subfase 0.2: Confirmación del alcance funcional

### Tareas

1. Confirmar que el administrador tendrá acceso completo.
2. Confirmar que el docente tendrá acceso limitado.
3. Confirmar que el alumno tendrá acceso limitado.
4. Confirmar que el alumno iniciará sesión con código automático.
5. Confirmar que el postulante debe cumplir requisitos.
6. Confirmar que el pago con Stripe es obligatorio antes de convertirse en alumno.
7. Confirmar que los documentos se guardarán mediante Cloudinary a través del backend.
8. Confirmar que Firebase Authentication se usará para login/validación con Google.
9. Confirmar que la asistencia es obligatoria para docentes y alumnos.
10. Confirmar que los exámenes serán de selección múltiple.
11. Confirmar que los reportes tendrán exportación PDF y Excel.
12. Confirmar que los reportes podrán generarse con comandos de voz.

### Resultado esperado

Alcance funcional completo identificado antes de programar.

### Criterio de aceptación

No se empieza a crear pantallas sin tener claros los módulos obligatorios.

## Subfase 0.3: Selección de librerías

### Tareas

1. Seleccionar librería de rutas: React Router DOM.
2. Seleccionar librería de peticiones HTTP: Axios.
3. Seleccionar librería para formularios: React Hook Form.
4. Seleccionar librería para validaciones: Zod o equivalente.
5. Seleccionar librería para tablas: TanStack Table o equivalente.
6. Seleccionar librería para estado del servidor: TanStack Query o equivalente.
7. Seleccionar librería visual compatible con Tailwind CSS: shadcn/ui, Headless UI, Radix UI o equivalente.
8. Seleccionar librería de iconos: Lucide React o equivalente.
9. Seleccionar librería de notificaciones: Sonner, React Hot Toast o equivalente.
10. Seleccionar librería de gráficos: Recharts o equivalente.
11. Seleccionar librería de fechas: date-fns o Day.js.
12. Confirmar uso de Web Speech API para comandos de voz.

### Resultado esperado

Librerías definidas para acelerar el desarrollo y reducir código repetido.

### Criterio de aceptación

Cada librería tiene un propósito claro dentro del proyecto.

## Subfase 0.4: Definición de comunicación con backend Laravel

### Tareas

1. Definir URL base del backend Laravel.
2. Definir variables de entorno del frontend.
3. Definir cliente HTTP centralizado.
4. Definir servicios por módulo.
5. Definir manejo de token o sesión.
6. Definir manejo de errores 401, 403, 422 y 500.
7. Definir formato de respuesta esperado desde Laravel.
8. Definir flujo de descarga de PDF.
9. Definir flujo de descarga de Excel.
10. Definir flujo de redirección a Stripe.
11. Definir envío de token Firebase al backend.

### Resultado esperado

Frontend preparado conceptualmente para consumir APIs reales del backend Laravel.

### Criterio de aceptación

Existe una estrategia clara para conectar React con Laravel.

---

# Fase 1: Creación del proyecto frontend

## Objetivo

Crear la base del proyecto React con Vite, Node 22.22.2 y Tailwind CSS 4.

## Subfase 1.1: Verificación del entorno

### Tareas

1. Verificar versión instalada de Node.
2. Confirmar que Node sea 22.22.2.
3. Verificar npm.
4. Verificar acceso a terminal.
5. Verificar carpeta donde se creará el proyecto frontend.

### Comandos sugeridos

```bash
node -v
npm -v
```

### Resultado esperado

Entorno listo para crear proyecto React.

### Criterio de aceptación

Node 22.22.2 está disponible.

## Subfase 1.2: Creación del proyecto con Vite

### Tareas

1. Crear proyecto React con Vite.
2. Entrar a la carpeta del proyecto.
3. Instalar dependencias base.
4. Ejecutar el proyecto localmente.
5. Verificar que abra en navegador.

### Comandos sugeridos

```bash
npm create vite@latest cup-ficct-frontend -- --template react
cd cup-ficct-frontend
npm install
npm run dev
```

### Resultado esperado

Proyecto React funcionando localmente.

### Criterio de aceptación

La aplicación abre correctamente en el navegador.

## Subfase 1.3: Limpieza de plantilla inicial

### Tareas

1. Eliminar archivos visuales innecesarios de la plantilla.
2. Limpiar `App.jsx`.
3. Limpiar estilos iniciales no usados.
4. Mantener solo lo necesario para iniciar la arquitectura.
5. Verificar que el proyecto siga compilando.

### Resultado esperado

Proyecto limpio y listo para arquitectura real.

### Criterio de aceptación

No quedan elementos innecesarios de la plantilla de Vite.

## Subfase 1.4: Instalación de Tailwind CSS 4

### Tareas

1. Instalar Tailwind CSS versión 4 según documentación vigente.
2. Configurar archivo global de estilos.
3. Importar estilos globales.
4. Probar clases de Tailwind.
5. Verificar que Tailwind funcione en componentes.

### Resultado esperado

Tailwind CSS 4 funcionando en React.

### Criterio de aceptación

Una clase Tailwind aplicada en una vista se refleja correctamente en el navegador.

## Subfase 1.5: Configuración de variables de entorno

### Variables mínimas

```text
VITE_API_URL=http://localhost:8000/api
VITE_FIREBASE_API_KEY=
VITE_FIREBASE_AUTH_DOMAIN=
VITE_STRIPE_PUBLIC_KEY=
```

### Tareas

1. Crear archivo `.env`.
2. Crear archivo `.env.example`.
3. Agregar URL base del backend Laravel.
4. Agregar claves públicas necesarias.
5. Evitar claves privadas en frontend.
6. Verificar lectura de variables con `import.meta.env`.

### Resultado esperado

Variables listas para conectar frontend con servicios externos y backend.

### Criterio de aceptación

El frontend puede leer `VITE_API_URL` correctamente.

---

# Fase 2: Arquitectura base y componentes reutilizables

## Objetivo

Crear la estructura modular del frontend y los componentes base para evitar repetir código.

## Subfase 2.1: Creación de carpetas principales

### Tareas

1. Crear carpeta `app`.
2. Crear carpeta `components`.
3. Crear carpeta `components/common`.
4. Crear carpeta `components/layout`.
5. Crear carpeta `components/forms`.
6. Crear carpeta `components/tables`.
7. Crear carpeta `config`.
8. Crear carpeta `hooks`.
9. Crear carpeta `lib`.
10. Crear carpeta `modules`.
11. Crear carpeta `services`.
12. Crear carpeta `styles`.

### Resultado esperado

Estructura base creada.

### Criterio de aceptación

El proyecto está organizado por responsabilidad y no tiene archivos mezclados sin criterio.

## Subfase 2.2: Configuración de providers globales

### Tareas

1. Crear `providers.jsx`.
2. Configurar provider de rutas si corresponde.
3. Configurar provider de TanStack Query si se usa.
4. Configurar provider de notificaciones.
5. Configurar contexto de autenticación si se implementa con Context API.

### Resultado esperado

Aplicación preparada para manejo global de estado, peticiones y notificaciones.

### Criterio de aceptación

Los providers se cargan desde la raíz del proyecto sin errores.

## Subfase 2.3: Componentes comunes obligatorios

### Componentes

1. `Boton.jsx`.
2. `Input.jsx`.
3. `Select.jsx`.
4. `Textarea.jsx`.
5. `Modal.jsx`.
6. `Tabla.jsx`.
7. `BadgeEstado.jsx`.
8. `Loader.jsx`.
9. `MensajeError.jsx`.
10. `ConfirmDialog.jsx`.
11. `EmptyState.jsx`.
12. `CardIndicador.jsx`.

### Tareas

1. Crear cada componente como componente reutilizable.
2. Permitir variantes visuales cuando corresponda.
3. Permitir estado deshabilitado.
4. Permitir estado de carga en botones.
5. Permitir mensajes de error.
6. Usar Tailwind CSS 4.
7. Evitar lógica de negocio dentro de componentes comunes.

### Resultado esperado

Componentes reutilizables disponibles para todos los módulos.

### Criterio de aceptación

Las pantallas usan estos componentes y no crean versiones duplicadas.

## Subfase 2.4: Componentes de formulario reutilizables

### Componentes

1. `CampoTexto.jsx`.
2. `CampoSelect.jsx`.
3. `CampoFecha.jsx`.
4. `CampoArchivo.jsx`.
5. `CampoPassword.jsx`.

### Tareas

1. Integrar con React Hook Form.
2. Mostrar errores de validación.
3. Permitir labels.
4. Permitir placeholders.
5. Permitir campos obligatorios.
6. Reutilizar en postulantes, docentes, usuarios, horarios, exámenes y demás módulos.

### Resultado esperado

Campos reutilizables para formularios.

### Criterio de aceptación

No se repite código de inputs en cada formulario.

## Subfase 2.5: Componentes de tabla reutilizables

### Componentes

1. `TablaBase.jsx`.
2. `AccionesTabla.jsx`.
3. `PaginacionTabla.jsx`.
4. `FiltrosTabla.jsx`.

### Tareas

1. Crear tabla base reutilizable.
2. Permitir columnas dinámicas.
3. Permitir acciones por fila.
4. Permitir filtros.
5. Permitir paginación.
6. Permitir estado cargando.
7. Permitir estado sin datos.

### Resultado esperado

Tabla base reutilizable en todos los listados.

### Criterio de aceptación

Los módulos usan la misma estructura de tabla base.

## Subfase 2.6: Layouts base

### Layouts necesarios

1. `AuthLayout.jsx`.
2. `DashboardLayout.jsx`.
3. Layout de administrador.
4. Layout de docente.
5. Layout de alumno.

### Tareas

1. Crear layout para pantallas públicas.
2. Crear layout para paneles internos.
3. Crear sidebar.
4. Crear navbar.
5. Crear breadcrumb.
6. Adaptar layout para móvil.

### Resultado esperado

Base visual lista para todos los roles.

### Criterio de aceptación

Cada rol puede tener una estructura visual propia, pero reutilizando componentes comunes.

---

# Fase 3: Configuración de rutas

## Objetivo

Definir rutas públicas, rutas privadas y rutas protegidas por rol.

## Subfase 3.1: Configuración base de React Router

### Tareas

1. Instalar React Router DOM.
2. Crear `router.jsx`.
3. Definir rutas principales.
4. Integrar router en `App.jsx`.
5. Probar navegación básica.

### Resultado esperado

Sistema de rutas funcionando.

### Criterio de aceptación

Se puede navegar entre pantallas sin recargar la página.

## Subfase 3.2: Rutas públicas

### Rutas públicas mínimas

1. Login.
2. Registro de postulante si corresponde al flujo público.
3. Pantalla de pago o retorno de pago si corresponde.
4. Pantalla de pago exitoso.
5. Pantalla de pago cancelado o fallido.
6. Validación o retorno de Google/Firebase si corresponde.

### Resultado esperado

Rutas públicas accesibles sin sesión.

### Criterio de aceptación

Un usuario no autenticado solo puede entrar a rutas públicas.

## Subfase 3.3: Ruta protegida por autenticación

### Tareas

1. Crear componente `RutaProtegida`.
2. Validar existencia de token o sesión.
3. Consultar usuario autenticado al backend si corresponde.
4. Redirigir a login si no hay sesión.
5. Mostrar loader mientras se valida sesión.

### Resultado esperado

Rutas privadas protegidas.

### Criterio de aceptación

Un usuario sin sesión no puede entrar a paneles internos.

## Subfase 3.4: Ruta protegida por rol

### Roles

- Administrador.
- Docente.
- Alumno.

### Tareas

1. Crear componente `RutaPorRol`.
2. Validar rol del usuario autenticado.
3. Redirigir si el rol no coincide.
4. Mostrar pantalla de acceso denegado si corresponde.
5. Ocultar menús no permitidos.

### Resultado esperado

Cada rol accede solo a sus pantallas permitidas.

### Criterio de aceptación

Un alumno no puede ingresar al panel administrador y un docente no puede ver funciones administrativas completas.

## Subfase 3.5: Redirección según rol

### Tareas

1. Después del login, detectar rol.
2. Redirigir administrador a dashboard administrativo.
3. Redirigir docente a panel docente.
4. Redirigir alumno a panel alumno.
5. Manejar rol inválido como error.

### Resultado esperado

Flujo de ingreso ordenado según rol.

### Criterio de aceptación

Cada usuario llega a su panel correcto después de autenticarse.

---

# Fase 4: Conexión con backend Laravel

## Objetivo

Conectar React con el backend hecho en PHP y Laravel mediante API REST.

## Subfase 4.1: Cliente Axios centralizado

### Tareas

1. Crear `lib/api.js`.
2. Configurar `baseURL` usando `VITE_API_URL`.
3. Configurar headers comunes.
4. Agregar token si existe.
5. Configurar interceptor de request.
6. Configurar interceptor de response.
7. Manejar errores 401.
8. Manejar errores 403.
9. Manejar errores 422.
10. Manejar errores 500.

### Resultado esperado

Todas las peticiones pasan por el cliente HTTP centralizado.

### Criterio de aceptación

No existen llamadas directas repetidas a `fetch` o `axios` fuera del servicio central.

## Subfase 4.2: Servicios por módulo

### Servicios mínimos

1. `auth.service.js`.
2. `usuarios.service.js`.
3. `postulantes.service.js`.
4. `requisitos.service.js`.
5. `pagos.service.js`.
6. `alumnos.service.js`.
7. `docentes.service.js`.
8. `gestionAcademica.service.js`.
9. `carreras.service.js`.
10. `cupos.service.js`.
11. `materias.service.js`.
12. `grupos.service.js`.
13. `aulas.service.js`.
14. `horarios.service.js`.
15. `asistencia.service.js`.
16. `examenes.service.js`.
17. `notas.service.js`.
18. `reportes.service.js`.
19. `cargaMasiva.service.js`.

### Tareas

1. Crear un servicio por módulo.
2. Definir funciones claras: listar, crear, ver, editar, eliminar, validar, exportar según corresponda.
3. Evitar lógica visual dentro de servicios.
4. Retornar datos limpios a las vistas.
5. Centralizar endpoints para facilitar mantenimiento.

### Resultado esperado

Servicios organizados y reutilizables.

### Criterio de aceptación

Cada módulo consume su servicio correspondiente.

## Subfase 4.3: Manejo global de errores

### Tareas

1. Crear utilidad `errores.js`.
2. Convertir errores del backend en mensajes legibles.
3. Mostrar errores de validación.
4. Mostrar errores de permisos.
5. Mostrar error de sesión expirada.
6. Mostrar error de conexión.
7. Mostrar error inesperado.

### Resultado esperado

El usuario ve mensajes claros cuando una operación falla.

### Criterio de aceptación

Los errores del backend no se muestran como objetos técnicos confusos.

## Subfase 4.4: Preparación para CORS y API real

### Tareas

1. Confirmar URL del backend local.
2. Confirmar que Laravel permite peticiones desde React.
3. Probar endpoint de salud o `/api` si existe.
4. Probar solicitud autenticada cuando exista login.
5. Documentar si el error es del frontend o CORS backend.

### Resultado esperado

Frontend preparado para consumir backend local y luego backend desplegado.

### Criterio de aceptación

React puede comunicarse correctamente con Laravel.

---

# Fase 5: Módulo de autenticación frontend

## Objetivo

Implementar login, logout, control de sesión, Firebase Authentication y perfil autenticado.

## Subfase 5.1: Pantalla de login tradicional

### Tareas

1. Crear módulo `auth`.
2. Crear pantalla `Login.jsx`.
3. Crear formulario con usuario/correo y contraseña.
4. Validar usuario obligatorio.
5. Validar contraseña obligatoria.
6. Enviar credenciales al backend Laravel.
7. Guardar token o sesión según respuesta del backend.
8. Consultar datos del usuario autenticado.
9. Redirigir según rol.
10. Mostrar errores de credenciales inválidas.

### Resultado esperado

Login tradicional funcional.

### Criterio de aceptación

Un usuario válido puede iniciar sesión y un usuario inválido recibe mensaje claro.

## Subfase 5.2: Login de alumno con código automático

### Regla

El alumno inicia sesión usando el código generado automáticamente con:

```text
AÑO + GESTIÓN + CÉDULA DE IDENTIDAD
```

Ejemplo:

```text
2026113541539
```

### Tareas

1. Crear opción visual para login de alumno con código.
2. Crear campo de código.
3. Validar que el código sea obligatorio.
4. Enviar código al backend Laravel.
5. Procesar respuesta del backend.
6. Redirigir al panel alumno si es correcto.
7. Mostrar error si el código es inválido.

### Resultado esperado

Alumno puede iniciar sesión con su código.

### Criterio de aceptación

El alumno accede únicamente a su panel.

## Subfase 5.3: Firebase Authentication con Google

### Tareas

1. Crear archivo `firebase.config.js`.
2. Configurar claves públicas desde variables de entorno.
3. Crear botón de login con Google.
4. Abrir flujo de autenticación de Google.
5. Obtener token de Firebase.
6. Enviar token al backend Laravel.
7. Esperar validación del backend.
8. Continuar flujo según rol o estado.
9. Mostrar error si Firebase falla.
10. Mostrar error si backend rechaza el token.

### Resultado esperado

Login o validación con Google disponible.

### Criterio de aceptación

El frontend no valida solo el token; siempre debe enviarlo al backend para validación final.

## Subfase 5.4: Logout

### Tareas

1. Crear función de cierre de sesión.
2. Consumir endpoint de logout del backend.
3. Limpiar token local.
4. Limpiar datos de usuario.
5. Limpiar cache de TanStack Query si se usa.
6. Redirigir al login.

### Resultado esperado

Cierre de sesión funcional.

### Criterio de aceptación

Después de cerrar sesión no se puede acceder a rutas protegidas.

## Subfase 5.5: Perfil autenticado

### Tareas

1. Consultar endpoint `/me` o equivalente.
2. Mostrar datos del usuario autenticado.
3. Mostrar rol.
4. Mostrar información según administrador, docente o alumno.
5. Manejar sesión expirada.

### Resultado esperado

Usuario autenticado visible en la interfaz.

### Criterio de aceptación

El sistema sabe quién está autenticado y qué rol tiene.

---

# Fase 6: Panel administrativo

## Objetivo

Crear el dashboard principal para administrador, con menú, indicadores y acceso a todos los módulos.

## Subfase 6.1: Layout administrativo

### Tareas

1. Crear sidebar administrativo.
2. Crear navbar administrativo.
3. Crear menú de módulos.
4. Crear breadcrumbs.
5. Crear versión responsive.
6. Mostrar usuario autenticado.
7. Agregar botón de logout.

### Resultado esperado

Panel administrativo base funcional.

### Criterio de aceptación

El administrador ve el menú completo del sistema.

## Subfase 6.2: Indicadores principales

### Indicadores obligatorios

1. Total inscritos.
2. Total aprobados.
3. Total reprobados.
4. Total grupos habilitados.

### Tareas

1. Crear tarjetas `CardIndicador`.
2. Consumir endpoint de dashboard.
3. Mostrar estado cargando.
4. Mostrar error si falla.
5. Mostrar indicadores con formato claro.

### Resultado esperado

Dashboard muestra resumen general del sistema.

### Criterio de aceptación

Los indicadores vienen desde backend Laravel.

## Subfase 6.3: Indicadores adicionales

### Indicadores

1. Pagos.
2. Asistencia.
3. Cupos.
4. Exámenes.
5. Docentes.
6. Alumnos.

### Tareas

1. Crear gráficos si corresponde.
2. Mostrar distribución de estados.
3. Mostrar accesos rápidos a módulos.
4. Mostrar información útil sin sobrecargar la pantalla.

### Resultado esperado

Administrador visualiza el estado general del sistema.

### Criterio de aceptación

Los datos importantes son visibles y comprensibles.

---

# Fase 7: Gestión de usuarios, roles y administradores

## Objetivo

Permitir que el administrador gestione usuarios y cree administradores.

## Subfase 7.1: Listado de usuarios

### Tareas

1. Crear vista `ListarUsuarios.jsx`.
2. Consumir endpoint de usuarios.
3. Mostrar tabla reutilizable.
4. Filtrar por rol.
5. Buscar por nombre.
6. Buscar por correo.
7. Buscar por cédula de identidad.
8. Mostrar estado activo/inactivo.
9. Agregar acciones por fila.

### Resultado esperado

Administrador puede ver usuarios.

### Criterio de aceptación

El listado usa `TablaBase` y datos del backend.

## Subfase 7.2: Crear administrador

### Tareas

1. Crear formulario de administrador.
2. Validar campos obligatorios.
3. Validar correo.
4. Validar contraseña si corresponde.
5. Enviar datos al backend.
6. Mostrar confirmación.
7. Actualizar listado.

### Resultado esperado

Administrador puede crear otros administradores.

### Criterio de aceptación

El rol asignado es administrador y no se crean roles distintos a los permitidos.

## Subfase 7.3: Editar usuario

### Tareas

1. Abrir modal o pantalla de edición.
2. Cargar datos actuales.
3. Actualizar datos permitidos.
4. Guardar cambios en backend.
5. Mostrar confirmación.
6. Actualizar listado.

### Resultado esperado

Usuario actualizado correctamente.

### Criterio de aceptación

No se permite asignar roles inexistentes.

## Subfase 7.4: Activar o desactivar usuario

### Tareas

1. Agregar acción en tabla.
2. Pedir confirmación.
3. Enviar solicitud al backend.
4. Mostrar resultado.
5. Actualizar estado en tabla.

### Resultado esperado

Gestión básica de estado de usuarios.

### Criterio de aceptación

La acción respeta permisos del administrador.

---

# Fase 8: Módulo de postulantes

## Objetivo

Permitir registrar, listar, consultar, editar y eliminar lógicamente postulantes.

## Subfase 8.1: Formulario reutilizable de postulante

### Campos

1. Cédula de identidad.
2. Nombres.
3. Apellidos.
4. Fecha de nacimiento.
5. Sexo.
6. Dirección.
7. Teléfono.
8. Correo electrónico.
9. Colegio de procedencia.
10. Ciudad.
11. Primera opción de carrera.
12. Segunda opción de carrera.
13. Título de bachiller como imagen.

### Tareas

1. Crear `FormularioPostulante.jsx`.
2. Usar campos reutilizables.
3. Validar campos vacíos.
4. Validar correo.
5. Validar primera opción de carrera.
6. Validar segunda opción de carrera.
7. Validar que existan dos carreras obligatorias.
8. Validar archivo de imagen.
9. Preparar envío con `FormData` si incluye archivo.
10. Reutilizar formulario para crear y editar.

### Resultado esperado

Formulario de postulante reutilizable.

### Criterio de aceptación

No se crean formularios separados e innecesarios para registrar y editar.

## Subfase 8.2: Registro de postulante

### Tareas

1. Crear pantalla `RegistrarPostulante.jsx`.
2. Integrar `FormularioPostulante`.
3. Enviar datos al backend Laravel.
4. Mostrar errores de validación.
5. Mostrar éxito al registrar.
6. Redirigir o limpiar formulario según flujo.

### Resultado esperado

Postulante registrado correctamente.

### Criterio de aceptación

El backend recibe todos los campos requeridos.

## Subfase 8.3: Listado de postulantes

### Tareas

1. Crear pantalla `ListarPostulantes.jsx`.
2. Consumir endpoint de postulantes.
3. Mostrar `TablaBase`.
4. Agregar búsqueda.
5. Agregar filtros.
6. Agregar paginación.
7. Mostrar acciones: ver, editar, eliminar.
8. Mostrar estado de requisitos.
9. Mostrar estado de pago.
10. Mostrar estado de acceso como alumno.

### Resultado esperado

Administrador puede consultar postulantes.

### Criterio de aceptación

El listado es funcional, filtrable y conectado al backend.

## Subfase 8.4: Consulta individual de postulante

### Tareas

1. Crear pantalla de detalle.
2. Mostrar datos personales.
3. Mostrar requisitos.
4. Mostrar imagen del título de bachiller.
5. Mostrar estado de pago.
6. Mostrar estado de acceso como alumno.
7. Mostrar carreras elegidas.
8. Mostrar acciones permitidas.

### Resultado esperado

Detalle completo del postulante disponible.

### Criterio de aceptación

El administrador puede revisar toda la información relevante antes de validar o convertir.

## Subfase 8.5: Edición de postulante

### Tareas

1. Cargar datos actuales.
2. Reutilizar `FormularioPostulante`.
3. Permitir modificar datos permitidos.
4. Enviar actualización al backend.
5. Mostrar confirmación.
6. Actualizar listado o detalle.

### Resultado esperado

Postulante actualizado.

### Criterio de aceptación

No se pierde información del postulante al editar.

## Subfase 8.6: Eliminación lógica de postulante

### Tareas

1. Agregar acción eliminar.
2. Mostrar confirmación.
3. Enviar solicitud al backend.
4. Mostrar resultado.
5. Actualizar listado.

### Resultado esperado

Postulante eliminado lógicamente si corresponde.

### Criterio de aceptación

La eliminación no rompe relaciones del sistema.

---

# Fase 9: Requisitos, documentos y Cloudinary

## Objetivo

Permitir cargar, visualizar y validar documentos del postulante, principalmente el título de bachiller.

## Subfase 9.1: Componente de subida de archivo

### Tareas

1. Crear `CampoArchivo.jsx`.
2. Validar que el archivo sea imagen.
3. Mostrar nombre del archivo.
4. Mostrar vista previa.
5. Permitir reemplazar archivo antes de enviar.
6. Mostrar errores.

### Resultado esperado

Componente reutilizable de carga de imagen.

### Criterio de aceptación

El mismo componente puede usarse en otros módulos si se requiere carga de archivos.

## Subfase 9.2: Subida del título de bachiller

### Tareas

1. Integrar campo de archivo en formulario de postulante.
2. Enviar archivo al backend.
3. El backend gestionará Cloudinary.
4. Mostrar URL o imagen devuelta por backend.
5. Manejar error de subida.

### Resultado esperado

Título de bachiller cargado y visible.

### Criterio de aceptación

La imagen visualizada proviene de la respuesta del backend o de la URL almacenada.

## Subfase 9.3: Validación de requisitos por administrador

### Tareas

1. Mostrar requisitos del postulante.
2. Permitir aprobar requisito.
3. Permitir rechazar requisito.
4. Permitir observaciones si backend lo contempla.
5. Actualizar estado visual.
6. Mostrar confirmación.

### Resultado esperado

Administrador valida requisitos desde interfaz.

### Criterio de aceptación

El pago solo debe habilitarse visualmente si los requisitos están cumplidos, de acuerdo con la respuesta del backend.

---

# Fase 10: Pagos con Stripe

## Objetivo

Implementar el flujo visual de pago obligatorio mediante Stripe.

## Subfase 10.1: Estado de pago del postulante

### Tareas

1. Mostrar si el postulante puede pagar.
2. Mostrar si requisitos están cumplidos.
3. Mostrar si el pago está pendiente.
4. Mostrar si el pago está confirmado.
5. Mostrar si el pago fue fallido o cancelado.

### Resultado esperado

Estado de pago claro en la interfaz.

### Criterio de aceptación

El usuario entiende si puede continuar o no con el pago.

## Subfase 10.2: Solicitud de pago

### Tareas

1. Habilitar botón de pago solo si requisitos están cumplidos.
2. Consumir endpoint Laravel para crear sesión de Stripe.
3. Redirigir a Stripe o abrir flujo correspondiente.
4. Mostrar cargando mientras se crea sesión.
5. Mostrar error si no se puede iniciar pago.

### Resultado esperado

Postulante puede iniciar pago cuando corresponde.

### Criterio de aceptación

No se inicia pago si los requisitos no están cumplidos.

## Subfase 10.3: Retorno de pago exitoso

### Tareas

1. Crear pantalla de pago exitoso.
2. Consultar estado real al backend.
3. Mostrar mensaje de éxito.
4. Indicar que el administrador debe validar o dar acceso según flujo.

### Resultado esperado

Frontend refleja pago exitoso.

### Criterio de aceptación

El estado final viene del backend, no solo de la URL de retorno.

## Subfase 10.4: Retorno de pago cancelado o fallido

### Tareas

1. Crear pantalla de pago cancelado.
2. Crear pantalla de pago fallido si corresponde.
3. Consultar estado real al backend.
4. Mostrar opción de reintentar si backend lo permite.

### Resultado esperado

Usuario recibe información clara si el pago no se completó.

### Criterio de aceptación

El frontend no marca pago como exitoso sin confirmación del backend.

## Subfase 10.5: Vista administrativa de pagos

### Tareas

1. Listar pagos.
2. Filtrar por estado.
3. Buscar por postulante.
4. Ver detalle de pago.
5. Mostrar relación con postulante.
6. Permitir validación administrativa si corresponde.

### Resultado esperado

Administrador puede revisar pagos desde el sistema.

### Criterio de aceptación

Los pagos están relacionados visualmente con postulantes.

---

# Fase 11: Conversión de postulante a alumno

## Objetivo

Permitir que el administrador otorgue acceso como alumno después de cumplir requisitos y pago.

## Subfase 11.1: Validaciones visuales previas

### Tareas

1. Mostrar estado de requisitos.
2. Mostrar estado de pago.
3. Bloquear botón si faltan requisitos.
4. Bloquear botón si no hay pago confirmado.
5. Mostrar mensajes explicativos.

### Resultado esperado

No se permite convertir visualmente si falta requisito o pago.

### Criterio de aceptación

La interfaz respeta el flujo: requisitos -> pago -> acceso como alumno.

## Subfase 11.2: Conversión a alumno

### Tareas

1. Crear botón “Dar acceso como alumno”.
2. Mostrar confirmación.
3. Consumir endpoint de conversión.
4. Esperar respuesta del backend.
5. Mostrar código generado.
6. Actualizar estado del postulante.

### Resultado esperado

Postulante convertido a alumno y código visible.

### Criterio de aceptación

El código se genera en backend y solo se muestra en frontend.

## Subfase 11.3: Visualización del código automático

### Formato del código

```text
AÑO + GESTIÓN + CÉDULA DE IDENTIDAD
```

Ejemplo:

```text
2026113541539
```

### Tareas

1. Mostrar código generado.
2. Permitir copiar código si se desea.
3. Mostrar advertencia de que sirve para login del alumno.
4. Mostrar código en perfil del alumno.

### Resultado esperado

Código del alumno visible y comprensible.

### Criterio de aceptación

El frontend no calcula el código, solo muestra lo recibido del backend.

---

# Fase 12: Gestión académica, carreras y cupos

## Objetivo

Crear interfaces para gestionar gestiones académicas, carreras y cupos.

## Subfase 12.1: Gestión académica

### Tareas

1. Listar gestiones académicas.
2. Crear gestión.
3. Definir año.
4. Definir gestión 1 o 2.
5. Mostrar estado.
6. Editar gestión si corresponde.
7. Activar o desactivar gestión si corresponde.

### Resultado esperado

Gestiones académicas administrables.

### Criterio de aceptación

La gestión solo puede ser 1 o 2 según backend.

## Subfase 12.2: Carreras

### Tareas

1. Listar carreras.
2. Crear carrera.
3. Editar carrera.
4. Activar carrera.
5. Desactivar carrera.
6. Ver detalle de carrera.

### Resultado esperado

Carreras administrables.

### Criterio de aceptación

Las carreras pueden usarse en postulaciones y cupos.

## Subfase 12.3: Cupos por carrera y gestión

### Tareas

1. Listar cupos.
2. Seleccionar carrera.
3. Seleccionar gestión.
4. Definir cantidad de cupos.
5. Mostrar cupos usados.
6. Mostrar cupos disponibles.
7. Mostrar carrera con menos personas si corresponde.

### Resultado esperado

Cupos visibles y administrables.

### Criterio de aceptación

El administrador puede controlar cupos por carrera y gestión.

---

# Fase 13: Docentes

## Objetivo

Permitir la gestión completa de docentes.

## Subfase 13.1: Formulario de docente

### Campos

1. Nombre.
2. Apellido paterno.
3. Apellido materno.
4. Cédula de identidad.
5. Celular.
6. Correo.
7. Profesional en el área.
8. Maestría.
9. Diplomado en educación superior.

### Tareas

1. Crear `FormularioDocente.jsx`.
2. Validar campos obligatorios.
3. Validar correo.
4. Validar requisitos académicos.
5. Reutilizar formulario para crear y editar.

### Resultado esperado

Formulario docente reutilizable.

### Criterio de aceptación

El formulario representa todos los datos requeridos del docente.

## Subfase 13.2: Registro de docente

### Tareas

1. Crear pantalla de registro.
2. Usar formulario reutilizable.
3. Enviar datos al backend.
4. Mostrar errores.
5. Mostrar confirmación.

### Resultado esperado

Docente registrado.

### Criterio de aceptación

El docente cumple con los requisitos definidos o el backend rechaza el registro.

## Subfase 13.3: Listado y búsqueda de docentes

### Tareas

1. Listar docentes.
2. Buscar por nombre.
3. Buscar por cédula.
4. Buscar por correo.
5. Mostrar estado.
6. Mostrar acciones.

### Resultado esperado

Administrador consulta docentes.

### Criterio de aceptación

La tabla de docentes usa componente reutilizable.

## Subfase 13.4: Edición, detalle y desactivación

### Tareas

1. Ver detalle del docente.
2. Editar datos.
3. Desactivar docente si corresponde.
4. Mostrar asignaciones relacionadas.

### Resultado esperado

Gestión completa de docentes.

### Criterio de aceptación

No se pierde información de asignaciones al consultar detalle.

---

# Fase 14: Materias, grupos y aulas

## Objetivo

Permitir administrar materias, grupos y aulas.

## Subfase 14.1: Materias

### Materias obligatorias

1. Física.
2. Matemáticas.
3. Computación.
4. Inglés.

### Tareas

1. Listar materias.
2. Mostrar materias base.
3. Crear materia si backend lo permite.
4. Editar materia si backend lo permite.
5. Activar o desactivar materia si corresponde.

### Resultado esperado

Materias visibles y administrables.

### Criterio de aceptación

Las materias obligatorias existen y pueden usarse en exámenes, horarios y asignaciones.

## Subfase 14.2: Grupos

### Reglas

- Cada grupo admite máximo 70 alumnos.
- El backend calcula la cantidad de grupos necesarios.

### Tareas

1. Listar grupos.
2. Crear grupo.
3. Mostrar gestión.
4. Mostrar cantidad de estudiantes.
5. Mostrar capacidad máxima.
6. Mostrar estudiantes del grupo.
7. Mostrar estado del grupo.

### Resultado esperado

Grupos administrables.

### Criterio de aceptación

La interfaz muestra que el máximo permitido es 70 alumnos por grupo.

## Subfase 14.3: Aulas

### Regla

El aula solo tendrá ubicación.

Ejemplo:

```text
Módulo 236, Aula 11
```

### Tareas

1. Listar aulas.
2. Crear aula con ubicación.
3. Editar ubicación.
4. Desactivar aula si corresponde.

### Resultado esperado

Aulas administrables por ubicación.

### Criterio de aceptación

No se agregan campos no definidos como capacidad o equipamiento.

---

# Fase 15: Horarios, días, turnos y periodos

## Objetivo

Permitir que el administrador defina días, turnos, periodos y horarios.

## Subfase 15.1: Días

### Tareas

1. Listar días disponibles.
2. Mostrar días en formularios de horario.
3. Validar selección de día.

### Resultado esperado

Días disponibles para crear horarios.

### Criterio de aceptación

Los días se obtienen desde backend si existe endpoint correspondiente.

## Subfase 15.2: Turnos

### Tareas

1. Listar turnos.
2. Crear turno.
3. Editar turno.
4. Mostrar turnos disponibles.
5. Validar campos obligatorios.

### Resultado esperado

Turnos administrables.

### Criterio de aceptación

Los turnos pueden asociarse a horarios.

## Subfase 15.3: Periodos de 45 minutos

### Regla

Cada periodo tendrá 45 minutos.

### Tareas

1. Crear formulario de periodo.
2. Ingresar hora de inicio.
3. Ingresar hora de fin.
4. Mostrar duración.
5. Validar duración de 45 minutos según backend.
6. Listar periodos.

### Resultado esperado

Periodos definidos correctamente.

### Criterio de aceptación

No se acepta visualmente un periodo inválido, pero la validación final la realiza backend.

## Subfase 15.4: Creación de horarios

### Tareas

1. Seleccionar grupo.
2. Seleccionar materia.
3. Seleccionar docente.
4. Seleccionar aula.
5. Seleccionar día.
6. Seleccionar turno.
7. Seleccionar periodo.
8. Enviar datos al backend.
9. Mostrar error si hay choque de horario según backend.
10. Mostrar confirmación si se crea correctamente.

### Resultado esperado

Horarios de clase administrables.

### Criterio de aceptación

El horario queda relacionado con grupo, materia, docente, aula, día, turno y periodo.

## Subfase 15.5: Consulta de horarios por rol

### Usuarios

1. Administrador ve horarios generales.
2. Docente ve sus horarios.
3. Alumno ve sus horarios.

### Tareas

1. Crear vista de horarios general.
2. Crear vista de horario docente.
3. Crear vista de horario alumno.
4. Mostrar día, turno, periodo, materia, grupo y aula.
5. Mostrar próxima clase si corresponde.

### Resultado esperado

Horarios visibles según rol.

### Criterio de aceptación

Cada usuario solo ve los horarios permitidos.

---

# Fase 16: Asignación docente-materia-grupo

## Objetivo

Permitir asignar docentes a materias y grupos.

## Reglas

1. Un docente puede ser asignado de 1 a 4 grupos.
2. Un docente puede dar de 1 a 4 materias como máximo.
3. El administrador realiza la asignación.

## Subfase 16.1: Formulario de asignación

### Tareas

1. Seleccionar docente.
2. Seleccionar grupo.
3. Seleccionar materia.
4. Mostrar asignaciones actuales del docente.
5. Enviar datos al backend.
6. Mostrar error si supera límites.

### Resultado esperado

Asignación creada respetando reglas.

### Criterio de aceptación

El frontend informa claramente si el backend rechaza la asignación por superar límites.

## Subfase 16.2: Listado de asignaciones

### Tareas

1. Listar asignaciones.
2. Filtrar por docente.
3. Filtrar por grupo.
4. Filtrar por materia.
5. Ver detalle.
6. Eliminar o desactivar asignación si corresponde.

### Resultado esperado

Asignaciones visibles para administración.

### Criterio de aceptación

El administrador puede saber qué docente está asignado a qué materia y grupo.

---

# Fase 17: Asistencia docente

## Objetivo

Permitir al docente marcar entrada y salida, y al administrador visualizar asistencias.

## Reglas

1. La asistencia es obligatoria.
2. El docente marca cuando llega a dar clase.
3. El docente marca cuando finaliza su clase.
4. Solo puede marcar según horario.
5. Puede marcar máximo 30 minutos después de iniciar clase.
6. Luego de 30 minutos es retraso.
7. Pasado el horario es falta automática.
8. El administrador puede visualizar asistencia de todos los docentes.

## Subfase 17.1: Clase activa del docente

### Tareas

1. Consultar backend para obtener clase activa.
2. Mostrar materia.
3. Mostrar grupo.
4. Mostrar aula.
5. Mostrar horario.
6. Mostrar estado actual de asistencia.
7. Mostrar si puede marcar o no.

### Resultado esperado

Docente sabe si tiene una clase activa.

### Criterio de aceptación

No se habilita marcado si no hay clase activa según backend.

## Subfase 17.2: Marcar entrada docente

### Tareas

1. Mostrar botón marcar entrada.
2. Deshabilitar botón si no corresponde.
3. Enviar solicitud al backend.
4. Mostrar estado resultante: presente o retraso.
5. Mostrar mensaje de confirmación.

### Resultado esperado

Docente marca entrada según horario.

### Criterio de aceptación

El estado lo determina backend; frontend solo lo muestra.

## Subfase 17.3: Marcar salida docente

### Tareas

1. Mostrar botón marcar salida.
2. Validar visualmente que ya exista entrada.
3. Enviar solicitud al backend.
4. Mostrar confirmación.
5. Actualizar estado de asistencia.

### Resultado esperado

Docente marca finalización de clase.

### Criterio de aceptación

No se marca salida sin una asistencia de entrada válida, según backend.

## Subfase 17.4: Visualización administrativa de asistencia docente

### Tareas

1. Crear vista de asistencia docente.
2. Filtrar por fecha.
3. Filtrar por docente.
4. Filtrar por estado.
5. Mostrar estados: presente, retraso, falta.
6. Mostrar visualmente qué días vinieron y qué días no.

### Resultado esperado

Administrador puede ver qué docentes vinieron y cuáles no.

### Criterio de aceptación

La vista es clara y visual.

---

# Fase 18: Asistencia de alumnos

## Objetivo

Permitir al alumno marcar asistencia y al docente tomar asistencia a sus alumnos.

## Reglas

1. La asistencia del alumno es obligatoria.
2. El alumno puede marcar su asistencia.
3. El docente puede tomar asistencia a sus alumnos.
4. Solo se permite según horario.
5. Máximo 30 minutos después de iniciar la clase.
6. Luego de 30 minutos es retraso.
7. Pasado el horario es falta automática.
8. El administrador ve asistencia de todos.
9. El docente solo ve asistencia de sus alumnos.
10. El alumno solo ve su propia asistencia.

## Subfase 18.1: Clase activa del alumno

### Tareas

1. Consultar clase activa del alumno.
2. Mostrar materia.
3. Mostrar docente.
4. Mostrar grupo.
5. Mostrar aula.
6. Mostrar horario.
7. Mostrar estado de asistencia.

### Resultado esperado

Alumno sabe si puede marcar asistencia.

### Criterio de aceptación

El botón de asistencia depende de la clase activa reportada por backend.

## Subfase 18.2: Alumno marca asistencia

### Tareas

1. Mostrar botón marcar asistencia.
2. Deshabilitar si no corresponde.
3. Enviar solicitud al backend.
4. Mostrar estado generado.
5. Actualizar historial.

### Resultado esperado

Alumno marca asistencia según horario.

### Criterio de aceptación

El frontend muestra presente, retraso o falta según respuesta del backend.

## Subfase 18.3: Docente toma asistencia de alumnos

### Tareas

1. Mostrar grupos asignados al docente.
2. Mostrar clase activa.
3. Mostrar lista de alumnos del grupo.
4. Permitir marcar presentes.
5. Permitir marcar retrasos si corresponde.
6. Enviar asistencia al backend.
7. Mostrar confirmación.

### Resultado esperado

Docente puede registrar asistencia de sus alumnos.

### Criterio de aceptación

El docente solo gestiona alumnos de sus grupos asignados.

## Subfase 18.4: Visualización de asistencia por rol

### Tareas

1. Administrador ve asistencia de todos los alumnos.
2. Docente ve asistencia de sus alumnos.
3. Alumno ve su historial.
4. Mostrar filtros por fecha.
5. Mostrar filtros por estado.
6. Mostrar reportes visuales.

### Resultado esperado

Asistencia visible según rol.

### Criterio de aceptación

Ningún rol ve información no permitida.

---

# Fase 19: Exámenes y preguntas

## Objetivo

Permitir que el administrador cree exámenes, preguntas y opciones de respuesta.

## Reglas

1. El alumno da examen solo si está habilitado.
2. Las preguntas son de selección múltiple.
3. Materias: Física, Matemáticas, Computación e Inglés.
4. Porcentajes de ejemplo: Física 25%, Matemáticas 30%, Computación 30%, Inglés 15%.
5. La suma de porcentajes debe ser 100%.
6. Solo se toman 3 exámenes por estudiante en una gestión.

## Subfase 19.1: Listado de exámenes

### Tareas

1. Listar exámenes.
2. Filtrar por gestión.
3. Filtrar por parcial.
4. Mostrar estado habilitado/no habilitado.
5. Mostrar acciones.

### Resultado esperado

Administrador puede ver exámenes.

### Criterio de aceptación

La tabla usa datos del backend.

## Subfase 19.2: Crear examen

### Tareas

1. Crear formulario de examen.
2. Seleccionar gestión.
3. Seleccionar parcial 1, 2 o 3.
4. Definir estado habilitado o no habilitado.
5. Enviar al backend.
6. Mostrar confirmación.

### Resultado esperado

Examen creado.

### Criterio de aceptación

No se permite crear más de lo permitido según backend.

## Subfase 19.3: Porcentajes por materia

### Tareas

1. Crear formulario de porcentajes.
2. Ingresar Física.
3. Ingresar Matemáticas.
4. Ingresar Computación.
5. Ingresar Inglés.
6. Validar suma 100% visualmente.
7. Enviar al backend.
8. Mostrar error si no suma 100%.

### Resultado esperado

Porcentajes registrados correctamente.

### Criterio de aceptación

La suma debe ser 100%.

## Subfase 19.4: Preguntas de selección múltiple

### Tareas

1. Crear pregunta.
2. Seleccionar examen.
3. Seleccionar materia.
4. Agregar texto de pregunta.
5. Agregar opciones de respuesta.
6. Marcar respuesta correcta.
7. Editar pregunta.
8. Desactivar o eliminar pregunta si corresponde.

### Resultado esperado

Banco de preguntas del examen listo.

### Criterio de aceptación

Cada pregunta tiene opciones y una respuesta correcta.

## Subfase 19.5: Habilitar o deshabilitar examen

### Tareas

1. Mostrar estado del examen.
2. Permitir habilitar.
3. Permitir deshabilitar.
4. Mostrar confirmación.
5. Actualizar estado.

### Resultado esperado

Administrador controla cuándo se puede rendir examen.

### Criterio de aceptación

El alumno solo ve exámenes habilitados.

---

# Fase 20: Resolución de examen por alumno

## Objetivo

Permitir al alumno resolver el examen habilitado.

## Subfase 20.1: Vista de exámenes habilitados

### Tareas

1. Consultar backend.
2. Mostrar solo exámenes habilitados.
3. Bloquear examen ya rendido si corresponde.
4. Mostrar parcial.
5. Mostrar gestión.
6. Mostrar botón iniciar.

### Resultado esperado

Alumno ve únicamente exámenes permitidos.

### Criterio de aceptación

El backend determina qué examen puede rendir el alumno.

## Subfase 20.2: Pantalla de examen

### Tareas

1. Mostrar preguntas.
2. Agrupar o identificar preguntas por materia.
3. Mostrar opciones de selección múltiple.
4. Permitir elegir una respuesta por pregunta.
5. Mostrar progreso.
6. Evitar pérdida accidental de respuestas si es posible.

### Resultado esperado

Alumno puede responder examen.

### Criterio de aceptación

Cada pregunta permite seleccionar una opción.

## Subfase 20.3: Validación antes de enviar

### Tareas

1. Verificar preguntas respondidas.
2. Mostrar advertencia si hay preguntas sin responder.
3. Pedir confirmación antes de enviar.
4. Evitar doble envío.

### Resultado esperado

Alumno confirma envío del examen.

### Criterio de aceptación

No se envía el examen dos veces desde la interfaz.

## Subfase 20.4: Envío de respuestas

### Tareas

1. Enviar respuestas al backend.
2. Bloquear cambios después del envío.
3. Mostrar resultado si backend lo permite.
4. Mostrar mensaje de registro exitoso.
5. Redirigir a vista de exámenes o notas si corresponde.

### Resultado esperado

Respuestas enviadas y registradas.

### Criterio de aceptación

Las respuestas quedan registradas en backend.

---

# Fase 21: Notas, promedios y estado final

## Objetivo

Mostrar notas, promedios y estado aprobado/reprobado.

## Reglas

1. Las notas están entre 0 y 100.
2. El promedio final es la suma de los 3 parciales dividido entre 3.
3. Aprobado si promedio final es mayor o igual a 60.
4. Reprobado si promedio final es menor a 60.

## Subfase 21.1: Vista administrativa de notas

### Tareas

1. Mostrar alumnos.
2. Mostrar parciales.
3. Mostrar nota de cada parcial.
4. Mostrar promedio final.
5. Mostrar estado aprobado/reprobado.
6. Filtrar por grupo.
7. Filtrar por carrera.
8. Filtrar por estado.

### Resultado esperado

Administrador puede controlar notas y estados.

### Criterio de aceptación

Promedio y estado vienen calculados desde backend o validados por backend.

## Subfase 21.2: Vista alumno de notas

### Tareas

1. Mostrar notas propias.
2. Mostrar parcial 1.
3. Mostrar parcial 2.
4. Mostrar parcial 3.
5. Mostrar promedio.
6. Mostrar estado final si corresponde.

### Resultado esperado

Alumno ve sus notas.

### Criterio de aceptación

El alumno solo ve sus propias notas.

## Subfase 21.3: Indicadores visuales de estado

### Tareas

1. Usar `BadgeEstado`.
2. Mostrar aprobado.
3. Mostrar reprobado.
4. Mostrar pendiente si todavía no tiene los 3 parciales.
5. Mostrar estado de forma clara.

### Resultado esperado

Estados visuales comprensibles.

### Criterio de aceptación

El usuario identifica rápidamente su estado.

---

# Fase 22: Asignación final de carrera

## Objetivo

Mostrar el proceso de admisión final por mayor nota y cupos.

## Reglas

1. Siempre se prioriza por mayor nota.
2. Primero se intenta asignar a primera opción.
3. Si está llena, se intenta segunda opción.
4. Si ambas están llenas, se asigna a la carrera con menos personas.

## Subfase 22.1: Vista de alumnos aprobados

### Tareas

1. Mostrar alumnos aprobados.
2. Mostrar promedio.
3. Mostrar primera opción.
4. Mostrar segunda opción.
5. Mostrar carrera asignada si existe.
6. Mostrar cupos disponibles.

### Resultado esperado

Administrador visualiza alumnos aptos para asignación.

### Criterio de aceptación

La tabla permite revisar datos antes de ejecutar asignación.

## Subfase 22.2: Ejecutar asignación final

### Tareas

1. Crear botón de ejecutar asignación.
2. Mostrar confirmación.
3. Consumir endpoint del backend.
4. Mostrar resultado.
5. Actualizar tabla.

### Resultado esperado

Asignación final visible en frontend.

### Criterio de aceptación

La lógica de asignación la realiza backend; el frontend solo la solicita y muestra resultados.

## Subfase 22.3: Visualización por carrera

### Tareas

1. Mostrar admitidos por carrera.
2. Mostrar cupos usados.
3. Mostrar cupos disponibles.
4. Mostrar alumnos asignados por primera opción.
5. Mostrar alumnos asignados por segunda opción.
6. Mostrar alumnos asignados por carrera con menos personas.

### Resultado esperado

Administrador entiende cómo quedaron distribuidos los alumnos.

### Criterio de aceptación

La asignación final es clara y auditable visualmente.

---

# Fase 23: Reportes administrativos

## Objetivo

Crear vistas para reportes del sistema.

## Reportes obligatorios

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

## Subfase 23.1: Menú de reportes

### Tareas

1. Crear vista principal de reportes.
2. Mostrar tarjetas o lista de reportes disponibles.
3. Separar reportes académicos.
4. Separar reportes de asistencia.
5. Separar reportes de grupos.
6. Separar reportes de pagos si corresponde.

### Resultado esperado

Administrador accede a reportes desde un menú ordenado.

### Criterio de aceptación

Todos los reportes obligatorios están visibles.

## Subfase 23.2: Filtros de reportes

### Tareas

1. Crear filtros por gestión.
2. Crear filtros por carrera.
3. Crear filtros por grupo.
4. Crear filtros por fecha.
5. Crear filtros por estado.
6. Enviar filtros al backend.

### Resultado esperado

Reportes filtrables.

### Criterio de aceptación

Los filtros afectan los datos mostrados y exportados.

## Subfase 23.3: Visualización de reportes

### Tareas

1. Mostrar tablas.
2. Mostrar gráficos si corresponde.
3. Mostrar totales.
4. Mostrar estados vacíos.
5. Mostrar errores.

### Resultado esperado

Reportes visibles para administrador.

### Criterio de aceptación

Los reportes consumen datos reales del backend.

## Subfase 23.4: Exportación PDF

### Tareas

1. Crear botón exportar PDF.
2. Enviar filtros al backend.
3. Recibir archivo PDF.
4. Descargar archivo.
5. Mostrar estado de carga.

### Resultado esperado

Reportes exportables a PDF.

### Criterio de aceptación

La generación del archivo la realiza backend Laravel.

## Subfase 23.5: Exportación Excel

### Tareas

1. Crear botón exportar Excel.
2. Enviar filtros al backend.
3. Recibir archivo Excel.
4. Descargar archivo.
5. Mostrar estado de carga.

### Resultado esperado

Reportes exportables a Excel.

### Criterio de aceptación

La generación del archivo la realiza backend Laravel.

---

# Fase 24: Comandos de voz para reportes

## Objetivo

Permitir generar reportes usando comandos de voz.

## Tecnología

- Web Speech API.

## Subfase 24.1: Hook de comando de voz

### Tareas

1. Crear `useComandoVoz.js`.
2. Verificar compatibilidad del navegador.
3. Iniciar captura de voz.
4. Detener captura de voz.
5. Convertir voz a texto.
6. Manejar errores de micrófono.

### Resultado esperado

Hook reutilizable para comandos de voz.

### Criterio de aceptación

El hook puede usarse en el módulo de reportes.

## Subfase 24.2: Botón de micrófono

### Tareas

1. Crear botón de micrófono.
2. Mostrar estado escuchando.
3. Mostrar texto reconocido.
4. Permitir limpiar texto.

### Resultado esperado

Administrador puede dictar comandos.

### Criterio de aceptación

El usuario ve claramente qué texto fue reconocido.

## Subfase 24.3: Interpretación de comando

### Ejemplo

```text
listar alumnos reprobados y aprobados
```

### Tareas

1. Enviar texto al backend si corresponde.
2. Interpretar reporte solicitado según respuesta.
3. Mostrar reporte generado.
4. Permitir elegir PDF.
5. Permitir elegir Excel.

### Resultado esperado

Reportes generados por comando de voz.

### Criterio de aceptación

El comando de voz no reemplaza permisos; solo ayuda al administrador a solicitar reportes.

---

# Fase 25: Carga masiva Excel/CSV

## Objetivo

Permitir carga masiva desde archivos Excel o CSV.

## Subfase 25.1: Componente de carga masiva

### Tareas

1. Crear componente de carga.
2. Validar extensión Excel.
3. Validar extensión CSV.
4. Mostrar nombre del archivo.
5. Permitir reemplazar archivo antes de subir.
6. Mostrar error si el archivo no es válido.

### Resultado esperado

Componente de carga masiva disponible.

### Criterio de aceptación

Solo se permiten archivos Excel o CSV.

## Subfase 25.2: Envío al backend

### Tareas

1. Preparar `FormData`.
2. Enviar archivo al backend.
3. Mostrar estado de procesamiento.
4. Bloquear doble envío.
5. Mostrar respuesta.

### Resultado esperado

Archivo cargado al backend.

### Criterio de aceptación

El backend recibe el archivo correctamente.

## Subfase 25.3: Resultado de carga

### Tareas

1. Mostrar registros válidos.
2. Mostrar registros con errores.
3. Mostrar detalle por fila.
4. Mostrar cantidad total procesada.
5. Permitir descargar resultado si backend lo permite.

### Resultado esperado

Administrador puede revisar carga masiva.

### Criterio de aceptación

Los errores de carga son claros y corregibles.

---

# Fase 26: Panel docente

## Objetivo

Crear interfaz específica para docentes.

## Funciones del docente

1. Ver perfil.
2. Ver carga horaria.
3. Ver grupos asignados.
4. Ver materias asignadas.
5. Marcar asistencia de entrada.
6. Marcar salida.
7. Tomar asistencia a sus alumnos.
8. Ver asistencia de sus alumnos.

## Subfase 26.1: Dashboard docente

### Tareas

1. Crear panel docente.
2. Mostrar resumen de clases.
3. Mostrar próxima clase.
4. Mostrar grupos asignados.
5. Mostrar materias asignadas.
6. Mostrar accesos rápidos.

### Resultado esperado

Docente tiene panel propio.

### Criterio de aceptación

El docente no ve funciones administrativas completas.

## Subfase 26.2: Perfil docente

### Tareas

1. Mostrar datos personales.
2. Mostrar correo.
3. Mostrar celular.
4. Mostrar requisitos académicos si corresponde.
5. Mostrar estado de usuario.

### Resultado esperado

Docente visualiza su perfil.

### Criterio de aceptación

El docente ve solo su información.

## Subfase 26.3: Horarios docente

### Tareas

1. Mostrar horarios del docente.
2. Mostrar materia.
3. Mostrar grupo.
4. Mostrar aula.
5. Mostrar día.
6. Mostrar turno.
7. Mostrar periodo.

### Resultado esperado

Docente ve su carga horaria.

### Criterio de aceptación

Los horarios corresponden al docente autenticado.

## Subfase 26.4: Asistencia docente

### Tareas

1. Mostrar clase activa.
2. Permitir marcar entrada.
3. Permitir marcar salida.
4. Mostrar estado de asistencia.

### Resultado esperado

Docente controla su asistencia.

### Criterio de aceptación

El marcado depende del horario definido por administrador.

## Subfase 26.5: Asistencia de alumnos del docente

### Tareas

1. Mostrar grupos asignados.
2. Mostrar alumnos del grupo.
3. Tomar asistencia.
4. Ver historial de asistencia de sus alumnos.

### Resultado esperado

Docente gestiona asistencia de sus alumnos.

### Criterio de aceptación

El docente no ve alumnos fuera de sus grupos.

---

# Fase 27: Panel alumno

## Objetivo

Crear interfaz específica para alumnos.

## Funciones del alumno

1. Iniciar sesión con código.
2. Ver perfil.
3. Ver horarios.
4. Marcar asistencia.
5. Ver asistencias.
6. Dar examen si está habilitado.
7. Ver notas si corresponde.

## Subfase 27.1: Dashboard alumno

### Tareas

1. Crear panel alumno.
2. Mostrar perfil resumido.
3. Mostrar código de alumno.
4. Mostrar horario próximo.
5. Mostrar estado de examen.
6. Mostrar accesos rápidos.

### Resultado esperado

Alumno tiene panel propio.

### Criterio de aceptación

El alumno solo ve su información.

## Subfase 27.2: Perfil alumno

### Tareas

1. Mostrar datos personales.
2. Mostrar código automático.
3. Mostrar grupo.
4. Mostrar carrera o estado académico si corresponde.
5. Mostrar información relevante.

### Resultado esperado

Alumno visualiza su perfil.

### Criterio de aceptación

El perfil pertenece al alumno autenticado.

## Subfase 27.3: Horarios alumno

### Tareas

1. Mostrar horarios.
2. Mostrar materia.
3. Mostrar docente.
4. Mostrar aula.
5. Mostrar día.
6. Mostrar turno.
7. Mostrar periodo.

### Resultado esperado

Alumno ve sus horarios.

### Criterio de aceptación

Los horarios corresponden al grupo del alumno.

## Subfase 27.4: Asistencia alumno

### Tareas

1. Mostrar clase activa.
2. Marcar asistencia.
3. Mostrar estado.
4. Ver historial.

### Resultado esperado

Alumno gestiona su asistencia.

### Criterio de aceptación

El alumno solo puede marcar según horario.

## Subfase 27.5: Exámenes y notas del alumno

### Tareas

1. Mostrar exámenes habilitados.
2. Permitir rendir examen.
3. Mostrar exámenes ya rendidos.
4. Mostrar notas propias.
5. Mostrar promedio si corresponde.
6. Mostrar estado final si corresponde.

### Resultado esperado

Alumno puede rendir examen habilitado y consultar notas.

### Criterio de aceptación

El alumno no puede acceder a exámenes no habilitados.

---

# Fase 28: Responsive design y experiencia de usuario

## Objetivo

Asegurar que la aplicación funcione correctamente en PC y dispositivos móviles.

## Subfase 28.1: Adaptación responsive de layout

### Tareas

1. Adaptar sidebar a móvil.
2. Crear menú colapsable.
3. Adaptar navbar.
4. Adaptar breadcrumbs.
5. Verificar panel administrador.
6. Verificar panel docente.
7. Verificar panel alumno.

### Resultado esperado

Layouts funcionales en PC y móvil.

### Criterio de aceptación

La aplicación es usable desde navegador en PC, Android e iOS.

## Subfase 28.2: Adaptación responsive de tablas

### Tareas

1. Permitir scroll horizontal si es necesario.
2. Ocultar columnas secundarias en móvil si corresponde.
3. Mantener acciones accesibles.
4. Mantener filtros utilizables.

### Resultado esperado

Tablas usables en pantallas pequeñas.

### Criterio de aceptación

No se rompe la interfaz en móvil.

## Subfase 28.3: Adaptación responsive de formularios

### Tareas

1. Adaptar campos a una columna en móvil.
2. Mantener errores visibles.
3. Mantener botones accesibles.
4. Evitar formularios desbordados.

### Resultado esperado

Formularios usables en PC y móvil.

### Criterio de aceptación

El usuario puede completar formularios desde móvil.

## Subfase 28.4: Estados visuales obligatorios

### Estados

1. Cargando.
2. Sin datos.
3. Error.
4. Éxito.
5. Acceso denegado.
6. Sesión expirada.

### Tareas

1. Crear componentes para estados.
2. Reutilizarlos en módulos.
3. Mostrar mensajes claros.

### Resultado esperado

La interfaz comunica correctamente cada estado.

### Criterio de aceptación

El usuario entiende qué está pasando en cada pantalla.

---

# Fase 29: Pruebas del frontend

## Objetivo

Probar que el frontend cumple con los módulos y se conecta correctamente con Laravel.

## Subfase 29.1: Pruebas de rutas públicas

### Tareas

1. Probar login.
2. Probar registro público si existe.
3. Probar retorno de pago.
4. Probar acceso sin sesión.

### Resultado esperado

Rutas públicas funcionan.

### Criterio de aceptación

No requieren sesión.

## Subfase 29.2: Pruebas de rutas privadas

### Tareas

1. Probar acceso con sesión.
2. Probar acceso sin sesión.
3. Probar sesión expirada.
4. Probar redirección a login.

### Resultado esperado

Rutas privadas protegidas.

### Criterio de aceptación

No se accede a pantallas internas sin autenticación.

## Subfase 29.3: Pruebas por rol

### Tareas

1. Probar usuario administrador.
2. Probar usuario docente.
3. Probar usuario alumno.
4. Probar acceso denegado.
5. Probar menús por rol.

### Resultado esperado

Roles correctamente aplicados.

### Criterio de aceptación

Cada rol accede únicamente a lo permitido.

## Subfase 29.4: Pruebas de integración con backend

### Módulos a probar

1. Login.
2. Postulantes.
3. Requisitos.
4. Pagos.
5. Alumnos.
6. Docentes.
7. Gestión académica.
8. Carreras.
9. Cupos.
10. Materias.
11. Grupos.
12. Aulas.
13. Horarios.
14. Asistencia.
15. Exámenes.
16. Notas.
17. Reportes.
18. Carga masiva.

### Resultado esperado

Frontend integrado con backend Laravel.

### Criterio de aceptación

Cada módulo consume endpoints reales.

## Subfase 29.5: Pruebas de validación

### Tareas

1. Probar campos vacíos.
2. Probar correos inválidos.
3. Probar notas fuera de rango.
4. Probar porcentajes que no suman 100%.
5. Probar archivos inválidos.
6. Probar accesos no permitidos.
7. Probar errores 422 del backend.

### Resultado esperado

Validaciones frontend y backend funcionan juntas.

### Criterio de aceptación

El frontend muestra errores claros y backend mantiene validación final.

## Subfase 29.6: Pruebas visuales y responsive

### Tareas

1. Probar en escritorio.
2. Probar en pantalla mediana.
3. Probar en móvil.
4. Probar modales.
5. Probar tablas.
6. Probar formularios.

### Resultado esperado

Interfaz usable en diferentes tamaños de pantalla.

### Criterio de aceptación

No hay pantallas rotas ni elementos inaccesibles.

---

# Fase 30: Preparación para despliegue en Vercel

## Objetivo

Preparar el frontend para despliegue futuro en Vercel.

## Subfase 30.1: Variables de producción

### Tareas

1. Configurar URL del backend en Railway cuando corresponda.
2. Configurar claves públicas.
3. Verificar variables en Vercel.
4. Verificar que no existan claves privadas en frontend.

### Resultado esperado

Variables listas para producción.

### Criterio de aceptación

El build usa variables correctas.

## Subfase 30.2: Verificación de CORS con backend Laravel

### Tareas

1. Confirmar dominio frontend.
2. Confirmar dominio backend.
3. Probar llamadas desde frontend desplegado.
4. Documentar errores si aparecen.

### Resultado esperado

Frontend desplegado puede consumir backend.

### Criterio de aceptación

No hay bloqueo CORS en producción.

## Subfase 30.3: Build de producción

### Tareas

1. Ejecutar build.
2. Corregir errores.
3. Revisar rutas.
4. Revisar variables.
5. Revisar assets.

### Comando sugerido

```bash
npm run build
```

### Resultado esperado

Aplicación lista para desplegar.

### Criterio de aceptación

El build termina sin errores.

## Subfase 30.4: Revisión final antes de despliegue

### Tareas

1. Revisar login.
2. Revisar rutas.
3. Revisar conexión con API.
4. Revisar permisos.
5. Revisar dashboard.
6. Revisar módulos principales.
7. Revisar exportaciones.
8. Revisar comandos de voz si el navegador lo soporta.

### Resultado esperado

Frontend listo para producción.

### Criterio de aceptación

No se despliega con módulos críticos rotos.

---

## 8. Orden recomendado de trabajo frontend-backend

El frontend y backend pueden desarrollarse a la par, pero por módulos.

Orden recomendado:

1. Backend Laravel base.
2. Frontend React base.
3. Autenticación backend y frontend.
4. Postulantes backend y frontend.
5. Requisitos y Cloudinary backend y frontend.
6. Stripe backend y frontend.
7. Conversión a alumno backend y frontend.
8. Gestión académica backend y frontend.
9. Docentes backend y frontend.
10. Materias, grupos y aulas backend y frontend.
11. Horarios backend y frontend.
12. Asistencia backend y frontend.
13. Exámenes backend y frontend.
14. Notas backend y frontend.
15. Admisión final backend y frontend.
16. Reportes backend y frontend.
17. Exportaciones backend y frontend.
18. Comandos de voz frontend y backend.
19. Carga masiva backend y frontend.
20. Dashboard backend y frontend.
21. Pruebas completas.
22. Preparación para despliegue.

Reglas:

- No avanzar un módulo frontend como terminado si no consume el backend correspondiente.
- Puede usarse mock temporal durante diseño visual.
- El mock temporal debe reemplazarse por conexión real al backend Laravel.
- Ninguna pantalla debe considerarse terminada si no maneja cargando, error, éxito y vacío cuando corresponda.

---

## 9. Reglas obligatorias de conexión con Laravel

El frontend debe conectarse con Laravel para:

1. Login.
2. Logout.
3. Perfil autenticado.
4. Validación Firebase.
5. CRUD de usuarios.
6. CRUD de postulantes.
7. Subida de documentos.
8. Validación de requisitos.
9. Pago con Stripe.
10. Consulta de pagos.
11. Conversión a alumno.
12. CRUD de docentes.
13. CRUD de gestiones.
14. CRUD de carreras.
15. CRUD de cupos.
16. CRUD de materias.
17. CRUD de grupos.
18. CRUD de aulas.
19. CRUD de horarios.
20. Asignaciones docente-materia-grupo.
21. Asistencia docente.
22. Asistencia alumno.
23. Exámenes.
24. Preguntas.
25. Opciones.
26. Resolución de examen.
27. Notas.
28. Promedios.
29. Estado final.
30. Asignación de carrera.
31. Reportes.
32. Exportaciones.
33. Comandos de voz.
34. Carga masiva.
35. Dashboard.

---

## 10. Reglas de reutilización obligatoria

Para evitar repetir código, el frontend debe cumplir:

1. Todos los formularios deben usar componentes de `components/forms`.
2. Todos los botones deben usar `Boton`.
3. Todos los inputs deben usar componentes reutilizables.
4. Todos los listados deben usar `TablaBase` o una variante reutilizable.
5. Todos los modales deben usar `Modal`.
6. Todas las confirmaciones deben usar `ConfirmDialog`.
7. Todos los estados visuales deben usar componentes comunes.
8. Todas las llamadas HTTP deben pasar por servicios.
9. Todas las fechas deben formatearse con utilidades comunes.
10. Todos los errores deben manejarse con utilidad común.
11. No se debe copiar y pegar lógica de un módulo a otro.

---

## 11. Resultado final esperado

Al finalizar todas las fases y subfases, el frontend debe ser una aplicación web completa, modular, responsive y conectada al backend Laravel.

Debe permitir:

1. Que el administrador gestione todo el sistema.
2. Que el administrador cree administradores.
3. Que el administrador gestione postulantes.
4. Que el administrador valide requisitos.
5. Que el administrador revise pagos.
6. Que el administrador convierta postulantes en alumnos.
7. Que el administrador gestione docentes.
8. Que el administrador gestione gestiones académicas.
9. Que el administrador gestione carreras y cupos.
10. Que el administrador gestione materias, grupos y aulas.
11. Que el administrador gestione horarios, días, turnos y periodos.
12. Que el administrador gestione asignaciones docente-materia-grupo.
13. Que el administrador visualice asistencia de docentes y alumnos.
14. Que el administrador cree y habilite exámenes.
15. Que el administrador gestione preguntas de selección múltiple.
16. Que el administrador visualice notas, promedios y estados.
17. Que el administrador ejecute o visualice asignación final por carrera.
18. Que el administrador genere reportes.
19. Que el administrador exporte reportes a PDF y Excel.
20. Que el administrador genere reportes mediante comandos de voz.
21. Que el administrador realice carga masiva Excel/CSV.
22. Que el docente vea su perfil.
23. Que el docente vea su carga horaria.
24. Que el docente marque asistencia de entrada.
25. Que el docente marque salida.
26. Que el docente tome asistencia de sus alumnos.
27. Que el docente vea asistencia de sus alumnos.
28. Que el alumno ingrese con su código automático.
29. Que el alumno vea su perfil.
30. Que el alumno vea sus horarios.
31. Que el alumno marque asistencia.
32. Que el alumno vea sus asistencias.
33. Que el alumno rinda examen si está habilitado.
34. Que el alumno vea sus notas si corresponde.
35. Que todos los módulos consuman endpoints reales del backend Laravel.
36. Que los componentes se reutilicen para evitar repetir código.
37. Que la aplicación esté lista para despliegue futuro en Vercel.

---

## 12. Cierre del documento

Este archivo `frontend_subfases.md` es la guía definitiva para construir el frontend por fases y subfases.

Debe respetarse en cada etapa del desarrollo.

No se deben eliminar módulos definidos.
No se deben agregar roles nuevos.
No se deben inventar flujos que contradigan el contexto.
No se debe considerar terminado un módulo si no está conectado al backend Laravel.
No se debe duplicar código si puede resolverse con componentes, hooks o servicios reutilizables.

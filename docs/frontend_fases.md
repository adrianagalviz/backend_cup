# frontend_fases.md

# Plan de desarrollo por fases del Frontend - Aplicación Web de Admisión Universitaria (CUP) para la FICCT

## 1. Objetivo del archivo

Este archivo define el plan de desarrollo por fases para construir el frontend de la **Aplicación Web de Admisión Universitaria (CUP) para la FICCT**.

El frontend será desarrollado con:

- React JS.
- Vite.
- Node 22.22.2.
- Tailwind CSS versión 4.
- Librerías de apoyo para vistas, componentes, formularios, tablas, validaciones, peticiones HTTP, gráficos, iconos, notificaciones y manejo de estados.

El backend estará desarrollado obligatoriamente con:

- PHP.
- Laravel.
- API REST.
- PostgreSQL 16 como base de datos.
- pgAdmin 4 para administración de base de datos.

El frontend debe conectarse obligatoriamente con el backend mediante endpoints API REST. No se debe dejar el frontend aislado con datos estáticos como solución final.

Este documento debe servir como guía definitiva para implementar el frontend por fases, respetando el contexto funcional del sistema CUP-FICCT, la base de datos definida y el backend planificado por fases y subfases.

---

## 2. Alcance obligatorio del frontend

El frontend debe cubrir todos los módulos funcionales definidos para el sistema:

1. Autenticación.
2. Control de sesión.
3. Gestión de roles visuales: administrador, docente y alumno.
4. Panel administrativo.
5. Gestión de administradores.
6. Gestión de postulantes.
7. Validación de requisitos.
8. Subida y visualización de título de bachiller mediante Cloudinary.
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

## 3. Reglas obligatorias del proyecto frontend

El frontend debe cumplir las siguientes reglas:

1. Debe estar hecho con React JS.
2. Debe usar Vite.
3. Debe usar Node 22.22.2.
4. Debe usar Tailwind CSS versión 4.
5. Debe consumir el backend hecho en PHP y Laravel.
6. Debe conectarse al backend mediante API REST.
7. Debe centralizar las peticiones HTTP para no repetir código.
8. Debe reutilizar componentes obligatoriamente.
9. No debe duplicar formularios, tablas, botones, modales ni layouts.
10. Debe separar el sistema por módulos.
11. Debe tener rutas protegidas por autenticación.
12. Debe tener rutas protegidas por rol.
13. Solo existirán tres roles visibles en el sistema: administrador, docente y alumno.
14. El administrador tendrá acceso completo.
15. El docente solo verá sus funciones permitidas.
16. El alumno solo verá sus funciones permitidas.
17. El alumno iniciará sesión usando su código automático.
18. El código automático del alumno se genera en backend, pero debe poder mostrarse en frontend.
19. El sistema debe permitir login tradicional y login con Google mediante Firebase Authentication, según lo definido para simplificar validación de correo.
20. El frontend debe enviar el token de Firebase al backend Laravel cuando corresponda.
21. El frontend debe integrarse con Stripe para iniciar o redirigir al flujo de pago.
22. El frontend debe mostrar estados de pago.
23. El frontend debe permitir subir imagen del título de bachiller hacia el flujo conectado con Cloudinary mediante el backend.
24. El frontend debe permitir dar examen solo cuando el backend indique que el examen está habilitado.
25. El frontend debe impedir acciones visualmente cuando el rol no tenga permiso, pero la validación final siempre será del backend.
26. El frontend debe manejar errores del backend de forma clara.
27. El frontend debe mostrar mensajes de éxito, advertencia y error.
28. El frontend debe ser responsive para PC y dispositivos móviles.
29. El frontend debe estar preparado para futuro despliegue en Vercel.
30. El frontend debe consumir variables de entorno para la URL de la API y claves públicas necesarias.

---

## 4. Librerías recomendadas para facilitar el frontend

Las librerías deben usarse para facilitar el desarrollo, evitar código repetido y mantener una estructura profesional.

### 4.1 Peticiones HTTP

Se recomienda usar:

- Axios.

Objetivo:

- Centralizar llamadas al backend.
- Agregar token automáticamente.
- Manejar errores globales.
- Configurar la URL base de la API.

### 4.2 Rutas

Se recomienda usar:

- React Router DOM.

Objetivo:

- Manejar rutas públicas.
- Manejar rutas protegidas.
- Manejar rutas por rol.
- Separar panel administrativo, panel docente y panel alumno.

### 4.3 Formularios

Se recomienda usar:

- React Hook Form.
- Zod o una librería equivalente para validaciones.

Objetivo:

- Crear formularios reutilizables.
- Validar campos obligatorios.
- Validar correos.
- Validar fechas.
- Validar notas entre 0 y 100.
- Validar porcentajes de examen.
- Validar archivos.
- Evitar repetir lógica de formularios.

### 4.4 Tablas

Se recomienda usar:

- TanStack Table o una librería equivalente de tablas.

Objetivo:

- Listar postulantes.
- Listar alumnos.
- Listar docentes.
- Listar pagos.
- Listar grupos.
- Listar horarios.
- Listar asistencias.
- Listar exámenes.
- Listar reportes.
- Agregar paginación.
- Agregar filtros.
- Agregar búsqueda.
- Agregar ordenamiento.
- Reutilizar una tabla base en todo el sistema.

### 4.5 Estado del servidor

Se recomienda usar:

- TanStack Query o una solución equivalente.

Objetivo:

- Consultar datos del backend.
- Cachear datos.
- Refrescar listados después de crear, editar o eliminar.
- Manejar estados de carga.
- Manejar estados de error.

### 4.6 Componentes visuales

Se recomienda usar una librería compatible con React y Tailwind CSS, por ejemplo:

- shadcn/ui.
- Headless UI.
- Radix UI.
- Otra librería equivalente que permita componentes accesibles y personalizables.

Objetivo:

- Crear modales.
- Crear menús.
- Crear dropdowns.
- Crear tabs.
- Crear popovers.
- Crear dialogs.
- Crear inputs consistentes.
- Crear selectores.
- Crear componentes reutilizables.

### 4.7 Iconos

Se recomienda usar:

- Lucide React o una librería equivalente.

Objetivo:

- Usar iconos en sidebar.
- Usar iconos en botones.
- Usar iconos en tarjetas de dashboard.
- Mejorar la interfaz sin crear iconos manuales.

### 4.8 Notificaciones

Se recomienda usar:

- Sonner.
- React Hot Toast.
- Otra librería equivalente.

Objetivo:

- Mostrar mensajes de éxito.
- Mostrar errores.
- Mostrar advertencias.
- Confirmar acciones.

### 4.9 Gráficos

Se recomienda usar:

- Recharts.
- Otra librería de gráficos compatible con React.

Objetivo:

- Dashboard administrativo.
- Estadísticas de inscritos.
- Estadísticas de aprobados.
- Estadísticas de reprobados.
- Estadísticas por materia.
- Estadísticas de asistencia.
- Estadísticas de cupos.

### 4.10 Fechas y horas

Se recomienda usar:

- date-fns.
- Day.js.

Objetivo:

- Formatear fechas.
- Formatear horas.
- Comparar horarios.
- Mostrar horarios de clase.
- Mostrar asistencia por fecha.

### 4.11 PDF, Excel y descargas

La generación de PDF y Excel se realizará principalmente desde el backend Laravel.

El frontend debe:

- Enviar filtros al backend.
- Solicitar exportación PDF.
- Solicitar exportación Excel.
- Descargar archivos generados.
- Mostrar estados de carga durante la generación.

### 4.12 Comandos de voz

Se usará:

- Web Speech API del navegador.

Objetivo:

- Capturar voz del administrador.
- Convertir voz a texto.
- Enviar texto interpretado al backend si corresponde.
- Generar reportes por comando de voz.

---

## 5. Estructura recomendada de carpetas

La estructura debe ser modular, clara y fácil de mantener.

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
    pagos.service.js
    alumnos.service.js
    docentes.service.js
    horarios.service.js
    asistencia.service.js
    examenes.service.js
    reportes.service.js
  styles/
    index.css
  main.jsx
```

Regla obligatoria:

- Los componentes comunes deben reutilizarse en todos los módulos.
- No se debe crear una tabla distinta para cada pantalla si se puede usar `TablaBase`.
- No se debe repetir lógica de formularios si se puede reutilizar un componente o hook.
- No se debe repetir lógica de llamadas HTTP si se puede crear un servicio.

---

## 6. Convención de nombres

Se recomienda usar nombres claros en español para carpetas, archivos, funciones y componentes, respetando lo pedido para que sea fácil de comprender.

Ejemplos:

```text
modules/postulantes/pages/ListarPostulantes.jsx
modules/postulantes/pages/RegistrarPostulante.jsx
modules/postulantes/components/FormularioPostulante.jsx
services/postulantes.service.js
hooks/useAuth.js
components/common/Tabla.jsx
components/common/Boton.jsx
```

Se deben evitar nombres ambiguos como:

```text
page1.jsx
crud.jsx
misc.js
data.jsx
cosas.jsx
```

---

## 7. Fases de desarrollo del frontend

# Fase 0: Planificación técnica del frontend

## Objetivo

Definir la arquitectura inicial del frontend y asegurar que el proyecto React esté alineado con el backend Laravel y el contexto funcional del sistema CUP-FICCT.

## Subfase 0.1: Confirmación del alcance

### Tareas

- Confirmar que el frontend será React JS con Vite.
- Confirmar Node 22.22.2.
- Confirmar Tailwind CSS versión 4.
- Confirmar que el backend será PHP con Laravel.
- Confirmar que la comunicación será mediante API REST.
- Confirmar que PostgreSQL 16 será la base de datos del backend.
- Confirmar que pgAdmin 4 se usará para administración de base de datos.
- Confirmar que solo existen tres roles: administrador, docente y alumno.
- Confirmar que el frontend debe trabajar por módulos.
- Confirmar que los componentes se deben reutilizar obligatoriamente.

### Resultado esperado

Alcance técnico del frontend confirmado y alineado con backend Laravel.

## Subfase 0.2: Definición de librerías

### Tareas

- Seleccionar librería para rutas.
- Seleccionar librería para formularios.
- Seleccionar librería para validaciones.
- Seleccionar librería para tablas.
- Seleccionar librería para peticiones HTTP.
- Seleccionar librería para gráficos.
- Seleccionar librería para notificaciones.
- Seleccionar librería para iconos.
- Seleccionar librería de componentes visuales compatible con Tailwind CSS.

### Resultado esperado

Lista de librerías definida para acelerar el desarrollo sin duplicar código.

## Subfase 0.3: Definición de comunicación con backend

### Tareas

- Definir URL base del backend Laravel.
- Definir uso de variables de entorno.
- Definir estructura de servicios por módulo.
- Definir manejo de token.
- Definir manejo de errores del backend.
- Definir estructura de respuesta esperada desde Laravel.
- Definir cómo descargar PDF y Excel desde endpoints.

### Resultado esperado

Frontend preparado para consumir APIs reales del backend.

---

# Fase 1: Creación del proyecto frontend

## Objetivo

Crear la base del proyecto React con Vite, Node 22.22.2 y Tailwind CSS 4.

## Subfase 1.1: Creación del proyecto React

### Tareas

- Crear proyecto con Vite.
- Verificar que Node sea 22.22.2.
- Instalar dependencias iniciales.
- Ejecutar el proyecto en entorno local.
- Limpiar archivos innecesarios generados por plantilla.

### Resultado esperado

Proyecto React funcionando localmente.

## Subfase 1.2: Instalación y configuración de Tailwind CSS 4

### Tareas

- Instalar Tailwind CSS versión 4.
- Configurar archivo global de estilos.
- Verificar que las clases de Tailwind funcionen.
- Definir estilos base.
- Definir estructura visual inicial.

### Resultado esperado

Tailwind CSS 4 funcionando en React.

## Subfase 1.3: Configuración de variables de entorno

### Variables mínimas

```text
VITE_API_URL=http://localhost:8000/api
VITE_FIREBASE_API_KEY=
VITE_FIREBASE_AUTH_DOMAIN=
VITE_STRIPE_PUBLIC_KEY=
```

### Tareas

- Crear archivo `.env`.
- Crear archivo `.env.example`.
- Configurar URL base del backend.
- Configurar claves públicas necesarias.
- No subir claves privadas al frontend.

### Resultado esperado

Proyecto preparado para conectarse con Laravel usando variables de entorno.

---

# Fase 2: Arquitectura base y componentes reutilizables

## Objetivo

Crear la estructura modular del frontend y los componentes base para evitar repetir código.

## Subfase 2.1: Creación de estructura de carpetas

### Tareas

- Crear carpetas principales.
- Crear carpeta `components`.
- Crear carpeta `modules`.
- Crear carpeta `services`.
- Crear carpeta `hooks`.
- Crear carpeta `lib`.
- Crear carpeta `config`.
- Crear carpeta `styles`.

### Resultado esperado

Estructura base lista para crecimiento modular.

## Subfase 2.2: Componentes comunes

### Componentes obligatorios

- Botón.
- Input.
- Select.
- Textarea.
- Modal.
- Tabla base.
- Badge de estado.
- Loader.
- Mensaje de error.
- ConfirmDialog.
- EmptyState.
- CardIndicador.

### Resultado esperado

Componentes comunes reutilizables en todo el sistema.

## Subfase 2.3: Layouts

### Layouts necesarios

- AuthLayout.
- DashboardLayout.
- Layout de administrador.
- Layout de docente.
- Layout de alumno.

### Resultado esperado

Estructura visual base para todos los roles.

---

# Fase 3: Configuración de rutas

## Objetivo

Definir rutas públicas, privadas y por rol.

## Subfase 3.1: Rutas públicas

### Rutas

- Login.
- Registro de postulante si corresponde al flujo público.
- Pantalla de pago o retorno de pago si corresponde.
- Recuperación o validación de acceso si se define desde backend.

### Resultado esperado

Rutas públicas disponibles sin sesión.

## Subfase 3.2: Rutas protegidas

### Tareas

- Crear componente `RutaProtegida`.
- Validar si existe sesión.
- Redirigir a login si no hay sesión.
- Consultar usuario autenticado desde backend.

### Resultado esperado

Rutas protegidas por autenticación.

## Subfase 3.3: Rutas por rol

### Roles

- Administrador.
- Docente.
- Alumno.

### Tareas

- Crear protección por rol.
- Bloquear rutas no permitidas.
- Redirigir según rol.
- Mostrar menú según permisos.

### Resultado esperado

Cada rol accede solo a sus pantallas permitidas.

---

# Fase 4: Configuración de conexión con backend Laravel

## Objetivo

Conectar React con el backend hecho en PHP y Laravel.

## Subfase 4.1: Cliente Axios

### Tareas

- Crear cliente Axios centralizado.
- Configurar `baseURL` con `VITE_API_URL`.
- Agregar headers comunes.
- Agregar token de sesión si existe.
- Manejar errores 401, 403, 422 y 500.

### Resultado esperado

Todas las peticiones pasan por un cliente HTTP centralizado.

## Subfase 4.2: Servicios por módulo

### Servicios mínimos

- auth.service.js.
- usuarios.service.js.
- postulantes.service.js.
- requisitos.service.js.
- pagos.service.js.
- alumnos.service.js.
- docentes.service.js.
- gestionAcademica.service.js.
- carreras.service.js.
- cupos.service.js.
- materias.service.js.
- grupos.service.js.
- aulas.service.js.
- horarios.service.js.
- asistencia.service.js.
- examenes.service.js.
- notas.service.js.
- reportes.service.js.
- cargaMasiva.service.js.

### Resultado esperado

Servicios organizados y reutilizables para consumir endpoints Laravel.

## Subfase 4.3: Manejo global de errores

### Tareas

- Mostrar errores de validación del backend.
- Mostrar errores de permisos.
- Mostrar errores de sesión expirada.
- Mostrar errores de conexión.
- Mostrar errores inesperados.

### Resultado esperado

El usuario ve mensajes claros cuando una operación falla.

---

# Fase 5: Módulo de autenticación frontend

## Objetivo

Implementar login, logout, sesión y perfil autenticado.

## Subfase 5.1: Login tradicional

### Tareas

- Crear pantalla de login.
- Validar usuario obligatorio.
- Validar contraseña obligatoria.
- Enviar credenciales al backend Laravel.
- Guardar token o sesión según respuesta del backend.
- Redirigir según rol.

### Resultado esperado

Login tradicional funcional.

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

- Crear campo para código de alumno.
- Enviar código al backend.
- Validar respuesta.
- Redirigir al panel de alumno.

### Resultado esperado

Alumno puede iniciar sesión con su código.

## Subfase 5.3: Firebase Authentication con Google

### Tareas

- Configurar Firebase en frontend.
- Crear botón de login con Google.
- Obtener token de Firebase.
- Enviar token al backend Laravel.
- Esperar validación del backend.
- Continuar flujo según respuesta.

### Resultado esperado

Login o validación con Google funcionando desde frontend.

## Subfase 5.4: Logout

### Tareas

- Crear función de cierre de sesión.
- Llamar endpoint de logout del backend.
- Limpiar token local.
- Limpiar datos de usuario.
- Redirigir al login.

### Resultado esperado

Cierre de sesión funcional.

## Subfase 5.5: Perfil autenticado

### Tareas

- Consultar `/me` o endpoint equivalente.
- Mostrar datos del usuario autenticado.
- Mostrar rol.
- Mostrar información según administrador, docente o alumno.

### Resultado esperado

Usuario autenticado visible en la interfaz.

---

# Fase 6: Panel administrativo

## Objetivo

Crear el dashboard principal para administrador.

## Subfase 6.1: Layout administrativo

### Tareas

- Crear sidebar.
- Crear navbar.
- Crear menú de módulos.
- Crear breadcrumbs.
- Crear diseño responsive.

### Resultado esperado

Panel administrativo base listo.

## Subfase 6.2: Indicadores principales

### Indicadores obligatorios

- Total inscritos.
- Total aprobados.
- Total reprobados.
- Total grupos habilitados.

### Resultado esperado

Dashboard administrativo muestra resumen general.

## Subfase 6.3: Indicadores adicionales

### Indicadores

- Indicadores de pagos.
- Indicadores de asistencia.
- Indicadores de cupos.
- Indicadores de exámenes.

### Resultado esperado

Administrador visualiza el estado general del sistema.

---

# Fase 7: Gestión de usuarios, roles y administradores

## Objetivo

Permitir al administrador gestionar usuarios y crear administradores.

## Subfase 7.1: Listado de usuarios

### Tareas

- Consumir endpoint de usuarios.
- Mostrar tabla reutilizable.
- Filtrar por rol.
- Buscar por nombre, correo o CI.

### Resultado esperado

Administrador puede ver usuarios.

## Subfase 7.2: Crear administrador

### Tareas

- Crear formulario.
- Validar campos obligatorios.
- Enviar datos al backend.
- Mostrar confirmación.
- Actualizar listado.

### Resultado esperado

Administrador puede crear otros administradores.

## Subfase 7.3: Activar, desactivar y editar usuarios

### Tareas

- Crear acciones de tabla.
- Confirmar antes de desactivar.
- Actualizar datos del usuario.
- Mantener roles permitidos.

### Resultado esperado

Gestión básica de usuarios funcionando.

---

# Fase 8: Módulo de postulantes

## Objetivo

Permitir registrar, listar, consultar, editar y eliminar lógicamente postulantes.

## Subfase 8.1: Registro de postulante

### Campos

- Cédula de identidad.
- Nombres.
- Apellidos.
- Fecha de nacimiento.
- Sexo.
- Dirección.
- Teléfono.
- Correo electrónico.
- Colegio de procedencia.
- Ciudad.
- Primera opción de carrera.
- Segunda opción de carrera.
- Título de bachiller como imagen.

### Tareas

- Crear formulario reutilizable.
- Validar campos vacíos.
- Validar correo.
- Validar que existan dos carreras.
- Validar archivo de imagen.
- Enviar datos al backend Laravel.

### Resultado esperado

Postulante registrado correctamente.

## Subfase 8.2: Listado y búsqueda

### Tareas

- Mostrar tabla de postulantes.
- Agregar búsqueda.
- Agregar filtros.
- Agregar paginación.
- Agregar acciones: ver, editar, eliminar.

### Resultado esperado

Administrador puede consultar postulantes.

## Subfase 8.3: Consulta individual

### Tareas

- Mostrar datos completos del postulante.
- Mostrar requisitos.
- Mostrar título de bachiller.
- Mostrar estado de pago.
- Mostrar estado de acceso como alumno.

### Resultado esperado

Detalle completo del postulante disponible.

## Subfase 8.4: Edición y eliminación lógica

### Tareas

- Reutilizar formulario de postulante.
- Permitir actualizar datos.
- Confirmar eliminación lógica.
- Actualizar listado.

### Resultado esperado

Gestión completa de postulantes.

---

# Fase 9: Requisitos, documentos y Cloudinary

## Objetivo

Permitir cargar, visualizar y validar documentos del postulante.

## Subfase 9.1: Subida del título de bachiller

### Tareas

- Crear componente de subida de archivo.
- Validar que sea imagen.
- Mostrar vista previa.
- Enviar archivo al backend.
- El backend gestionará Cloudinary según su implementación.

### Resultado esperado

Título de bachiller cargado y visible.

## Subfase 9.2: Validación de requisitos por administrador

### Tareas

- Mostrar requisitos del postulante.
- Permitir aprobar requisito.
- Permitir rechazar requisito.
- Mostrar observaciones si existen.
- Actualizar estado.

### Resultado esperado

Administrador valida requisitos desde interfaz.

---

# Fase 10: Pagos con Stripe

## Objetivo

Implementar el flujo visual de pago obligatorio mediante Stripe.

## Subfase 10.1: Solicitud de pago

### Tareas

- Mostrar estado de requisitos.
- Habilitar pago solo si los requisitos están cumplidos.
- Consumir endpoint Laravel para crear sesión de pago.
- Redirigir a Stripe o mostrar flujo correspondiente.

### Resultado esperado

Postulante puede iniciar pago cuando corresponde.

## Subfase 10.2: Retorno de pago

### Tareas

- Crear pantalla de pago exitoso.
- Crear pantalla de pago cancelado o fallido.
- Consultar estado real al backend.
- Mostrar mensaje claro.

### Resultado esperado

Frontend refleja correctamente el estado del pago.

## Subfase 10.3: Validación administrativa del pago

### Tareas

- Mostrar pagos pendientes.
- Mostrar pagos confirmados.
- Mostrar postulante relacionado.
- Permitir al administrador validar si corresponde.

### Resultado esperado

Administrador puede revisar pagos desde el sistema.

---

# Fase 11: Conversión de postulante a alumno

## Objetivo

Permitir que el administrador dé acceso como alumno después de requisitos y pago.

## Subfase 11.1: Validaciones visuales previas

### Tareas

- Mostrar si requisitos están completos.
- Mostrar si pago está confirmado.
- Bloquear botón si no cumple condiciones.
- Mostrar mensajes claros.

### Resultado esperado

No se permite convertir a alumno visualmente si falta requisito o pago.

## Subfase 11.2: Generación de alumno

### Tareas

- Consumir endpoint de conversión a alumno.
- Mostrar código generado por backend.
- Mostrar confirmación.
- Actualizar estado del postulante.

### Resultado esperado

Postulante convertido a alumno y código visible.

---

# Fase 12: Gestión académica, carreras y cupos

## Objetivo

Crear interfaces para gestionar gestiones académicas, carreras y cupos.

## Subfase 12.1: Gestión académica

### Tareas

- Listar gestiones.
- Crear gestión.
- Definir año.
- Definir gestión 1 o 2.
- Mostrar estado.

### Resultado esperado

Gestiones académicas administrables.

## Subfase 12.2: Carreras

### Tareas

- Listar carreras.
- Crear carrera.
- Editar carrera.
- Activar o desactivar carrera.

### Resultado esperado

Carreras administrables.

## Subfase 12.3: Cupos

### Tareas

- Asignar cupos por carrera y gestión.
- Mostrar cupos disponibles.
- Mostrar cupos usados.
- Mostrar carrera con menos personas si corresponde.

### Resultado esperado

Cupos visibles y administrables.

---

# Fase 13: Docentes

## Objetivo

Permitir la gestión completa de docentes.

## Subfase 13.1: Registro de docente

### Campos

- Nombre.
- Apellido paterno.
- Apellido materno.
- Cédula de identidad.
- Celular.
- Correo.
- Profesional en el área.
- Maestría.
- Diplomado en educación superior.

### Resultado esperado

Docente registrado con requisitos correspondientes.

## Subfase 13.2: Listado y edición

### Tareas

- Listar docentes.
- Buscar docentes.
- Ver detalle.
- Editar docente.
- Desactivar docente.

### Resultado esperado

Gestión de docentes funcional.

---

# Fase 14: Materias, grupos y aulas

## Objetivo

Permitir administrar materias, grupos y aulas.

## Subfase 14.1: Materias

### Materias obligatorias

- Física.
- Matemáticas.
- Computación.
- Inglés.

### Resultado esperado

Materias base visibles y administrables.

## Subfase 14.2: Grupos

### Reglas

- Cada grupo admite máximo 70 alumnos.
- El sistema calcula cantidad de grupos necesarios desde backend.

### Tareas

- Crear grupo.
- Listar grupos.
- Mostrar cantidad de estudiantes.
- Mostrar estudiantes por grupo.

### Resultado esperado

Grupos administrables.

## Subfase 14.3: Aulas

### Regla

El aula solo tendrá ubicación.

Ejemplo:

```text
Módulo 236, Aula 11
```

### Resultado esperado

Aulas administrables por ubicación.

---

# Fase 15: Horarios, días, turnos y periodos

## Objetivo

Permitir que el administrador defina días, turnos, periodos y horarios.

## Subfase 15.1: Días y turnos

### Tareas

- Listar días.
- Crear turnos.
- Editar turnos.
- Mostrar turnos disponibles.

### Resultado esperado

Días y turnos disponibles para horarios.

## Subfase 15.2: Periodos de 45 minutos

### Regla

Cada periodo tendrá 45 minutos.

### Tareas

- Crear periodos.
- Validar duración de 45 minutos.
- Mostrar hora de inicio y fin.

### Resultado esperado

Periodos definidos correctamente.

## Subfase 15.3: Creación de horarios

### Tareas

- Seleccionar grupo.
- Seleccionar materia.
- Seleccionar docente.
- Seleccionar aula.
- Seleccionar día.
- Seleccionar turno.
- Seleccionar periodo.
- Validar choques según respuesta del backend.

### Resultado esperado

Horarios de clase administrables.

## Subfase 15.4: Consulta de horarios por usuario

### Usuarios

- Administrador ve horarios generales.
- Docente ve sus horarios.
- Alumno ve sus horarios.

### Resultado esperado

Horarios visibles según rol.

---

# Fase 16: Asignación docente-materia-grupo

## Objetivo

Permitir asignar docentes a materias y grupos.

## Reglas

- Un docente puede ser asignado de 1 a 4 grupos.
- Un docente puede dar de 1 a 4 materias como máximo.
- El administrador realiza la asignación.

## Subfase 16.1: Crear asignación

### Tareas

- Seleccionar docente.
- Seleccionar grupo.
- Seleccionar materia.
- Enviar datos al backend.
- Mostrar error si supera límites.

### Resultado esperado

Asignación creada respetando reglas.

## Subfase 16.2: Consultar asignaciones

### Tareas

- Listar asignaciones.
- Filtrar por docente.
- Filtrar por grupo.
- Filtrar por materia.

### Resultado esperado

Asignaciones visibles para administración.

---

# Fase 17: Asistencia docente

## Objetivo

Permitir al docente marcar entrada y salida, y al administrador visualizar asistencias.

## Reglas

- La asistencia es obligatoria.
- El docente marca cuando llega a dar clase.
- El docente marca cuando finaliza su clase.
- Solo puede marcar según horario.
- Puede marcar máximo 30 minutos después de iniciar clase.
- Luego de 30 minutos es retraso.
- Pasado el horario es falta automática.

## Subfase 17.1: Panel docente para asistencia

### Tareas

- Mostrar clase activa del docente.
- Mostrar botón marcar entrada.
- Mostrar botón marcar salida.
- Mostrar estado actual.
- Bloquear si no hay horario activo.

### Resultado esperado

Docente puede marcar asistencia según horario.

## Subfase 17.2: Visualización administrativa

### Tareas

- Mostrar asistencia por docente.
- Mostrar filtros por fecha.
- Mostrar estados: presente, retraso, falta.
- Mostrar vista visual clara.

### Resultado esperado

Administrador puede ver qué docentes vinieron y cuáles no.

---

# Fase 18: Asistencia de alumnos

## Objetivo

Permitir al alumno marcar asistencia y al docente tomar asistencia a sus alumnos.

## Reglas

- La asistencia del alumno es obligatoria.
- El alumno puede marcar su asistencia.
- El docente puede tomar asistencia a sus alumnos.
- Solo se permite según horario.
- Máximo 30 minutos después de iniciar la clase.
- Luego de 30 minutos es retraso.
- Pasado el horario es falta automática.
- El administrador ve asistencia de todos.
- El docente solo ve asistencia de sus alumnos.
- El alumno solo ve su propia asistencia.

## Subfase 18.1: Alumno marca asistencia

### Tareas

- Mostrar clase actual.
- Mostrar botón marcar asistencia.
- Mostrar estado generado.
- Bloquear si no hay horario activo.

### Resultado esperado

Alumno puede marcar asistencia según horario.

## Subfase 18.2: Docente toma asistencia

### Tareas

- Mostrar lista de alumnos del grupo.
- Marcar presentes.
- Marcar retrasos si corresponde.
- Enviar asistencia al backend.

### Resultado esperado

Docente puede registrar asistencia de sus alumnos.

## Subfase 18.3: Visualización de asistencia

### Tareas

- Administrador ve todos.
- Docente ve sus alumnos.
- Alumno ve su historial.

### Resultado esperado

Asistencia visible según rol.

---

# Fase 19: Exámenes y preguntas

## Objetivo

Permitir que el administrador cree exámenes, preguntas y opciones de respuesta.

## Reglas

- El alumno da examen solo si está habilitado.
- Las preguntas son de selección múltiple.
- Materias: Física, Matemáticas, Computación e Inglés.
- Porcentajes de ejemplo: Física 25%, Matemáticas 30%, Computación 30%, Inglés 15%.
- La suma de porcentajes debe ser 100%.
- Solo se toman 3 exámenes por estudiante en una gestión.

## Subfase 19.1: Crear examen

### Tareas

- Crear formulario de examen.
- Seleccionar gestión.
- Seleccionar parcial 1, 2 o 3.
- Definir estado habilitado o no habilitado.

### Resultado esperado

Examen creado.

## Subfase 19.2: Porcentajes por materia

### Tareas

- Crear formulario de porcentajes.
- Validar suma 100%.
- Mostrar error si no suma 100%.
- Enviar porcentajes al backend.

### Resultado esperado

Porcentajes registrados correctamente.

## Subfase 19.3: Preguntas de selección múltiple

### Tareas

- Crear pregunta.
- Seleccionar materia.
- Agregar opciones.
- Marcar respuesta correcta.
- Editar pregunta.
- Eliminar o desactivar pregunta.

### Resultado esperado

Banco de preguntas del examen listo.

## Subfase 19.4: Habilitar examen

### Tareas

- Mostrar estado del examen.
- Permitir habilitar.
- Permitir deshabilitar.
- Mostrar confirmación.

### Resultado esperado

Administrador controla cuándo se puede rendir examen.

---

# Fase 20: Resolución de examen por alumno

## Objetivo

Permitir al alumno resolver el examen habilitado.

## Subfase 20.1: Ver exámenes habilitados

### Tareas

- Consultar backend.
- Mostrar solo exámenes habilitados.
- Bloquear si ya rindió cuando corresponda.

### Resultado esperado

Alumno ve únicamente exámenes permitidos.

## Subfase 20.2: Mostrar examen

### Tareas

- Mostrar preguntas.
- Mostrar opciones de selección múltiple.
- Permitir elegir una respuesta por pregunta.
- Mostrar progreso.

### Resultado esperado

Alumno puede responder examen.

## Subfase 20.3: Enviar respuestas

### Tareas

- Confirmar envío.
- Enviar respuestas al backend.
- Bloquear cambios después del envío.
- Mostrar resultado si backend lo permite.

### Resultado esperado

Respuestas enviadas y registradas.

---

# Fase 21: Notas, promedios y estado final

## Objetivo

Mostrar notas, promedios y estado aprobado/reprobado.

## Reglas

- Las notas están entre 0 y 100.
- El promedio final es la suma de los 3 parciales dividido entre 3.
- Aprobado si promedio final es mayor o igual a 60.
- Reprobado si promedio final es menor a 60.

## Subfase 21.1: Vista administrativa de notas

### Tareas

- Mostrar notas por alumno.
- Mostrar parciales.
- Mostrar promedio final.
- Mostrar estado.

### Resultado esperado

Administrador puede controlar notas y estados.

## Subfase 21.2: Vista alumno

### Tareas

- Mostrar notas propias.
- Mostrar promedio.
- Mostrar estado final si corresponde.

### Resultado esperado

Alumno ve sus notas.

---

# Fase 22: Asignación final de carrera

## Objetivo

Mostrar el proceso de admisión final por mayor nota y cupos.

## Reglas

- Siempre se prioriza por mayor nota.
- Primero se intenta asignar a primera opción.
- Si está llena, se intenta segunda opción.
- Si ambas están llenas, se asigna a la carrera con menos personas.

## Subfase 22.1: Vista de asignación

### Tareas

- Mostrar alumnos aprobados.
- Mostrar promedio.
- Mostrar primera opción.
- Mostrar segunda opción.
- Mostrar carrera asignada.
- Mostrar cupos disponibles.

### Resultado esperado

Administrador visualiza admisión final por carrera.

## Subfase 22.2: Ejecutar asignación

### Tareas

- Consumir endpoint del backend.
- Mostrar confirmación antes de ejecutar.
- Mostrar resultado final.

### Resultado esperado

Asignación final visible en frontend.

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

## Subfase 23.1: Vista de reportes

### Tareas

- Crear menú de reportes.
- Crear filtros.
- Mostrar tablas.
- Mostrar gráficos si corresponde.

### Resultado esperado

Reportes visibles para administrador.

## Subfase 23.2: Exportación PDF y Excel

### Tareas

- Crear botón exportar PDF.
- Crear botón exportar Excel.
- Enviar filtros al backend.
- Descargar archivo generado.

### Resultado esperado

Reportes exportables desde frontend.

---

# Fase 24: Comandos de voz para reportes

## Objetivo

Permitir generar reportes usando comandos de voz.

## Tecnología

- Web Speech API.

## Subfase 24.1: Captura de voz

### Tareas

- Crear botón de micrófono.
- Capturar voz.
- Convertir voz a texto.
- Mostrar texto reconocido.

### Resultado esperado

Administrador puede dictar comandos.

## Subfase 24.2: Interpretación de comando

### Ejemplo

```text
listar alumnos reprobados y aprobados
```

### Tareas

- Enviar texto al backend si corresponde.
- Mostrar reporte generado.
- Permitir elegir PDF o Excel.

### Resultado esperado

Reportes generados por comando de voz.

---

# Fase 25: Carga masiva Excel/CSV

## Objetivo

Permitir carga masiva desde archivos Excel o CSV.

## Subfase 25.1: Subida de archivo

### Tareas

- Crear componente de carga.
- Validar extensión Excel o CSV.
- Enviar archivo al backend.
- Mostrar estado de procesamiento.

### Resultado esperado

Archivo cargado al backend.

## Subfase 25.2: Resultado de carga

### Tareas

- Mostrar registros válidos.
- Mostrar registros con errores.
- Mostrar detalle por fila.
- Permitir descargar resultado si backend lo permite.

### Resultado esperado

Administrador puede revisar carga masiva.

---

# Fase 26: Panel docente

## Objetivo

Crear interfaz específica para docentes.

## Funciones del docente

- Ver perfil.
- Ver carga horaria.
- Ver grupos asignados.
- Ver materias asignadas.
- Marcar asistencia de entrada.
- Marcar salida.
- Tomar asistencia a sus alumnos.
- Ver asistencia de sus alumnos.

## Subfase 26.1: Dashboard docente

### Tareas

- Mostrar resumen de clases.
- Mostrar próxima clase.
- Mostrar accesos rápidos.

### Resultado esperado

Docente tiene panel propio.

## Subfase 26.2: Horarios y asistencia

### Tareas

- Mostrar horarios del docente.
- Mostrar clase activa.
- Permitir marcar entrada y salida.

### Resultado esperado

Docente controla su asistencia.

## Subfase 26.3: Asistencia de alumnos

### Tareas

- Mostrar grupos asignados.
- Mostrar alumnos.
- Tomar asistencia.
- Ver historial de asistencia de sus alumnos.

### Resultado esperado

Docente gestiona asistencia de sus alumnos.

---

# Fase 27: Panel alumno

## Objetivo

Crear interfaz específica para alumnos.

## Funciones del alumno

- Iniciar sesión con código.
- Ver perfil.
- Ver horarios.
- Marcar asistencia.
- Ver asistencias.
- Dar examen si está habilitado.
- Ver notas si corresponde.

## Subfase 27.1: Dashboard alumno

### Tareas

- Mostrar perfil resumido.
- Mostrar código de alumno.
- Mostrar horario próximo.
- Mostrar estado de examen.

### Resultado esperado

Alumno tiene panel propio.

## Subfase 27.2: Horarios y asistencia

### Tareas

- Mostrar horarios.
- Mostrar clase activa.
- Marcar asistencia.
- Ver historial.

### Resultado esperado

Alumno gestiona su asistencia.

## Subfase 27.3: Exámenes y notas

### Tareas

- Mostrar exámenes habilitados.
- Permitir rendir examen.
- Mostrar notas propias.

### Resultado esperado

Alumno puede rendir examen habilitado y consultar notas.

---

# Fase 28: Responsive design y experiencia de usuario

## Objetivo

Asegurar que la aplicación funcione correctamente en PC y dispositivos móviles.

## Subfase 28.1: Adaptación responsive

### Tareas

- Adaptar sidebar a móvil.
- Adaptar tablas a pantallas pequeñas.
- Adaptar formularios.
- Adaptar modales.
- Adaptar dashboard.

### Resultado esperado

Interfaz usable en PC, Android e iOS desde navegador.

## Subfase 28.2: Estados visuales

### Estados obligatorios

- Cargando.
- Sin datos.
- Error.
- Éxito.
- Acceso denegado.
- Sesión expirada.

### Resultado esperado

La interfaz comunica correctamente cada estado.

---

# Fase 29: Pruebas del frontend

## Objetivo

Probar que el frontend cumple con los módulos y se conecta correctamente con Laravel.

## Subfase 29.1: Pruebas de rutas

### Tareas

- Probar rutas públicas.
- Probar rutas privadas.
- Probar rutas por rol.

### Resultado esperado

Navegación segura y correcta.

## Subfase 29.2: Pruebas de integración con backend

### Tareas

- Probar login.
- Probar postulantes.
- Probar pagos.
- Probar alumnos.
- Probar docentes.
- Probar horarios.
- Probar asistencia.
- Probar exámenes.
- Probar reportes.

### Resultado esperado

Frontend integrado con backend Laravel.

## Subfase 29.3: Pruebas de validación

### Tareas

- Probar campos vacíos.
- Probar correos inválidos.
- Probar notas fuera de rango.
- Probar porcentajes que no suman 100%.
- Probar archivos inválidos.
- Probar accesos no permitidos.

### Resultado esperado

Validaciones frontend y backend funcionan juntas.

---

# Fase 30: Preparación para despliegue en Vercel

## Objetivo

Preparar el frontend para despliegue futuro en Vercel.

## Subfase 30.1: Variables de producción

### Tareas

- Configurar URL del backend en Railway.
- Configurar claves públicas.
- Verificar CORS con backend Laravel.

### Resultado esperado

Frontend preparado para producción.

## Subfase 30.2: Build de producción

### Tareas

- Ejecutar build.
- Corregir errores.
- Revisar rutas.
- Revisar variables.

### Resultado esperado

Aplicación lista para desplegar.

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

Regla:

- No avanzar un módulo frontend como terminado si no consume el backend correspondiente.
- Puede usarse mock temporal durante diseño visual, pero debe reemplazarse por conexión real al backend Laravel.

---

## 9. Reglas de conexión obligatorias con Laravel

El frontend debe conectarse con Laravel para:

- Login.
- Logout.
- Perfil autenticado.
- Validación Firebase.
- CRUD de usuarios.
- CRUD de postulantes.
- Subida de documentos.
- Validación de requisitos.
- Pago con Stripe.
- Consulta de pagos.
- Conversión a alumno.
- CRUD de docentes.
- CRUD de gestiones.
- CRUD de carreras.
- CRUD de cupos.
- CRUD de materias.
- CRUD de grupos.
- CRUD de aulas.
- CRUD de horarios.
- Asignaciones docente-materia-grupo.
- Asistencia docente.
- Asistencia alumno.
- Exámenes.
- Preguntas.
- Opciones.
- Resolución de examen.
- Notas.
- Promedios.
- Estado final.
- Asignación de carrera.
- Reportes.
- Exportaciones.
- Comandos de voz.
- Carga masiva.
- Dashboard.

---

## 10. Resultado final esperado

Al finalizar todas las fases, el frontend debe ser una aplicación web completa, modular, responsive y conectada al backend Laravel.

Debe permitir:

- Que el administrador gestione todo el sistema.
- Que el docente vea su carga horaria, marque asistencia, finalice clase, tome asistencia y consulte asistencia de sus alumnos.
- Que el alumno ingrese con su código, vea perfil, vea horarios, marque asistencia, rinda examen habilitado y vea sus datos.
- Que los reportes se visualicen y exporten.
- Que los comandos de voz generen reportes.
- Que todos los módulos consuman endpoints reales del backend Laravel.
- Que los componentes se reutilicen para evitar repetir código.
- Que la aplicación esté lista para despliegue futuro en Vercel.


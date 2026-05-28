# Contexto del Proyecto: Aplicación Web de Admisión Universitaria (CUP) para la FICCT

## 1. Nombre del proyecto

**Aplicación Web de Admisión Universitaria (CUP) para la FICCT**.

La FICCT, es decir, la **Facultad de Ingeniería de Ciencias de la Computación y Telecomunicaciones**, desea desarrollar un sistema web para administrar el proceso de ingreso de estudiantes al curso preuniversitario.

Este sistema debe permitir controlar todo el proceso de admisión universitaria para el CUP, desde el registro inicial del postulante hasta la asignación de grupos, docentes, horarios, aulas, materias, asistencia, exámenes, notas, aprobación, reprobación, cupos por carrera, pagos, reportes y administración de usuarios.

---

## 2. Objetivo general del sistema

El objetivo general del sistema es desarrollar una aplicación web que permita administrar el proceso de ingreso de estudiantes al curso preuniversitario de la FICCT.

El sistema debe permitir:

- Registrar postulantes.
- Validar requisitos de postulación.
- Gestionar el pago mediante Stripe.
- Permitir que el administrador revise el cumplimiento de requisitos y el pago.
- Permitir que el administrador dé acceso al postulante para ser estudiante.
- Generar automáticamente un código de acceso para el alumno.
- Organizar a los alumnos inscritos en grupos.
- Asignar horarios.
- Asignar docentes.
- Asignar aulas.
- Asignar materias.
- Controlar asistencia de docentes.
- Controlar asistencia de alumnos.
- Permitir exámenes cuando el administrador los habilite.
- Registrar y calcular notas.
- Calcular el promedio final.
- Determinar si el postulante está aprobado o reprobado.
- Gestionar cupos por carrera.
- Priorizar la admisión por mayor nota.
- Generar reportes.
- Exportar reportes en PDF.
- Exportar reportes en Excel.
- Generar reportes mediante comandos de voz.
- Gestionar usuarios, roles y permisos.

---

## 3. Roles del sistema

El sistema tendrá solamente **3 roles**:

1. **Administrador**.
2. **Docente**.
3. **Alumno**.

No existirán otros roles adicionales como autoridad, coordinador u otros perfiles independientes. Toda referencia anterior a autoridades, coordinadores u otros usuarios queda reemplazada por la decisión final de manejar únicamente estos tres roles.

---

## 4. Rol Administrador

El administrador tendrá acceso completo al sistema.

El administrador será quien:

- Crea otros administradores.
- Gestiona usuarios.
- Valida los requisitos de los postulantes.
- Revisa o recibe la confirmación del pago realizado mediante Stripe.
- Da acceso al postulante para convertirse en alumno.
- Genera o habilita el código automático del alumno.
- Administra postulantes.
- Administra alumnos.
- Administra docentes.
- Administra materias.
- Administra grupos.
- Administra aulas.
- Administra horarios.
- Administra turnos.
- Administra periodos.
- Administra exámenes.
- Crea preguntas de examen.
- Habilita exámenes.
- Define porcentajes de las materias dentro del examen.
- Controla notas.
- Controla cupos por carrera.
- Visualiza asistencia de docentes.
- Visualiza asistencia de alumnos.
- Genera reportes.
- Exporta reportes a PDF.
- Exporta reportes a Excel.
- Usa comandos de voz para generar reportes.
- Tiene acceso completo al sistema.

El administrador es el único rol que puede ver toda la información general de asistencia tanto de docentes como de alumnos.

---

## 5. Rol Docente

El docente tendrá permisos limitados.

El docente podrá:

- Iniciar sesión en el sistema.
- Ver su perfil.
- Ver su carga horaria.
- Ver los grupos que tiene asignados.
- Ver las materias que tiene asignadas.
- Ver los horarios que tiene asignados.
- Marcar su propia asistencia cuando llega a dar clase.
- Marcar su salida o finalización de clase.
- Tomar asistencia a sus alumnos.
- Ver la asistencia de sus alumnos.

El docente no tendrá acceso completo al sistema.

El docente no podrá ver toda la asistencia de todos los docentes y alumnos. Solo podrá ver la asistencia de sus propios alumnos, es decir, de los alumnos que pertenecen a los grupos y materias que tiene asignados por el administrador.

El docente puede dar de **1 a 4 materias como máximo**.

El administrador asigna las materias y grupos en los que el docente dará clases y/o estará relacionado con el examen.

---

## 6. Rol Alumno

El alumno tendrá un código generado automáticamente por el sistema.

Ese código servirá para que el alumno pueda:

- Iniciar sesión.
- Colocar o marcar su asistencia.
- Dar el examen si el administrador habilita el examen.
- Ver sus asistencias.
- Ver su perfil.
- Ver sus horarios.

El alumno solo podrá acceder a sus propios datos.

El alumno no podrá ver información administrativa general ni asistencia de otros alumnos.

---

## 7. Requisitos del postulante

Los postulantes deberán cumplir con los siguientes requisitos:

- Título de bachiller.
- Imagen del título de bachiller.
- Nombres.
- Apellido paterno.
- Apellido materno.
- Cédula de identidad.
- Correo.
- Celular.

Además, en el módulo de registro de postulantes se deben registrar los siguientes datos:

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
- Carrera a la que postula.
- Título de bachiller en imagen.

El alumno debe registrar dos carreras a las que postula de forma obligatoria:

- Primera opción de carrera.
- Segunda opción de carrera.

---

## 8. Flujo general del postulante hasta convertirse en alumno

El flujo entendido es el siguiente:

1. El postulante registra sus datos personales.
2. El postulante presenta sus requisitos.
3. El postulante sube la imagen de su título de bachiller.
4. La imagen del título de bachiller se guardará en Cloudinary.
5. El sistema valida datos obligatorios.
6. El sistema valida que la cédula de identidad no esté duplicada.
7. El sistema valida el correo electrónico.
8. Se podrá crear cuenta con Google y validar con cuenta de Google usando Firebase.
9. Si el postulante cumple con los requisitos, pasa al pago.
10. El postulante debe pagar obligatoriamente mediante Stripe.
11. El pago mediante Stripe es obligatorio antes de que el postulante reciba acceso como alumno.
12. El administrador recibe o revisa la información del pago.
13. Después de confirmar que el postulante cumple requisitos y realizó el pago, el administrador le da acceso para ser estudiante/alumno.
14. El sistema genera automáticamente el código del alumno.
15. El alumno usa ese código para iniciar sesión.
16. El alumno podrá ver su perfil, horarios, asistencias y rendir examen si el administrador habilita el examen.

---

## 9. Pasarela de pago

La pasarela de pago definida será **Stripe**.

El pago mediante Stripe será obligatorio para que el postulante pueda continuar el proceso y convertirse en alumno.

El flujo de pago será:

1. El postulante cumple con los requisitos.
2. El postulante realiza el pago mediante Stripe.
3. El administrador recibe la información o confirmación del pago.
4. El administrador valida el pago.
5. Después de validar el pago, el administrador da acceso al postulante para ser alumno.
6. El sistema genera el código automático del alumno.

No se debe entregar acceso como alumno antes de realizar y validar el pago.

---

## 10. Código automático del alumno

El código del alumno será generado automáticamente.

El código servirá para que el alumno pueda iniciar sesión en el sistema.

El formato del código será:

```text
AÑO + GESTIÓN + CÉDULA DE IDENTIDAD
```

Ejemplo:

```text
Año: 2026
Gestión: 1
Cédula de identidad: 13541539
Código generado: 2026113541539
```

El código se forma de esta manera:

```text
2026 + 1 + 13541539 = 2026113541539
```

Reglas del código:

- El año se genera automáticamente usando el año actual.
- La gestión solo puede ser 1 o 2.
- La gestión será 1 si corresponde al primer semestre del año.
- La gestión será 2 si corresponde al segundo semestre del año.
- La cédula de identidad se concatena al final.
- El código no se escribe manualmente.
- El código se genera automáticamente.
- El código se entrega o habilita cuando el administrador da acceso al alumno.

---

## 11. Gestión académica

Cada año tendrá dos gestiones académicas.

Las gestiones se manejan por semestre:

- Gestión 1: primer semestre del año.
- Gestión 2: segundo semestre del año.

La gestión forma parte del código automático del alumno y también debe relacionarse con:

- Postulantes.
- Alumnos.
- Carreras.
- Cupos.
- Grupos.
- Docentes.
- Materias.
- Horarios.
- Aulas.
- Exámenes.
- Asistencias.
- Reportes.

---

## 12. Carreras y cupos

Cada carrera maneja una cantidad de cupos de alumnos admitidos por gestión.

El postulante debe elegir obligatoriamente dos carreras:

- Primera opción.
- Segunda opción.

Reglas de admisión por cupo:

1. Si el alumno aprueba, primero se intenta asignarlo a su primera opción de carrera.
2. Si la primera opción de carrera ya llenó sus cupos, se intenta asignarlo a su segunda opción de carrera.
3. Si ambas opciones están llenas, se lo añade a la carrera que tenga menos personas.
4. Siempre se prioriza por mayor nota.

La prioridad de admisión será siempre por la nota más alta.

Esto significa que, cuando haya más alumnos aprobados que cupos disponibles, tendrán prioridad los alumnos con mayor nota final.

---

## 13. Materias o áreas evaluadas

Las materias o áreas evaluadas serán:

1. Física.
2. Matemáticas.
3. Computación.
4. Inglés.

Cada examen tendrá preguntas de todas estas materias.

---

## 14. Porcentajes de las materias dentro del examen

Cada materia tendrá un porcentaje dentro del examen.

Los porcentajes definidos como ejemplo son:

- Física: 25%.
- Matemáticas: 30%.
- Computación: 30%.
- Inglés: 15%.

La suma total de los porcentajes debe ser 100%.

```text
25% + 30% + 30% + 15% = 100%
```

El administrador será quien defina o asigne los porcentajes de las materias dentro del examen.

El sistema debe validar que la suma de los porcentajes de todas las materias sea igual a 100%.

---

## 15. Exámenes

El sistema tendrá un módulo para dar examen.

El administrador será quien cargue las preguntas de todas las materias.

Las materias del examen serán:

- Física.
- Matemáticas.
- Computación.
- Inglés.

Reglas del examen:

- El estudiante debe dar 3 exámenes en una gestión.
- Solo se toman 3 exámenes por estudiante.
- Cada examen tendrá 10 preguntas por cada área o materia.
- Las respuestas serán siempre de selección múltiple.
- El administrador cargará las preguntas.
- El administrador habilitará el examen.
- El alumno solo podrá dar el examen si el administrador habilita el examen.
- Las preguntas se dividen según los porcentajes definidos por materia.
- Las notas deben estar entre 0 y 100.

El documento original indica que cada examen tendrá 10 preguntas de cada área. También se aclara que el administrador pondrá las preguntas de todas las materias y se dividirán 10 preguntas entre el porcentaje definido para cada materia.

Por lo tanto, el sistema debe respetar que las preguntas pertenecen a las materias y que las materias tienen porcentajes definidos por el administrador.

---

## 16. Tipo de preguntas y respuestas

Las respuestas de los exámenes serán siempre de **selección múltiple**.

No se manejarán, por ahora, respuestas abiertas, desarrollo, verdadero/falso u otros tipos de pregunta.

Cada pregunta debe tener opciones de respuesta y una respuesta correcta.

---

## 17. Notas y cálculo del promedio

El sistema debe registrar notas de:

- Computación.
- Matemáticas.
- Inglés.
- Física.

Las notas deben estar entre:

- 0 como mínimo.
- 100 como máximo.

El promedio final se calcula siempre como la suma de los 3 parciales dividida entre el total de parciales, es decir, 3.

Fórmula:

```text
Promedio final = (Parcial 1 + Parcial 2 + Parcial 3) / 3
```

Regla de aprobación:

- APROBADO: promedio final mayor o igual a 60.
- REPROBADO: promedio final menor a 60.

```text
Si promedio >= 60 entonces APROBADO
Si promedio < 60 entonces REPROBADO
```

El sistema debe calcular automáticamente:

- Promedio final.
- Estado del postulante o alumno.

---

## 18. Estado del postulante o alumno

Los estados finales según la nota son:

- **APROBADO**: cuando el promedio final es mayor o igual a 60 puntos.
- **REPROBADO**: cuando el promedio final es menor a 60 puntos.

Los postulantes que obtengan una nota final mayor o igual a 60 puntos serán considerados aprobados.

---

## 19. Grupos

La facultad necesita organizar grupos para el curso preuniversitario o curso de nivelación.

Reglas:

- Cada grupo admite máximo 70 estudiantes.
- El sistema debe calcular automáticamente la cantidad total de inscritos.
- El sistema debe calcular automáticamente la cantidad de grupos necesarios.
- El sistema debe mostrar la cantidad de grupos habilitados.
- El sistema debe mostrar los estudiantes por grupo.

Fórmula lógica:

```text
Cantidad de grupos = techo(total de inscritos / 70)
```

Ejemplos:

```text
70 inscritos  -> 1 grupo
71 inscritos  -> 2 grupos
140 inscritos -> 2 grupos
141 inscritos -> 3 grupos
```

---

## 20. Docentes

La facultad realiza la contratación de docentes.

Un docente será contratado si cumple con los siguientes requisitos:

- Ser profesional en el área.
- Tener maestría.
- Tener diplomado en educación superior.

Los docentes deben entregar los siguientes datos:

- Nombre.
- Apellido paterno.
- Apellido materno.
- Cédula de identidad.
- Celular.
- Correo.

Reglas de asignación:

- Un docente puede ser asignado desde 1 hasta 4 grupos.
- Un docente puede dar de 1 a 4 materias como máximo.
- El administrador asigna las materias.
- El administrador asigna los grupos.
- El docente solo puede ver lo relacionado a su carga horaria y sus alumnos.

---

## 21. Horarios

Los administradores deben poder definir los horarios.

El administrador debe poder definir:

- Días.
- Turnos.
- Periodos.

Cada periodo tendrá una duración de **45 minutos**.

Los horarios serán importantes para:

- Asignar materias.
- Asignar docentes.
- Asignar grupos.
- Asignar aulas.
- Controlar asistencia de docentes.
- Controlar asistencia de alumnos.
- Determinar si una asistencia está a tiempo, retrasada o como falta.

---

## 22. Aulas

El aula solo manejará ubicación.

Ejemplo de ubicación:

```text
Módulo 236, Aula 11
```

No se manejarán por ahora otros datos del aula como capacidad, tipo de aula, equipamiento u otros atributos.

---

## 23. Asistencia

La asistencia es obligatoria.

Debe manejarse asistencia para:

- Docentes.
- Alumnos.

El docente debe marcar su propia asistencia.

El docente también podrá tomar asistencia a sus alumnos.

Los administradores podrán ver la asistencia de:

- Todos los docentes.
- Todos los alumnos.

Los docentes podrán ver solamente la asistencia de:

- Sus propios alumnos.

Los alumnos podrán ver:

- Sus propias asistencias.

---

## 24. Reglas de asistencia

La asistencia depende del horario definido por el administrador.

El administrador define días, turnos y periodos.

Reglas para marcar asistencia:

1. Docentes y alumnos solo podrán marcar asistencia de acuerdo al horario establecido por el administrador.
2. Solo podrán marcar asistencia máximo hasta 30 minutos después de empezar la clase.
3. Si marcan dentro de los 30 minutos después de empezar la clase, se considera asistencia válida según la regla definida.
4. Luego de esos 30 minutos, se marcará como retraso.
5. Pasado el horario de esa clase, se marcarará automáticamente como falta.
6. Esta regla aplica tanto para docentes como para alumnos.

El sistema debe permitir visualizar la asistencia de forma clara.

El administrador necesita ver visualmente:

- Qué días los docentes vinieron a dar clases.
- Qué días los docentes no vinieron.
- Qué alumnos asistieron.
- Qué alumnos tuvieron retraso.
- Qué alumnos faltaron.

---

## 25. Módulo de asistencia

La asistencia debe existir en el sistema porque es obligatoria.

Aunque inicialmente existía duda sobre si manejarla como módulo o no, por las reglas definidas se debe considerar como una parte importante del sistema.

Puede manejarse como un módulo o submódulo, pero funcionalmente debe permitir:

- Marcar asistencia docente.
- Marcar salida o finalización de clase del docente.
- Tomar asistencia de alumnos.
- Registrar asistencia a tiempo.
- Registrar retrasos.
- Registrar faltas automáticas.
- Consultar asistencia docente.
- Consultar asistencia de alumnos.
- Visualizar asistencia para administradores.
- Visualizar asistencia de alumnos para docentes.
- Visualizar asistencia propia para alumnos.

---

## 26. Módulo de autenticación

El sistema debe permitir:

- Inicio de sesión seguro.
- Cerrar sesión.
- Control de sesiones.

Validaciones:

- Usuario obligatorio.
- Contraseña obligatoria.
- Control de sesiones.

Los usuarios que podrán autenticarse son:

- Administradores.
- Docentes.
- Alumnos.

Los alumnos podrán iniciar sesión usando su código generado automáticamente.

---

## 27. Firebase Authentication y Google

Se implementará la recomendación de usar **Firebase Authentication** para simplificar el proceso de login con Google y validación de correo.

Uso recomendado:

- Firebase Authentication se usará principalmente en el frontend.
- Permitirá crear cuenta con Google.
- Permitirá validar correo con cuenta de Google.
- El frontend podrá obtener un token de Firebase.
- El token puede enviarse al backend PHP para verificar la identidad del usuario.

Esto simplifica:

- Login con Google.
- Validación de correo.
- Gestión inicial de autenticación externa.
- Evitar programar desde cero el flujo de autenticación con Google.

El backend principal seguirá siendo PHP 8.2.12.

---

## 28. Módulo de registro de postulantes

El sistema deberá permitir registrar postulantes con los siguientes datos:

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
- Carrera a la que postula.
- Título de bachiller como imagen.

Funcionalidades:

- Registrar postulante.
- Modificar datos.
- Eliminar registro.
- Buscar postulante.
- Listar postulantes.

Validaciones:

- No permitir cédula de identidad duplicada.
- Validar correo electrónico.
- Validar campos vacíos.
- Validar con Firebase.
- Permitir crear cuenta con Google.
- Validar con cuenta de Google.

La imagen del título de bachiller se guardará en Cloudinary.

---

## 29. Cloudinary

Cloudinary se usará para guardar imágenes.

Por ahora, el documento define específicamente que se guardará en Cloudinary:

- Imagen del título de bachiller.

Cloudinary debe integrarse con el sistema para almacenar y recuperar las imágenes necesarias.

---

## 30. Módulo de exámenes

El módulo de exámenes debe permitir:

- Crear exámenes.
- Cargar preguntas de todas las materias.
- Registrar preguntas de selección múltiple.
- Definir respuestas correctas.
- Habilitar examen.
- Permitir que el alumno dé examen solo si está habilitado.
- Registrar respuestas del alumno.
- Calcular nota del examen.
- Aplicar porcentajes por materia.
- Registrar hasta 3 parciales por alumno en una gestión.
- Calcular promedio final.
- Calcular estado final.

Funcionalidades solicitadas originalmente:

- Registrar notas.
- Editar notas.
- Mostrar promedio.
- Mostrar estado final.

---

## 31. Módulo de asignación de grupos

La facultad necesita organizar grupos para el curso de nivelación.

Reglas:

- Cada grupo tendrá máximo 70 estudiantes.
- El sistema debe calcular automáticamente la cantidad total de inscritos.
- El sistema debe calcular automáticamente la cantidad de grupos habilitados.

Funcionalidades:

- Mostrar total de inscritos.
- Mostrar cantidad de grupos.
- Mostrar estudiantes por grupo.

---

## 32. Módulo de reportes

El sistema deberá generar reportes de:

- Lista general de postulantes.
- Postulantes aprobados.
- Postulantes reprobados.
- Promedios generales.
- Cantidad de grupos habilitados.
- Estadísticas por materia.
- Docentes por grupos.
- Grupos con mayor cantidad de aprobados.
- Asistencia de docentes.
- Asistencia de alumnos.

Los reportes deberán poder exportarse en:

- PDF.
- Excel.

También se podrá generar reportes y consultas de reportes mediante comandos de voz.

Ejemplo de comando de voz:

```text
listar alumnos reprobados y aprobados
```

Cuando el administrador diga un comando de voz, el sistema debe generar el reporte correspondiente y permitir elegir si se exporta en PDF o Excel.

---

## 33. Comandos de voz

Se implementará la recomendación de usar la tecnología más fácil para comandos de voz.

La opción recomendada es usar la **Web Speech API del navegador**.

Razones:

- Es más fácil de implementar.
- No requiere crear un sistema de reconocimiento de voz desde cero.
- Puede funcionar directamente desde el frontend.
- Permite convertir voz a texto.
- Permite que el administrador diga comandos como “listar alumnos reprobados y aprobados”.
- El sistema puede interpretar ese texto y generar el reporte correspondiente.

La finalidad de esta decisión es simplificar el proceso de implementación.

---

## 34. Panel administrativo

El sistema debe incluir un panel administrativo.

Debe tener:

- Menú de navegación.
- Dashboard principal.
- Indicadores estadísticos.

Indicadores estadísticos:

- Total inscritos.
- Total aprobados.
- Total reprobados.
- Total grupos habilitados.

Además, por las nuevas reglas, el panel administrativo también debe permitir ver de forma visual:

- Asistencia de docentes.
- Asistencia de alumnos.
- Docentes que asistieron.
- Docentes que no asistieron.
- Alumnos presentes.
- Alumnos con retraso.
- Alumnos con falta.

---

## 35. Generación de cuentas de usuario

El sistema debe generar automáticamente cuentas para los usuarios a partir de datos que se entreguen al sistema en cada gestión académica.

La administración de la facultad podrá entregar datos en lotes mediante:

- Excel.
- CSV.

La carga debe poder realizarse desde una aplicación web.

Sin embargo, los roles finales del sistema serán solamente:

- Administrador.
- Docente.
- Alumno.

El administrador puede crear administradores.

El administrador da acceso a los alumnos después de validar requisitos y pago.

---

## 36. Roles y permisos finales

Los permisos se manejarán en base a los tres roles finales.

### Administrador

Acceso completo al sistema.

Puede gestionar:

- Administradores.
- Docentes.
- Alumnos.
- Postulantes.
- Pagos.
- Carreras.
- Cupos.
- Materias.
- Grupos.
- Aulas.
- Horarios.
- Turnos.
- Periodos.
- Exámenes.
- Preguntas.
- Notas.
- Asistencias.
- Reportes.
- Exportaciones.

### Docente

Acceso limitado.

Puede:

- Ver su perfil.
- Ver su carga horaria.
- Ver sus grupos asignados.
- Ver sus materias asignadas.
- Marcar su asistencia de entrada.
- Marcar finalización de clase.
- Tomar asistencia a sus alumnos.
- Ver asistencia de sus alumnos.

### Alumno

Acceso limitado.

Puede:

- Iniciar sesión con su código.
- Ver su perfil.
- Ver sus horarios.
- Marcar su asistencia.
- Ver sus asistencias.
- Dar examen si el examen está habilitado.

---

## 37. Stack tecnológico definido

### Frontend

El frontend será desarrollado con:

- React JS.
- Vite.
- Node 22.22.2.
- Tailwind CSS versión 4.
- Librerías de React para creación de componentes.

Se podrán usar librerías recomendadas que simplifiquen la creación de componentes.

### Backend

El backend será desarrollado usando:

- PHP 8.2.12 CLI.
- Apache para localhost.
- XAMPP.

El backend principal debe ser PHP.

### Base de datos

La base de datos será:

- PostgreSQL 16.

La herramienta para crear y administrar la base de datos será:

- pgAdmin 4.

### Servicios externos

Se usarán:

- Stripe para pagos.
- Cloudinary para guardar imágenes.
- Firebase Authentication para login con Google y validación de correo.
- Web Speech API para comandos de voz.

### Despliegue futuro

Próximamente se desplegará:

- Base de datos en Clever Cloud.
- Frontend en Vercel.
- Backend en Railway.

---

## 38. Recomendaciones aceptadas para simplificar implementación

Se aceptan e implementan las siguientes recomendaciones:

### Firebase Authentication

Usar Firebase Authentication para:

- Login con Google.
- Validación de correo.
- Simplificar autenticación externa.

Uso recomendado:

- Firebase en frontend.
- Backend PHP verifica token recibido desde frontend.

### Web Speech API

Usar Web Speech API para comandos de voz.

Uso recomendado:

- El administrador habla.
- El navegador convierte la voz a texto.
- El frontend interpreta el texto.
- El sistema genera el reporte correspondiente.

### Cloudinary

Usar Cloudinary para guardar imágenes.

Uso definido:

- Imagen del título de bachiller.

### Stripe

Usar Stripe como pasarela de pagos.

Uso definido:

- Pago obligatorio antes de que el administrador otorgue acceso como alumno.

---

## 39. Módulos principales identificados

El sistema debe organizarse en módulos o partes funcionales como mínimo:

1. Autenticación.
2. Usuarios.
3. Roles y permisos.
4. Registro de postulantes.
5. Validación de requisitos.
6. Pagos con Stripe.
7. Gestión académica.
8. Carreras.
9. Cupos por carrera.
10. Alumnos.
11. Generación de código automático.
12. Docentes.
13. Materias.
14. Grupos.
15. Aulas.
16. Horarios.
17. Turnos.
18. Periodos.
19. Asistencia docente.
20. Asistencia de alumnos.
21. Exámenes.
22. Preguntas de selección múltiple.
23. Respuestas.
24. Notas.
25. Promedios.
26. Estado final aprobado/reprobado.
27. Reportes.
28. Exportación PDF.
29. Exportación Excel.
30. Comandos de voz.
31. Dashboard administrativo.
32. Carga masiva Excel/CSV.

---

## 40. Reglas de negocio principales

Las reglas de negocio principales son:

1. El postulante debe registrar sus datos.
2. El postulante debe cumplir requisitos.
3. El postulante debe subir imagen del título de bachiller.
4. La imagen del título de bachiller se guarda en Cloudinary.
5. El sistema no debe permitir cédula de identidad duplicada.
6. El sistema debe validar correo electrónico.
7. Se usará Firebase para validación con Google.
8. Si el postulante cumple requisitos, debe pagar mediante Stripe.
9. El pago mediante Stripe es obligatorio antes de recibir acceso como alumno.
10. El administrador valida requisitos y pago.
11. El administrador da acceso al postulante para ser alumno.
12. El sistema genera automáticamente el código del alumno.
13. El código se forma con año actual, gestión y cédula de identidad.
14. La gestión solo puede ser 1 o 2.
15. Gestión 1 corresponde al primer semestre.
16. Gestión 2 corresponde al segundo semestre.
17. El alumno inicia sesión con su código.
18. Solo existirán 3 roles: administrador, docente y alumno.
19. El administrador puede crear administradores.
20. El administrador tiene acceso completo.
21. El docente tiene acceso limitado.
22. El alumno tiene acceso limitado.
23. Cada grupo admite máximo 70 estudiantes.
24. El sistema calcula automáticamente la cantidad de grupos necesarios.
25. El sistema calcula automáticamente la cantidad total de inscritos.
26. El administrador define horarios, días, turnos y periodos.
27. Cada periodo dura 45 minutos.
28. Las aulas solo tendrán ubicación.
29. El docente debe marcar su asistencia.
30. El docente debe marcar cuando llega a dar clase.
31. El docente debe marcar cuando finaliza su clase.
32. El docente podrá tomar asistencia a sus alumnos.
33. El alumno podrá marcar su asistencia.
34. La asistencia se basa en el horario definido por el administrador.
35. Se puede marcar asistencia máximo 30 minutos después de empezar la clase.
36. Luego de los 30 minutos se marca como retraso.
37. Pasado el horario de la clase se marca automáticamente como falta.
38. La regla de asistencia aplica a docentes y alumnos.
39. El administrador puede ver asistencia de docentes y alumnos.
40. El docente solo puede ver asistencia de sus alumnos.
41. El alumno solo puede ver sus propias asistencias.
42. El estudiante debe rendir 3 exámenes por gestión.
43. Solo se toman 3 exámenes por estudiante.
44. Cada examen tiene preguntas de Física, Matemáticas, Computación e Inglés.
45. Las preguntas son de selección múltiple.
46. El administrador carga las preguntas.
47. El administrador habilita el examen.
48. El alumno solo puede dar examen si está habilitado.
49. Las notas deben estar entre 0 y 100.
50. Las materias tienen porcentajes dentro del examen.
51. Los porcentajes ejemplo son Física 25%, Matemáticas 30%, Computación 30% e Inglés 15%.
52. La suma de porcentajes debe ser 100%.
53. El promedio final es la suma de los 3 parciales dividido entre 3.
54. Si el promedio final es mayor o igual a 60, el alumno queda aprobado.
55. Si el promedio final es menor a 60, el alumno queda reprobado.
56. Cada carrera tiene cupos por gestión.
57. El alumno debe elegir dos carreras obligatoriamente.
58. Primero se intenta asignar al alumno a su primera opción.
59. Si la primera opción está llena, se intenta asignar a su segunda opción.
60. Si ambas están llenas, se añade a la carrera que tenga menos personas.
61. Siempre se prioriza por mayor nota.
62. El docente debe cumplir requisitos para ser contratado.
63. El docente debe ser profesional en el área.
64. El docente debe tener maestría.
65. El docente debe tener diplomado en educación superior.
66. El docente puede ser asignado de 1 a 4 grupos.
67. El docente puede dar de 1 a 4 materias como máximo.
68. Los reportes deben poder exportarse en PDF.
69. Los reportes deben poder exportarse en Excel.
70. Los reportes pueden generarse mediante comandos de voz.

---

## 41. Reportes obligatorios

Los reportes obligatorios son:

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

Los reportes deben permitir:

- Visualización en pantalla.
- Exportación a PDF.
- Exportación a Excel.
- Generación por comandos de voz.

---

## 42. Datos importantes para la base de datos

De todo el contexto se desprende que la base de datos debe poder representar, como mínimo, información sobre:

- Personas.
- Usuarios.
- Roles.
- Administradores.
- Docentes.
- Alumnos.
- Postulantes.
- Requisitos.
- Imágenes de título de bachiller.
- Pagos con Stripe.
- Gestión académica.
- Carreras.
- Cupos por carrera.
- Postulaciones.
- Primera opción de carrera.
- Segunda opción de carrera.
- Grupos.
- Aulas.
- Ubicación de aula.
- Materias.
- Horarios.
- Días.
- Turnos.
- Periodos de 45 minutos.
- Asignación de docentes a materias.
- Asignación de docentes a grupos.
- Asignación de alumnos a grupos.
- Asistencia docente.
- Asistencia alumno.
- Exámenes.
- Parciales.
- Preguntas.
- Opciones de respuesta.
- Respuestas correctas.
- Respuestas del alumno.
- Porcentajes por materia.
- Notas.
- Promedios.
- Estado aprobado/reprobado.
- Reportes.
- Carga masiva Excel/CSV.

---

## 43. Restricciones importantes para diseño e implementación

No se debe inventar información que no haya sido definida.

No se debe eliminar ningún requisito ya establecido.

No se deben agregar roles adicionales.

Solo existen estos roles:

- Administrador.
- Docente.
- Alumno.

No se deben olvidar las siguientes decisiones finales:

- Stripe será la pasarela de pago.
- Cloudinary guardará imágenes.
- Firebase Authentication se usará para simplificar login con Google y validación de correo.
- Web Speech API será la opción recomendada para comandos de voz.
- El backend será PHP 8.2.12 con Apache/XAMPP.
- El frontend será React con Vite, Node 22.22.2 y Tailwind CSS 4.
- La base de datos será PostgreSQL 16 con pgAdmin 4.
- El despliegue futuro será frontend en Vercel, backend en Railway y base de datos en Clever Cloud.
- El código del alumno se genera automáticamente con año, gestión y cédula de identidad.
- La asistencia es obligatoria para docentes y alumnos.
- El sistema debe controlar retrasos y faltas automáticamente según el horario.
- Los exámenes son de selección múltiple.
- Los alumnos solo rinden examen si el administrador habilita el examen.
- El promedio final se calcula con 3 parciales.
- Se prioriza la admisión por mayor nota.

---

## 44. Puntos que ya quedaron definidos

Los puntos que antes estaban ambiguos quedaron definidos así:

1. **Pasarela de pago:** se usará Stripe.
2. **Código automático:** año actual + gestión + cédula de identidad. Ejemplo: 2026113541539.
3. **Gestión:** solo puede ser 1 o 2.
4. **Gestión 1:** primer semestre.
5. **Gestión 2:** segundo semestre.
6. **Porcentajes de materias:** ejemplo definido: Física 25%, Matemáticas 30%, Computación 30%, Inglés 15%.
7. **Promedio final:** suma de los 3 parciales dividido entre 3.
8. **Prioridad de admisión:** siempre por mayor nota.
9. **Si las dos carreras están llenas:** se añade a la carrera que menos personas tenga.
10. **Módulo de examen:** sí existirá.
11. **Preguntas:** serán de selección múltiple.
12. **Imágenes:** se guardarán en Cloudinary.
13. **Roles:** solo administrador, docente y alumno.
14. **Administrador:** acceso completo.
15. **Docente:** marca su asistencia, finaliza clase, toma asistencia de sus alumnos y ve asistencia de sus alumnos.
16. **Alumno:** marca su asistencia, da examen si está habilitado y ve perfil, horarios y asistencias.
17. **Asistencia:** obligatoria para docentes y alumnos.
18. **Control de asistencia:** máximo 30 minutos después de iniciar la clase; después es retraso; pasado el horario es falta automática.
19. **Horarios:** definidos por administradores.
20. **Periodos:** duran 45 minutos.
21. **Aulas:** solo ubicación.
22. **Docente:** puede dar de 1 a 4 materias como máximo.
23. **Comandos de voz:** usar tecnología fácil, recomendada Web Speech API.
24. **Firebase Authentication:** se implementará para simplificar login con Google y validación de correo.

---

## 45. Resumen final del contexto

El sistema será una aplicación web completa para administrar el proceso de admisión universitaria del CUP de la FICCT.

El sistema manejará solo tres roles: administrador, docente y alumno.

El postulante primero registra sus datos y requisitos. Si cumple los requisitos, debe pagar mediante Stripe. Después de validar el pago, el administrador le da acceso como alumno y el sistema genera automáticamente un código formado por año, gestión y cédula de identidad.

El alumno usará ese código para iniciar sesión, ver su perfil, ver sus horarios, marcar asistencia, ver sus asistencias y rendir examen cuando el administrador lo habilite.

El administrador tendrá control total del sistema. Podrá gestionar postulantes, alumnos, docentes, administradores, materias, grupos, aulas, horarios, turnos, periodos, exámenes, preguntas, notas, asistencia, reportes y exportaciones.

El docente tendrá acceso limitado. Podrá marcar su asistencia al llegar y al finalizar clase, tomar asistencia a sus alumnos y ver la asistencia de sus alumnos.

La asistencia será obligatoria para docentes y alumnos. Dependerá del horario definido por el administrador. Se podrá marcar máximo 30 minutos después del inicio de clase; luego será retraso y pasado el horario será falta automática.

Los exámenes serán de selección múltiple. El administrador cargará las preguntas de Física, Matemáticas, Computación e Inglés. Las materias tendrán porcentajes, por ejemplo: Física 25%, Matemáticas 30%, Computación 30% e Inglés 15%. El estudiante dará 3 exámenes en la gestión. El promedio final será la suma de los 3 parciales dividida entre 3. Si el promedio es mayor o igual a 60, será aprobado; si es menor a 60, será reprobado.

Las carreras tendrán cupos por gestión. El alumno debe elegir dos carreras. Se priorizará siempre por mayor nota. Primero se intentará asignar a la primera opción, luego a la segunda opción, y si ambas están llenas se añadirá a la carrera con menos personas.

El sistema usará React con Vite, Node 22.22.2 y Tailwind CSS 4 en el frontend. El backend será PHP 8.2.12 con Apache/XAMPP. La base de datos será PostgreSQL 16 administrada con pgAdmin 4. Se usará Stripe para pagos, Cloudinary para imágenes, Firebase Authentication para login con Google y validación de correo, y Web Speech API para comandos de voz. El despliegue futuro será con frontend en Vercel, backend en Railway y base de datos en Clever Cloud.

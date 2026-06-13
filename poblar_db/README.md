# Poblar base de datos CUP

Estos scripts cargan datos demo para una base vacia del backend CUP/FICCT.

## Ejecucion

Desde `backend_cup`:

```bash
php artisan db:seed --class=PoblarDbSeeder
```

El seeder maestro ejecuta los archivos en este orden:

1. `01_seguridad.php`
2. `02_academico.php`
3. `03_docentes.php`
4. `04_infraestructura.php`
5. `05_horarios.php`
6. `06_postulantes.php`
7. `07_alumnos.php`
8. `08_examenes.php`
9. `09_resultados.php`
10. `10_asistencias_reportes_cargas.php`

`00_helpers.php` contiene funciones compartidas para ids, usuarios, passwords, timestamps y JSON.

## Credenciales demo

| Rol | Usuario | Password |
|---|---|---|
| Administrador | `admin` | `admin12345` o valor de `ADMIN_INITIAL_PASSWORD` |
| Administrador | `admin.academico` | `admin12345` |
| Administrador | `admin.reportes` | `admin12345` |
| Docente | `docente_8100001` a `docente_8100005` | `docente12345` |
| Alumno | codigo de alumno, por ejemplo `202612000137` | CI del alumno, por ejemplo `2000137` |

Los alumnos tambien tienen `codigo_acceso` con formato `anio + gestion + cedula`.

## Imagen generica

La carpeta `img/` queda preparada para que coloques una imagen generica.

La base actual no tiene una columna de foto para postulantes o docentes. Para documentos de postulantes se carga una URL demo:

```text
/poblar_db/img/titulo_bachiller_generico.jpg
```

Puedes reemplazar ese archivo localmente si quieres usarlo como referencia visual desde el frontend.

## Notas

- Los datos son ficticios.
- Los scripts usan `updateOrInsert` para poder re-ejecutarse sin duplicar los registros principales.
- Los turnos duran 6 horas y generan 4 periodos de 90 minutos, como exige el servicio actual de horarios.
- No se modifican migraciones ni el `DatabaseSeeder` existente.

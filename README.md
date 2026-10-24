# MediTurno · Sistema de citas médicas de la Clínica Vida Salud

Sistema web para reservar y gestionar fichas (citas) médicas, hecho en **PHP (Laravel 12)** con **PostgreSQL (Supabase)**. El diseño (azul clínico y verde salud) y la lógica son propios del proyecto.

```
Documentacion/   diagrama UML de clases (21 clases, fuente del modelo de datos)
backend/         Laravel: modelos, controladores, servicios (reglas de negocio), rutas y SQL
frontend/        vistas Blade (views/) y CSS/JS (assets/), organizadas por paquete y caso de uso
```

## Organización por paquetes y casos de uso

| Paquete | Casos de uso |
|---|---|
| P1 Seguridad y acceso | CU1 Iniciar sesión · CU3 Gestionar usuarios (con roles y configuración) |
| P2 Gestión médica | CU4 Especialidades · CU5 Médicos · CU6 Horarios |
| P3 Gestión de pacientes | CU7 Gestionar pacientes (y "Mis datos") · CU8 Registrarse |
| P4 Gestión de citas | CU9 Consultar disponibilidad · CU10 Reservar ficha · CU11 Reprogramar · CU12 Cancelar · CU13 Registrar asistencia · CU14 Agenda médica y bloqueos |
| P5 Atención médica | CU15 Consulta · CU16 Historia clínica · CU17 Tratamiento (y catálogo de medicamentos) · CU18 Estudios (y catálogo) · CU19 Historial, resultados y notificaciones |
| P6 Reportes | CU20 Reportes de citas (exportables a Excel) y bitácora |

Las vistas están en `frontend/views/<Paquete>/<CU>/` y los controladores en `backend/app/Http/Controllers/<Paquete>/`.

## Roles

| Rol | Qué hace |
|---|---|
| Administrador | Todo lo de recepción, además de usuarios, roles, especialidades, médicos, horarios, medicamentos, catálogo de estudios, configuración, reportes y bitácora |
| Recepcionista | Registra pacientes; programa, reprograma y cancela citas; registra inasistencias; consulta agendas |
| Médico | Agenda del día, asistencia, consulta (signos vitales y diagnóstico), receta, estudios y resultados, historia clínica, bloqueo de agenda |
| Paciente | Se registra; reserva, confirma, reprograma y cancela sus fichas; ve su historial, resultados y notificaciones |

## Requisitos

- PHP 8.2 o superior con las extensiones `pdo_pgsql`, `pgsql`, `mbstring`, `openssl` y `zip`. En Laragon ya vienen; solo hay que activarlas en `php.ini`.
- Composer
- La base de datos PostgreSQL de Supabase (proyecto **mediturno**, ya creada con las tablas y los datos)

## Instalación

```bash
cd backend
composer install
```

1. Edite `backend/.env` y reemplace `[CONTRASEÑA]` en `DB_PASSWORD` por la contraseña de la base de datos de Supabase.
   - Si no la conoce: Supabase → proyecto **mediturno** → *Project Settings → Database → Reset database password*.
   - Los datos de conexión (`DB_HOST`, `DB_USERNAME`) están en Supabase → **Connect** → **Session pooler**, puerto 5432. Verifique que el host coincida con el de su proyecto.
2. Inicie el sistema:

```bash
php artisan serve
```

3. Abra http://localhost:8000

### Base de datos nueva (opcional)

La base de Supabase ya está lista. Si quiere montar MediTurno en otra base PostgreSQL vacía, configure el `.env` y ejecute:

```bash
php artisan mediturno:instalar     # crea las 21 tablas y carga los datos iniciales
```

## Usuarios de prueba

| Usuario | Contraseña | Rol |
|---|---|---|
| admin | Admin123* | Administrador |
| recepcion | Recepcion123* | Recepcionista |
| dr.perez | Medico123* | Médico (Medicina General y Cardiología; lun–vie 08–12 y 14–18) |
| dra.rojas | Medico123* | Médico (Pediatría; lun, mié, vie 09–13) |
| paciente (o CI 1234567) | Paciente123* | Paciente |

Los pacientes ingresan con su **CI** o su correo. Si los registra la recepción, su contraseña inicial es el CI.

## Reglas de negocio

Están en `backend/app/Services/CitaService.php` y `AtencionService.php`:

- **Sin cruces:** no se permiten dos citas del mismo médico en el mismo horario, y el paciente tampoco puede tener dos citas a la vez. La reserva bloquea la fila del médico para que dos reservas simultáneas no se crucen.
- **Horario y bloqueos:** solo se reserva dentro del **HORARIO** activo del médico y fuera de su **BLOQUEOAGENDA**. No se puede bloquear un horario que tiene citas pendientes.
- **Historial de la cita:** cada cambio se guarda en **HISTORIALCITA** (reserva, confirmación, reprogramación con fecha anterior y nueva, cancelación, atención, inasistencia).
- **Bitácora:** las acciones importantes quedan en **BITACORA**.
- **Notificaciones:** se crea una **NOTIFICACION** al reservar, reprogramar o cancelar, y cuando hay un resultado de estudio.
- **Parámetros (pantalla Configuración):** duración de la ficha (15 min), horas mínimas para que el paciente cancele o reprograme (2) y días máximos de anticipación (60).

## Pruebas automáticas

```bash
cd backend
php artisan test
```

Hay 33 pruebas de todos los casos de uso. Usan una base PostgreSQL **local** llamada `mediturno_pruebas` (ver `phpunit.xml`); cada prueba se revierte al terminar.

Para prepararla:

```bash
createdb -U postgres mediturno_pruebas
psql -U postgres -d mediturno_pruebas -f database/sql/schema.sql -f database/sql/seed.sql
```

Nunca apunte las pruebas a la base de Supabase.

## Publicación

El proyecto está listo para **Render** (plan gratuito) con Docker:

| Archivo | Para qué sirve |
|---|---|
| `Dockerfile` | Arma la imagen con PHP 8.3, Apache, `backend/` y `frontend/` |
| `docker/` | Configuración de Apache y PHP, y el script de arranque |
| `render.yaml` | Crea el servicio web y deja cargadas las variables de entorno |

### 1. Subir el proyecto a GitHub

Cree un repositorio **privado** en GitHub y, en la carpeta del proyecto, ejecute:

```bash
git init
git add .
git commit -m "MediTurno"
git branch -M main
git remote add origin https://github.com/SU_USUARIO/mediturno.git
git push -u origin main
```

El `.gitignore` evita que se suban `backend/.env` (con la contraseña) y `backend/vendor`.

### 2. Generar la clave de la aplicación

```bash
cd backend
php artisan key:generate --show
```

Copie el resultado (empieza con `base64:`).

### 3. Crear el servicio en Render

1. Entre a https://render.com con su cuenta de GitHub.
2. Vaya a **New → Blueprint** y elija el repositorio. Render lee `render.yaml`.
3. Complete las variables que pide:
   - `APP_KEY`: la clave del paso 2.
   - `DB_PASSWORD`: la contraseña de la base de datos de Supabase.
   - `APP_URL`: la dirección que le asigne Render, por ejemplo `https://mediturno.onrender.com`. Puede ponerla después de crear el servicio.
4. Pulse **Apply**. La primera publicación tarda unos minutos.

### Notas

- En el plan gratuito, el servicio se duerme tras 15 minutos sin visitas; la siguiente visita tarda alrededor de un minuto en abrir.
- La base de datos sigue en Supabase. Si el proyecto queda pausado por inactividad, se reactiva desde el panel de Supabase.
- Cada `git push` a `main` vuelve a publicar automáticamente.

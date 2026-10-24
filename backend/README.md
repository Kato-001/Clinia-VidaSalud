# Backend · Laravel 12 + PostgreSQL

```
app/
  Models/                 21 modelos Eloquent, uno por clase del diagrama UML
  Services/               reglas de negocio
    CitaService.php         CU9–CU14: disponibilidad, reservar, confirmar, reprogramar, cancelar, asistencia
    AtencionService.php     CU15–CU18: consulta, receta, estudios, historia clínica, bloqueos
    PacienteService.php     CU7–CU8: alta de paciente con su usuario e historia clínica
  Http/Controllers/P1…P6/ un controlador por caso de uso, agrupados por paquete
  Http/Middleware/        VerificarRol (control de acceso: rol:ADMINISTRADOR,RECEPCIONISTA…)
  Exceptions/ReglaNegocio regla incumplida → vuelve al formulario con el mensaje
database/sql/             schema.sql (21 tablas) y seed.sql (datos iniciales)
lang/es/validation.php    mensajes de validación en español
routes/web.php            rutas (con el paquete y CU de cada grupo)
routes/console.php        comando php artisan mediturno:instalar
tests/Feature/            33 pruebas por paquete
```

## Notas técnicas

- **Vistas y assets:** se leen desde `../frontend`. `config/view.php` apunta a `../frontend/views`, y `AssetController` sirve `../frontend/assets` en `/assets/...`.
- **Sin tablas fuera del diagrama:**
  - Sesiones y caché van en archivos (`SESSION_DRIVER=file`).
  - No se usan migraciones de Laravel. El esquema está en `database/sql/schema.sql`.
- **Datos que no son tablas nuevas:** el rol RECEPCIONISTA, los estados de cita y los parámetros son filas de ROL, ESTADOCITA y CONFIGURACION.
- **Supabase:**
  - Usar el *Session pooler* (puerto 5432). Con el *Transaction pooler* (6543), PDO no puede usar sentencias preparadas.
  - RLS está activado sin políticas. Así la API pública de Supabase no expone datos y solo este backend (usuario `postgres`) accede.

## Mapeo de los métodos del diagrama

| Clase | Métodos → implementación |
|---|---|
| USUARIO | `iniciarSesion`, `cerrarSesion`, `cambiarPassword` → `AutenticacionController` |
| ROL | `asignarPermisos`, `quitarPermisos` → `RolController` + middleware `rol:` |
| BITACORA | `registrar()` → `Bitacora::registrar()` |
| CONFIGURACION | `obtener(clave)`, `actualizar()` → `Configuracion::obtener()`, `ConfiguracionController@update` |
| PACIENTE | `registrarse`, `actualizarDatos` → `PacienteService` |
| PACIENTE | `reservarCita`, `confirmarCita`, `reprogramarCita`, `cancelarCita` → `CitaService` |
| PACIENTE | `verHistorial`, `verResultados` → `ResultadoController` |
| PACIENTE | `buscarMedico(esp)` → `/api/especialidades/{id}/medicos` |
| MEDICO | `verAgenda`, `bloquearAgenda` → `AgendaController` / `AtencionService` |
| MEDICO | `atenderCita` → `CitaService::atender` |
| MEDICO | `registrarConsulta`, `recetarTratamiento`, `solicitarEstudio` → `AtencionService` |
| CITA | `programar`, `confirmar`, `reprogramar`, `cancelar`, `marcarNoAsistio` → `CitaService` |
| HORARIO | `consultarDisponibilidad()` → `CitaService::disponibilidad` |
| ESTADOCITA | `ocupaHorario()` → `EstadoCita::ocupaHorario()` |
| HISTORIALCITA | `registrarCambio()` → `HistorialCita::registrarCambio()` |
| NOTIFICACION | `programar`, `enviar`, `marcarLeida` → `Notificacion::programar()`, `marcarLeida()` |
| CONSULTA | `iniciar`, `registrarSignos`, `diagnosticar`, `cerrar` → `CitaService::atender`, `AtencionService` |
| TRATAMIENTO | `indicar`, `suspender` → `AtencionService::recetar`, `suspender` |
| ESTUDIO | `solicitar`, `registrarResultado`, `adjuntarArchivo` → `AtencionService` |
| HISTORIACLINICA | `consultar`, `actualizarAntecedentes` → `HistoriaClinicaController` |
| ESPECIALIDAD | `listarMedicos()` → `Especialidad::listarMedicos()` |
| MEDICAMENTO | `buscar()` → `Medicamento::buscar()` |
| CATALOGOESTUDIO | `listar()` → `CatalogoEstudio::listar()` |
| MEDICOESPECIALIDAD | `asignar`, `quitar` → `Medico::especialidades()->sync()` |
| CONTACTOEMERGENCIA | `agregar`, `editar` → `PerfilController`, `PacienteService` |
| BLOQUEOAGENDA | `crear`, `liberar` → `AtencionService::bloquear`, `liberar` |

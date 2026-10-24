<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\P1_SeguridadAcceso\AutenticacionController;
use App\Http\Controllers\P1_SeguridadAcceso\ConfiguracionController;
use App\Http\Controllers\P1_SeguridadAcceso\PanelController;
use App\Http\Controllers\P1_SeguridadAcceso\RolController;
use App\Http\Controllers\P1_SeguridadAcceso\UsuarioController;
use App\Http\Controllers\P2_GestionMedica\EspecialidadController;
use App\Http\Controllers\P2_GestionMedica\HorarioController;
use App\Http\Controllers\P2_GestionMedica\MedicoController;
use App\Http\Controllers\P3_GestionPacientes\PacienteController;
use App\Http\Controllers\P3_GestionPacientes\PerfilController;
use App\Http\Controllers\P3_GestionPacientes\RegistroController;
use App\Http\Controllers\P4_GestionCitas\AgendaController;
use App\Http\Controllers\P4_GestionCitas\AsistenciaController;
use App\Http\Controllers\P4_GestionCitas\CancelacionController;
use App\Http\Controllers\P4_GestionCitas\CitaController;
use App\Http\Controllers\P4_GestionCitas\DisponibilidadController;
use App\Http\Controllers\P4_GestionCitas\ReprogramacionController;
use App\Http\Controllers\P5_AtencionMedica\CatalogoEstudioController;
use App\Http\Controllers\P5_AtencionMedica\ConsultaController;
use App\Http\Controllers\P5_AtencionMedica\EstudioController;
use App\Http\Controllers\P5_AtencionMedica\HistoriaClinicaController;
use App\Http\Controllers\P5_AtencionMedica\MedicamentoController;
use App\Http\Controllers\P5_AtencionMedica\ResultadoController;
use App\Http\Controllers\P5_AtencionMedica\TratamientoController;
use App\Http\Controllers\P6_Reportes\BitacoraController;
use App\Http\Controllers\P6_Reportes\ReporteController;
use Illuminate\Support\Facades\Route;

// Archivos de frontend/assets (CSS y JS de las vistas)
Route::get('assets/{ruta}', AssetController::class)->where('ruta', '.*')->name('assets');

// ---------------------------------------------------------------------
// Portal público (CU9 Consultar disponibilidad)
// ---------------------------------------------------------------------
Route::get('/', [DisponibilidadController::class, 'inicio'])->name('portal');
Route::get('horarios', [DisponibilidadController::class, 'horarios'])->name('portal.horarios');
Route::get('api/especialidades/{especialidad}/medicos', [DisponibilidadController::class, 'medicos'])->name('api.medicos');
Route::get('api/medicos/{medico}/bloques', [DisponibilidadController::class, 'bloques'])->name('api.bloques');

// ---------------------------------------------------------------------
// P1 Seguridad y acceso: CU1 Iniciar sesión · CU8 Registrarse
// ---------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('login', [AutenticacionController::class, 'formulario'])->name('login');
    Route::post('login', [AutenticacionController::class, 'ingresar'])->name('login.ingresar')->middleware('throttle:10,1');
    Route::get('registro', [RegistroController::class, 'formulario'])->name('registro');
    Route::post('registro', [RegistroController::class, 'guardar'])->name('registro.guardar');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AutenticacionController::class, 'salir'])->name('logout');
    Route::get('panel', PanelController::class)->middleware('rol:ADMINISTRADOR,RECEPCIONISTA,MEDICO,PACIENTE')->name('dashboard');
    Route::get('cuenta/password', [AutenticacionController::class, 'editarPassword'])->name('password.edit');
    Route::put('cuenta/password', [AutenticacionController::class, 'cambiarPassword'])->name('password.update');

    // -----------------------------------------------------------------
    // Administrador
    // -----------------------------------------------------------------
    Route::middleware('rol:ADMINISTRADOR')->group(function () {
        // CU3 Gestionar usuarios, roles y configuración
        Route::resource('usuarios', UsuarioController::class)->except(['show', 'destroy'])->parameters(['usuarios' => 'usuario']);
        Route::patch('usuarios/{usuario}/estado', [UsuarioController::class, 'estado'])->name('usuarios.estado');
        Route::resource('roles', RolController::class)->only(['index', 'store', 'update', 'destroy'])->parameters(['roles' => 'rol']);
        Route::get('configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
        Route::put('configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');

        // CU4 Especialidades
        Route::resource('especialidades', EspecialidadController::class)->except(['show', 'destroy'])->parameters(['especialidades' => 'especialidad']);
        Route::patch('especialidades/{especialidad}/estado', [EspecialidadController::class, 'estado'])->name('especialidades.estado');

        // CU5 Médicos y CU6 Horarios
        Route::resource('medicos', MedicoController::class)->only(['create', 'store', 'edit', 'update']);
        Route::patch('medicos/{medico}/estado', [MedicoController::class, 'estado'])->name('medicos.estado');
        Route::get('medicos/{medico}/horarios', [HorarioController::class, 'index'])->name('horarios.index');
        Route::post('medicos/{medico}/horarios', [HorarioController::class, 'store'])->name('horarios.store');
        Route::patch('medicos/{medico}/horarios/{horario}/estado', [HorarioController::class, 'estado'])->name('horarios.estado');
        Route::delete('medicos/{medico}/horarios/{horario}', [HorarioController::class, 'destroy'])->name('horarios.destroy');

        // CU17 y CU18: catálogos de medicamentos y estudios
        Route::resource('medicamentos', MedicamentoController::class)->only(['index', 'store', 'update'])->parameters(['medicamentos' => 'medicamento']);
        Route::resource('catalogo-estudios', CatalogoEstudioController::class)->only(['index', 'store', 'update'])->parameters(['catalogo-estudios' => 'catalogo']);

        // CU20 Reportes y bitácora
        Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('reportes/exportar', [ReporteController::class, 'exportar'])->name('reportes.exportar');
        Route::get('bitacora', [BitacoraController::class, 'index'])->name('bitacora.index');
    });

    // -----------------------------------------------------------------
    // Administrador y recepcionista: CU7 Pacientes, consulta de médicos
    // -----------------------------------------------------------------
    Route::middleware('rol:ADMINISTRADOR,RECEPCIONISTA')->group(function () {
        Route::resource('pacientes', PacienteController::class)->except(['destroy']);
        Route::patch('pacientes/{paciente}/estado', [PacienteController::class, 'estado'])->name('pacientes.estado');
        Route::get('api/pacientes', [PacienteController::class, 'buscar'])->name('api.pacientes');
        Route::get('medicos', [MedicoController::class, 'index'])->name('medicos.index');
        Route::get('medicos/{medico}', [MedicoController::class, 'show'])->name('medicos.show');
    });

    // -----------------------------------------------------------------
    // P4 Gestión de citas (la visibilidad de cada cita depende del rol)
    // -----------------------------------------------------------------
    Route::middleware('rol:ADMINISTRADOR,RECEPCIONISTA,MEDICO,PACIENTE')->group(function () {
        Route::get('citas', [CitaController::class, 'index'])->name('citas.index');
        Route::get('citas/{cita}', [CitaController::class, 'show'])->name('citas.show')->whereNumber('cita');
        Route::get('citas/{cita}/cancelar', [CancelacionController::class, 'formulario'])->name('citas.cancelar');
        Route::post('citas/{cita}/cancelar', [CancelacionController::class, 'cancelar'])->name('citas.cancelar.guardar');
    });
    Route::middleware('rol:ADMINISTRADOR,RECEPCIONISTA,PACIENTE')->group(function () {
        Route::get('citas/nueva', [CitaController::class, 'create'])->name('citas.create');
        Route::post('citas', [CitaController::class, 'store'])->name('citas.store');
        Route::post('citas/{cita}/confirmar', [CitaController::class, 'confirmar'])->name('citas.confirmar');
        Route::get('citas/{cita}/reprogramar', [ReprogramacionController::class, 'formulario'])->name('citas.reprogramar');
        Route::put('citas/{cita}/reprogramar', [ReprogramacionController::class, 'guardar'])->name('citas.reprogramar.guardar');
    });
    Route::middleware('rol:ADMINISTRADOR,RECEPCIONISTA,MEDICO')->group(function () {
        // CU13 Registrar asistencia y CU14 Consultar agenda médica
        Route::get('asistencia', [AsistenciaController::class, 'index'])->name('asistencia.index');
        Route::post('citas/{cita}/no-asistio', [AsistenciaController::class, 'noAsistio'])->name('asistencia.no-asistio');
        Route::get('agenda', [AgendaController::class, 'index'])->name('agenda.index');
    });

    // -----------------------------------------------------------------
    // Médico: CU14 bloqueos, CU15–CU18 atención
    // -----------------------------------------------------------------
    Route::middleware('rol:MEDICO')->group(function () {
        Route::get('agenda/bloqueos', [AgendaController::class, 'bloqueos'])->name('agenda.bloqueos');
        Route::post('agenda/bloqueos', [AgendaController::class, 'bloquear'])->name('agenda.bloquear');
        Route::delete('agenda/bloqueos/{bloqueo}', [AgendaController::class, 'liberar'])->name('agenda.liberar');

        Route::post('citas/{cita}/atender', [AsistenciaController::class, 'atender'])->name('asistencia.atender');
        Route::get('consultas/{consulta}', [ConsultaController::class, 'show'])->name('consultas.show');
        Route::put('consultas/{consulta}', [ConsultaController::class, 'update'])->name('consultas.update');
        Route::post('consultas/{consulta}/cerrar', [ConsultaController::class, 'cerrar'])->name('consultas.cerrar');

        Route::post('consultas/{consulta}/tratamientos', [TratamientoController::class, 'store'])->name('tratamientos.store');
        Route::delete('tratamientos/{tratamiento}', [TratamientoController::class, 'destroy'])->name('tratamientos.destroy');

        Route::post('consultas/{consulta}/estudios', [EstudioController::class, 'store'])->name('estudios.store');
        Route::get('estudios', [EstudioController::class, 'index'])->name('estudios.index');
        Route::get('estudios/{estudio}/resultado', [EstudioController::class, 'resultado'])->name('estudios.resultado');
        Route::put('estudios/{estudio}/resultado', [EstudioController::class, 'guardarResultado'])->name('estudios.resultado.guardar');

        Route::get('pacientes/{paciente}/historia', [HistoriaClinicaController::class, 'show'])->name('historia.show');
        Route::put('pacientes/{paciente}/historia', [HistoriaClinicaController::class, 'update'])->name('historia.update');
    });

    // -----------------------------------------------------------------
    // Paciente: CU19 historial y resultados, notificaciones y perfil
    // -----------------------------------------------------------------
    Route::middleware('rol:PACIENTE')->group(function () {
        Route::get('mi-historial', [ResultadoController::class, 'historial'])->name('paciente.historial');
        Route::get('mis-resultados', [ResultadoController::class, 'resultados'])->name('paciente.resultados');
        Route::get('notificaciones', [ResultadoController::class, 'notificaciones'])->name('notificaciones.index');
        Route::post('notificaciones/leidas', [ResultadoController::class, 'marcarTodas'])->name('notificaciones.leidas');
        Route::get('mi-perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
        Route::put('mi-perfil', [PerfilController::class, 'update'])->name('perfil.update');
        Route::post('mi-perfil/contactos', [PerfilController::class, 'agregarContacto'])->name('perfil.contactos.store');
        Route::delete('mi-perfil/contactos/{contacto}', [PerfilController::class, 'eliminarContacto'])->name('perfil.contactos.destroy');
    });
});

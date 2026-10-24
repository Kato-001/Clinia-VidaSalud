<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Inicio') · MediTurno</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('assets/css/compartido/mediturno.css') }}">
</head>
<body>
@php($u = auth()->user())
<header class="barra-superior">
    <button class="boton-menu" type="button" data-abrir-menu aria-label="Abrir menú">@include('compartido.partials.icono', ['n' => 'menu'])</button>
    <a href="{{ route('dashboard') }}" class="marca">
        <span class="marca-logo">@include('compartido.partials.logo')</span>
        <span class="marca-texto"><strong>MediTurno</strong><small>Clínica Vida Salud</small></span>
    </a>
    <div class="barra-ruta">@yield('migas')</div>
    <div class="cuenta">
        @if($u->tieneRol('PACIENTE'))
            <a href="{{ route('notificaciones.index') }}" class="cuenta-campana" title="Notificaciones">
                @include('compartido.partials.icono', ['n' => 'campana'])
                @if(($noLeidas ?? 0) > 0)<b>{{ $noLeidas }}</b>@endif
            </a>
        @endif
        <div class="cuenta-datos"><strong>{{ $u->nombre }}</strong><span>{{ $u->nombre_rol }}</span></div>
        <span class="cuenta-iniciales">{{ mb_strtoupper(mb_substr($u->nombre, 0, 1)) }}</span>
    </div>
</header>

<div class="estructura">
    {{-- Menú según el rol del usuario --}}
    <aside class="lateral" id="lateral">
        <nav aria-label="Menú principal">
            @if($u->tieneRol('ADMINISTRADOR', 'RECEPCIONISTA'))
                <div class="nav-seccion">
                    <div class="nav-titulo">Recepción</div>
                    @include('compartido.partials.nav-enlace', ['ruta' => 'dashboard', 'icono' => 'inicio', 'texto' => 'Inicio'])
                    @include('compartido.partials.nav-enlace', ['ruta' => 'citas.create', 'icono' => 'calendario-mas', 'texto' => 'Programar cita'])
                    @include('compartido.partials.nav-enlace', ['ruta' => 'citas.index', 'activa' => ['citas.index', 'citas.show', 'citas.reprogramar', 'citas.cancelar'], 'icono' => 'lista', 'texto' => 'Citas'])
                    @include('compartido.partials.nav-enlace', ['ruta' => 'asistencia.index', 'icono' => 'check', 'texto' => 'Asistencia del día'])
                    @include('compartido.partials.nav-enlace', ['ruta' => 'agenda.index', 'icono' => 'reloj', 'texto' => 'Agenda médica'])
                    @include('compartido.partials.nav-enlace', ['ruta' => 'pacientes.index', 'activa' => 'pacientes.*', 'icono' => 'pacientes', 'texto' => 'Pacientes'])
                    @include('compartido.partials.nav-enlace', ['ruta' => 'medicos.index', 'activa' => ['medicos.*', 'horarios.*'], 'icono' => 'estetoscopio', 'texto' => 'Médicos'])
                </div>
            @endif

            @if($u->tieneRol('MEDICO'))
                <div class="nav-seccion">
                    <div class="nav-titulo">Mi consultorio</div>
                    @include('compartido.partials.nav-enlace', ['ruta' => 'agenda.index', 'activa' => ['agenda.index', 'consultas.*', 'historia.*'], 'icono' => 'reloj', 'texto' => 'Mi agenda'])
                    @include('compartido.partials.nav-enlace', ['ruta' => 'asistencia.index', 'icono' => 'check', 'texto' => 'Registrar asistencia'])
                    @include('compartido.partials.nav-enlace', ['ruta' => 'citas.index', 'activa' => 'citas.*', 'icono' => 'lista', 'texto' => 'Mis citas'])
                    @include('compartido.partials.nav-enlace', ['ruta' => 'estudios.index', 'activa' => 'estudios.*', 'icono' => 'matraz', 'texto' => 'Estudios'])
                    @include('compartido.partials.nav-enlace', ['ruta' => 'agenda.bloqueos', 'icono' => 'bloqueo', 'texto' => 'Bloquear agenda'])
                </div>
            @endif

            @if($u->tieneRol('PACIENTE'))
                <div class="nav-seccion">
                    <div class="nav-titulo">Mis fichas</div>
                    @include('compartido.partials.nav-enlace', ['ruta' => 'dashboard', 'icono' => 'inicio', 'texto' => 'Inicio'])
                    @include('compartido.partials.nav-enlace', ['ruta' => 'citas.create', 'icono' => 'calendario-mas', 'texto' => 'Reservar ficha'])
                    @include('compartido.partials.nav-enlace', ['ruta' => 'citas.index', 'activa' => ['citas.index', 'citas.show', 'citas.reprogramar', 'citas.cancelar'], 'icono' => 'calendario', 'texto' => 'Mis citas'])
                </div>
                <div class="nav-seccion">
                    <div class="nav-titulo">Mi salud</div>
                    @include('compartido.partials.nav-enlace', ['ruta' => 'paciente.historial', 'icono' => 'corazon', 'texto' => 'Historial clínico'])
                    @include('compartido.partials.nav-enlace', ['ruta' => 'paciente.resultados', 'icono' => 'matraz', 'texto' => 'Resultados'])
                    @include('compartido.partials.nav-enlace', ['ruta' => 'notificaciones.index', 'activa' => 'notificaciones.*', 'icono' => 'campana', 'texto' => 'Notificaciones', 'contador' => $noLeidas ?? 0])
                    @include('compartido.partials.nav-enlace', ['ruta' => 'perfil.edit', 'activa' => 'perfil.*', 'icono' => 'usuario', 'texto' => 'Mis datos'])
                </div>
            @endif

            @if($u->tieneRol('ADMINISTRADOR'))
                <div class="nav-seccion">
                    <div class="nav-titulo">Administración</div>
                    @include('compartido.partials.nav-enlace', ['ruta' => 'usuarios.index', 'activa' => 'usuarios.*', 'icono' => 'usuario', 'texto' => 'Usuarios'])
                    @include('compartido.partials.nav-enlace', ['ruta' => 'roles.index', 'activa' => 'roles.*', 'icono' => 'escudo', 'texto' => 'Roles'])
                    @include('compartido.partials.nav-enlace', ['ruta' => 'especialidades.index', 'activa' => 'especialidades.*', 'icono' => 'etiqueta', 'texto' => 'Especialidades'])
                    @include('compartido.partials.nav-enlace', ['ruta' => 'medicamentos.index', 'activa' => 'medicamentos.*', 'icono' => 'pastilla', 'texto' => 'Medicamentos'])
                    @include('compartido.partials.nav-enlace', ['ruta' => 'catalogo-estudios.index', 'activa' => 'catalogo-estudios.*', 'icono' => 'matraz', 'texto' => 'Catálogo de estudios'])
                    @include('compartido.partials.nav-enlace', ['ruta' => 'configuracion.index', 'icono' => 'ajustes', 'texto' => 'Configuración'])
                </div>
                <div class="nav-seccion">
                    <div class="nav-titulo">Reportes</div>
                    @include('compartido.partials.nav-enlace', ['ruta' => 'reportes.index', 'activa' => 'reportes.*', 'icono' => 'grafico', 'texto' => 'Reportes de citas'])
                    @include('compartido.partials.nav-enlace', ['ruta' => 'bitacora.index', 'icono' => 'documento', 'texto' => 'Bitácora'])
                </div>
            @endif
        </nav>

        <div class="lateral-pie">
            @include('compartido.partials.nav-enlace', ['ruta' => 'password.edit', 'icono' => 'llave', 'texto' => 'Cambiar contraseña'])
            <form method="POST" action="{{ route('logout') }}" data-confirmacion="¿Desea cerrar la sesión?">
                @csrf
                <button type="submit" class="nav-enlace">@include('compartido.partials.icono', ['n' => 'salir'])<span>Cerrar sesión</span></button>
            </form>
        </div>
    </aside>

    <main class="principal">
        @include('compartido.partials.alertas')
        @yield('contenido')
    </main>
</div>
<script src="{{ url('assets/js/compartido/interfaz.js') }}"></script>
@stack('scripts')
</body>
</html>

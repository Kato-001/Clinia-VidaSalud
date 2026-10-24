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
<header class="portal-nav">
    <a href="{{ route('portal') }}" class="marca">
        <span class="marca-logo">@include('compartido.partials.logo')</span>
        <span class="marca-texto"><strong>MediTurno</strong><small class="texto-suave">Clínica Vida Salud</small></span>
    </a>
    <nav>
        <a href="{{ route('portal') }}#especialidades">Especialidades</a>
        <a href="{{ route('portal') }}#medicos">Médicos</a>
        <a href="{{ route('portal.horarios') }}">Turnos disponibles</a>
        @auth
            <a href="{{ route('dashboard') }}" class="boton boton-principal boton-chico">Ir a mi panel</a>
        @else
            <a href="{{ route('login') }}" class="boton boton-borde boton-chico">Ingresar</a>
            <a href="{{ route('registro') }}" class="boton boton-verde boton-chico">Crear cuenta</a>
        @endauth
    </nav>
</header>
<main>
    @yield('contenido')
</main>
<footer class="portal-pie">MediTurno · Clínica Vida Salud · Fichas de {{ $duracion ?? 15 }} minutos con reserva en línea</footer>
<script src="{{ url('assets/js/compartido/interfaz.js') }}"></script>
@stack('scripts')
</body>
</html>

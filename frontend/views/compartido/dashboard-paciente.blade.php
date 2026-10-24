@extends('compartido.layouts.app')
@section('titulo', 'Inicio')
@section('migas', 'Mis fichas · Inicio')

@section('contenido')
<div class="encabezado">
    <div><h1>Hola, {{ $paciente?->nombres ?? auth()->user()->nombre }}</h1>
        <p class="texto-suave">Código {{ $paciente?->codigo }} · CI {{ $paciente?->ci }}</p></div>
    <a href="{{ route('citas.create') }}" class="boton boton-verde">@include('compartido.partials.icono', ['n' => 'calendario-mas']) Reservar ficha</a>
</div>

@if($noLeidas > 0)
    <div class="aviso aviso-info">@include('compartido.partials.icono', ['n' => 'campana'])
        <div>Tiene <strong>{{ $noLeidas }}</strong> aviso(s) nuevo(s). <a href="{{ route('notificaciones.index') }}">Ver notificaciones</a></div></div>
@endif

<div class="accesos">
    <a class="acceso" href="{{ route('paciente.historial') }}"><span class="indicador-icono">@include('compartido.partials.icono', ['n' => 'corazon'])</span><div><strong>Historial clínico</strong><small>Consultas, diagnósticos y recetas</small></div></a>
    <a class="acceso" href="{{ route('paciente.resultados') }}"><span class="indicador-icono verde">@include('compartido.partials.icono', ['n' => 'matraz'])</span><div><strong>Resultados</strong><small>Estudios de laboratorio e imagen</small></div></a>
    <a class="acceso" href="{{ route('perfil.edit') }}"><span class="indicador-icono ambar">@include('compartido.partials.icono', ['n' => 'usuario'])</span><div><strong>Mis datos</strong><small>Contacto y emergencias</small></div></a>
</div>

<section class="panel">
    <div class="panel-cabecera"><h2>@include('compartido.partials.icono', ['n' => 'calendario']) Mis próximas fichas</h2><a href="{{ route('citas.index') }}">Ver todas</a></div>
    @forelse($proximas as $c)
        <a class="fila-cita" href="{{ route('citas.show', $c) }}">
            <div class="fecha-cita"><strong>{{ $c->fecha->format('d') }}</strong><span>{{ $c->fecha->locale('es')->isoFormat('MMM') }}</span></div>
            <div><strong>{{ $c->especialidad->nombre }}</strong> · Dr(a). {{ $c->medico->nombre_completo }}<br>
                <small class="texto-suave"><span class="mono">{{ $c->horario }}</span> · {{ $c->codigo }}</small></div>
            @include('compartido.partials.estado-cita', ['estado' => $c->codigoestado])
        </a>
    @empty
        <p class="sin-datos">No tiene fichas reservadas. <a href="{{ route('citas.create') }}">Reserve la primera</a>.</p>
    @endforelse
</section>
@endsection

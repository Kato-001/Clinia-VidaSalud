@extends('compartido.layouts.app')
@php($esMedico = auth()->user()->tieneRol('MEDICO'))
@php($esPaciente = auth()->user()->tieneRol('PACIENTE'))
@section('titulo', $esMedico ? 'Mis citas' : ($esPaciente ? 'Mis citas' : 'Citas'))
@section('migas', $esMedico ? 'Mi consulta · Citas' : ($esPaciente ? 'Mis fichas · Mis citas' : 'Atención · Citas'))

@section('contenido')
<div class="encabezado">
    <div><h1>{{ $esMedico || $esPaciente ? 'Mis citas' : 'Citas médicas' }}</h1><p class="texto-suave">{{ $esMedico ? 'Solo se muestran sus pacientes.' : 'Filtre por fecha, médico y estado.' }}</p></div>
    @unless($esMedico)<a href="{{ route('citas.create') }}" class="boton boton-principal">+ {{ $esPaciente ? 'Reservar ficha' : 'Programar cita' }}</a>@endunless
</div>
<form class="barra-filtros" method="GET">
    <input type="date" name="fecha" value="{{ request('fecha') }}" aria-label="Fecha">
    @if($medicos->isNotEmpty())
    <select name="medico">
        <option value="">Todos los médicos</option>
        @foreach($medicos as $m)<option value="{{ $m->idmedico }}" @selected(request('medico') == $m->idmedico)>{{ $m->nombre_completo }}</option>@endforeach
    </select>
    @endif
    <select name="estado">
        <option value="">Todos los estados</option>
        @foreach($estados as $e)<option value="{{ $e->codigo }}" @selected(request('estado') === $e->codigo)>{{ $e->nombre }}</option>@endforeach
    </select>
    <button class="boton boton-borde">Filtrar</button>
    @if(request()->hasAny(['fecha','medico','estado']))<a href="{{ route('citas.index') }}" class="boton boton-plano">Limpiar</a>@endif
</form>
<div class="panel">
    <table class="tabla-datos">
        <thead><tr><th>Código</th><th>Fecha</th><th>Horario</th>@unless($esPaciente)<th>Paciente</th>@endunless<th>Médico</th><th>Especialidad</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @forelse($citas as $c)
            <tr>
                <td><a href="{{ route('citas.show', $c) }}"><strong>{{ $c->codigo }}</strong></a></td>
                <td>{{ $c->fecha->format('d/m/Y') }}</td>
                <td class="mono">{{ $c->horario }}</td>
                @unless($esPaciente)<td>{{ $c->paciente->nombre_completo }}</td>@endunless
                <td>{{ $c->medico->nombre_completo }}</td>
                <td>{{ $c->especialidad->nombre }}</td>
                <td>@include('compartido.partials.estado-cita', ['estado' => $c->codigoestado])</td>
                <td class="celda-acciones"><a href="{{ route('citas.show', $c) }}" class="boton boton-plano boton-chico">Ver</a></td>
            </tr>
        @empty
            <tr><td colspan="8" class="sin-datos">No hay citas con esos filtros.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $citas->links('compartido.partials.paginacion') }}
</div>
@endsection

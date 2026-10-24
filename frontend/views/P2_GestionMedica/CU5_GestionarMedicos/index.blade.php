@extends('compartido.layouts.app')
@section('titulo', 'Médicos')
@section('migas', 'Atención · Médicos')

@section('contenido')
<div class="encabezado">
    <div><h1>Médicos</h1><p class="texto-suave">Profesionales, especialidades y estado.</p></div>
    @if(auth()->user()->tieneRol('ADMINISTRADOR'))
        <a href="{{ route('medicos.create') }}" class="boton boton-principal">+ Nuevo médico</a>
    @endif
</div>
<form class="barra-filtros" method="GET">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre o código">
    <select name="especialidad">
        <option value="">Todas las especialidades</option>
        @foreach($especialidades as $e)<option value="{{ $e->idespecialidad }}" @selected(request('especialidad') == $e->idespecialidad)>{{ $e->nombre }}</option>@endforeach
    </select>
    <button class="boton boton-borde">Buscar</button>
</form>
<div class="panel">
    <table class="tabla-datos">
        <thead><tr><th>Código</th><th>Médico</th><th>Reg. profesional</th><th>Especialidades</th><th>Teléfono</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @forelse($medicos as $m)
            <tr>
                <td class="mono">{{ $m->codigo }}</td>
                <td><a href="{{ route('medicos.show', $m) }}"><strong>{{ $m->nombre_completo }}</strong></a></td>
                <td class="mono">{{ $m->registroprofesional }}</td>
                <td>@foreach($m->especialidades as $e)<span class="pastilla">{{ $e->nombre }}</span>@endforeach</td>
                <td>{{ $m->telefono ?? '—' }}</td>
                <td>@include('compartido.partials.estado', ['estado' => $m->estado])</td>
                <td class="celda-acciones">
                    <a href="{{ route('medicos.show', $m) }}" class="boton boton-plano boton-chico">Ver</a>
                    <a href="{{ route('agenda.index', ['medico' => $m->idmedico]) }}" class="boton boton-plano boton-chico">Agenda</a>
                    @if(auth()->user()->tieneRol('ADMINISTRADOR'))
                        <a href="{{ route('horarios.index', $m) }}" class="boton boton-plano boton-chico">Horarios</a>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="sin-datos">No se encontraron médicos.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $medicos->links('compartido.partials.paginacion') }}
</div>
@endsection

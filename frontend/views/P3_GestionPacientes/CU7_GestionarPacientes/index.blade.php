@extends('compartido.layouts.app')
@section('titulo', 'Pacientes')
@section('migas', 'Atención · Pacientes')

@section('contenido')
<div class="encabezado">
    <div><h1>Pacientes</h1><p class="texto-suave">Busque por CI, nombre, apellido o código.</p></div>
    <a href="{{ route('pacientes.create') }}" class="boton boton-principal">+ Nuevo paciente</a>
</div>
<form class="barra-filtros" method="GET">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="CI, nombre, apellido o código" autofocus>
    <select name="estado">
        <option value="">Todos</option>
        <option value="ACTIVO" @selected(request('estado') === 'ACTIVO')>Activos</option>
        <option value="INACTIVO" @selected(request('estado') === 'INACTIVO')>Inactivos</option>
    </select>
    <button class="boton boton-borde">Buscar</button>
</form>
<div class="panel">
    <table class="tabla-datos">
        <thead><tr><th>Código</th><th>CI</th><th>Paciente</th><th>Edad</th><th>Teléfono</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @forelse($pacientes as $p)
            <tr>
                <td class="mono">{{ $p->codigo }}</td>
                <td class="mono">{{ $p->ci }}</td>
                <td><a href="{{ route('pacientes.show', $p) }}"><strong>{{ $p->apellidos }}, {{ $p->nombres }}</strong></a></td>
                <td>{{ $p->edad }} años</td>
                <td>{{ $p->telefono ?? '—' }}</td>
                <td>@include('compartido.partials.estado', ['estado' => $p->estado])</td>
                <td class="celda-acciones">
                    <a href="{{ route('pacientes.show', $p) }}" class="boton boton-plano boton-chico">Ver</a>
                    @if($p->estado === 'ACTIVO')<a href="{{ route('citas.create', ['paciente' => $p->idpaciente]) }}" class="boton boton-plano boton-chico">Programar cita</a>@endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="sin-datos">No se encontraron pacientes{{ request('q') ? ' para «' . request('q') . '»' : '' }}.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $pacientes->links('compartido.partials.paginacion') }}
</div>
@endsection

@extends('compartido.layouts.app')
@section('titulo', 'Bitácora')
@section('migas', 'Reportes · Bitácora')

@section('contenido')
<div class="encabezado"><div><h1>Bitácora del sistema</h1><p class="texto-suave">Registro de las acciones importantes (solo lectura).</p></div></div>

<form class="barra-filtros" method="GET">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar acción, descripción o usuario">
    <select name="modulo">
        <option value="">Todos los módulos</option>
        @foreach($modulos as $m)<option value="{{ $m }}" @selected(request('modulo') === $m)>{{ ucfirst(mb_strtolower(str_replace('_', ' ', $m))) }}</option>@endforeach
    </select>
    <input type="date" name="desde" value="{{ request('desde') }}" aria-label="Desde">
    <input type="date" name="hasta" value="{{ request('hasta') }}" aria-label="Hasta">
    <button class="boton boton-borde">Filtrar</button>
</form>

<div class="panel">
    <table class="tabla-datos">
        <thead><tr><th>Fecha</th><th>Usuario</th><th>Módulo</th><th>Acción</th><th>Descripción</th><th>IP</th></tr></thead>
        <tbody>
        @forelse($registros as $r)
            <tr>
                <td>{{ $r->fecha->format('d/m/Y') }}</td>
                <td class="mono">{{ $r->usuario?->username }}</td>
                <td>{{ ucfirst(mb_strtolower(str_replace('_', ' ', $r->modulo))) }}</td>
                <td><strong>{{ ucfirst(mb_strtolower(str_replace('_', ' ', $r->accion))) }}</strong></td>
                <td>{{ $r->descripcion }}</td>
                <td class="mono">{{ $r->ip }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="sin-datos">No hay registros con esos filtros.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $registros->links('compartido.partials.paginacion') }}
</div>
@endsection

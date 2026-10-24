@extends('compartido.layouts.app')
@section('titulo', 'Especialidades')
@section('migas', 'Administración · Especialidades')

@section('contenido')
<div class="encabezado">
    <div><h1>Especialidades médicas</h1><p class="texto-suave">Servicios que ofrece la clínica.</p></div>
    <a href="{{ route('especialidades.create') }}" class="boton boton-principal">+ Nueva especialidad</a>
</div>
<form class="barra-filtros" method="GET">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar especialidad">
    <button class="boton boton-borde">Buscar</button>
</form>
<div class="panel">
    <table class="tabla-datos">
        <thead><tr><th>Nombre</th><th>Descripción</th><th>Médicos</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @forelse($especialidades as $e)
            <tr>
                <td><strong>{{ $e->nombre }}</strong></td>
                <td>{{ $e->descripcion ?? '—' }}</td>
                <td>{{ $e->medicos_count }}</td>
                <td>@include('compartido.partials.estado', ['estado' => $e->estado])</td>
                <td class="celda-acciones">
                    <a href="{{ route('especialidades.edit', $e) }}" class="boton boton-plano boton-chico">Editar</a>
                    <form method="POST" action="{{ route('especialidades.estado', $e) }}" data-confirmacion="¿{{ $e->estado === 'ACTIVO' ? 'Desactivar' : 'Activar' }} {{ $e->nombre }}?">
                        @csrf @method('PATCH')<button class="boton boton-plano boton-chico">{{ $e->estado === 'ACTIVO' ? 'Desactivar' : 'Activar' }}</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="sin-datos">No hay especialidades registradas.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $especialidades->links('compartido.partials.paginacion') }}
</div>
@endsection

@extends('compartido.layouts.app')
@section('titulo', 'Catálogo de estudios')
@section('migas', 'Administración · Catálogo de estudios')

@section('contenido')
<div class="encabezado"><div><h1>Catálogo de estudios</h1><p class="texto-suave">Estudios de laboratorio e imagen que los médicos pueden solicitar.</p></div></div>

<div class="columnas-2 columnas-formulario">
    <form class="panel formulario" method="POST" action="{{ $editar ? route('catalogo-estudios.update', $editar) : route('catalogo-estudios.store') }}" data-validacion novalidate>
        @csrf @if($editar) @method('PUT') @endif
        <h2>{{ $editar ? 'Editar estudio' : 'Nuevo estudio' }}</h2>
        <label class="campo"><span>Nombre *</span>
            <input name="nombre" value="{{ old('nombre', $editar?->nombre) }}" required maxlength="150"></label>
        <label class="campo"><span>Categoría</span>
            <input name="categoria" value="{{ old('categoria', $editar?->categoria) }}" maxlength="100" placeholder="Laboratorio, Imagenología…"></label>
        <label class="campo"><span>Descripción</span>
            <textarea name="descripcion" rows="2" maxlength="255">{{ old('descripcion', $editar?->descripcion) }}</textarea></label>
        <label class="campo"><span>Estado *</span>
            <select name="estado" required>
                @foreach(['ACTIVO' => 'Activo', 'INACTIVO' => 'Inactivo'] as $k => $v)<option value="{{ $k }}" @selected(old('estado', $editar?->estado ?? 'ACTIVO') === $k)>{{ $v }}</option>@endforeach
            </select></label>
        <div class="acciones-formulario">
            @if($editar)<a href="{{ route('catalogo-estudios.index') }}" class="boton boton-plano">Cancelar</a>@endif
            <button class="boton boton-principal">Guardar</button>
        </div>
    </form>

    <div>
        <form class="barra-filtros" method="GET">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre o categoría">
            <button class="boton boton-borde">Buscar</button>
        </form>
        <div class="panel">
            <table class="tabla-datos">
                <thead><tr><th>Estudio</th><th>Categoría</th><th>Descripción</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                @forelse($estudios as $e)
                    <tr>
                        <td><strong>{{ $e->nombre }}</strong></td>
                        <td>{{ $e->categoria ?? '—' }}</td>
                        <td>{{ $e->descripcion ?? '—' }}</td>
                        <td>@include('compartido.partials.estado', ['estado' => $e->estado])</td>
                        <td class="celda-acciones"><a href="{{ route('catalogo-estudios.index', ['editar' => $e->idcatalogo] + request()->only('q', 'page')) }}" class="boton boton-plano boton-chico">Editar</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="sin-datos">No hay estudios en el catálogo.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $estudios->links('compartido.partials.paginacion') }}
        </div>
    </div>
</div>
@endsection

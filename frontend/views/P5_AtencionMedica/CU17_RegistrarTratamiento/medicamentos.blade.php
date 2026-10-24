@extends('compartido.layouts.app')
@section('titulo', 'Medicamentos')
@section('migas', 'Administración · Medicamentos')

@section('contenido')
<div class="encabezado"><div><h1>Medicamentos</h1><p class="texto-suave">Catálogo que usan los médicos al recetar. Los inactivos no aparecen en la receta.</p></div></div>

<div class="columnas-2 columnas-formulario">
    <form class="panel formulario" method="POST" action="{{ $editar ? route('medicamentos.update', $editar) : route('medicamentos.store') }}" data-validacion novalidate>
        @csrf @if($editar) @method('PUT') @endif
        <h2>{{ $editar ? 'Editar medicamento' : 'Nuevo medicamento' }}</h2>
        <label class="campo"><span>Nombre y concentración *</span>
            <input name="nombre" value="{{ old('nombre', $editar?->nombre) }}" required maxlength="150" placeholder="Ej. Paracetamol 500 mg"></label>
        <label class="campo"><span>Presentación</span>
            <input name="presentacion" value="{{ old('presentacion', $editar?->presentacion) }}" maxlength="100" placeholder="Tabletas, jarabe, inhalador…"></label>
        <label class="campo"><span>Estado *</span>
            <select name="estado" required>
                @foreach(['ACTIVO' => 'Activo', 'INACTIVO' => 'Inactivo'] as $k => $v)<option value="{{ $k }}" @selected(old('estado', $editar?->estado ?? 'ACTIVO') === $k)>{{ $v }}</option>@endforeach
            </select></label>
        <div class="acciones-formulario">
            @if($editar)<a href="{{ route('medicamentos.index') }}" class="boton boton-plano">Cancelar</a>@endif
            <button class="boton boton-principal">Guardar</button>
        </div>
    </form>

    <div>
        <form class="barra-filtros" method="GET">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar medicamento">
            <button class="boton boton-borde">Buscar</button>
        </form>
        <div class="panel">
            <table class="tabla-datos">
                <thead><tr><th>Medicamento</th><th>Presentación</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                @forelse($medicamentos as $m)
                    <tr>
                        <td><strong>{{ $m->nombre }}</strong></td>
                        <td>{{ $m->presentacion ?? '—' }}</td>
                        <td>@include('compartido.partials.estado', ['estado' => $m->estado])</td>
                        <td class="celda-acciones"><a href="{{ route('medicamentos.index', ['editar' => $m->idmedicamento] + request()->only('q', 'page')) }}" class="boton boton-plano boton-chico">Editar</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="sin-datos">No hay medicamentos registrados.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $medicamentos->links('compartido.partials.paginacion') }}
        </div>
    </div>
</div>
@endsection

@extends('compartido.layouts.app')
@section('titulo', 'Configuración')
@section('migas', 'Administración · Configuración')

@section('contenido')
<div class="encabezado"><div><h1>Configuración del sistema</h1><p class="texto-suave">Parámetros que usan las reglas de reserva de fichas.</p></div></div>

<form class="panel formulario angosto" method="POST" action="{{ route('configuracion.update') }}" data-validacion novalidate>
    @csrf @method('PUT')
    @foreach($parametros as $p)
        <label class="campo"><span>{{ $p->descripcion ?? $p->clave }}</span>
            <input name="valores[{{ $p->clave }}]" value="{{ old('valores.' . $p->clave, $p->valor) }}" required maxlength="255">
            <small class="nota mono">{{ $p->clave }}</small></label>
    @endforeach
    <div class="acciones-formulario"><button class="boton boton-principal">Guardar cambios</button></div>
</form>
@endsection

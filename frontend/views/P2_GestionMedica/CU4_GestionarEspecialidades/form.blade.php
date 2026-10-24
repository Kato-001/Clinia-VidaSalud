@extends('compartido.layouts.app')
@php($editando = $especialidad->exists)
@section('titulo', $editando ? 'Editar especialidad' : 'Nueva especialidad')
@section('migas', 'Administración · Especialidades · ' . ($editando ? 'Editar' : 'Nueva'))

@section('contenido')
<div class="encabezado"><div><h1>{{ $editando ? 'Editar especialidad' : 'Registrar especialidad' }}</h1></div></div>
<form class="panel formulario angosto" method="POST" action="{{ $editando ? route('especialidades.update', $especialidad) : route('especialidades.store') }}" data-validacion novalidate>
    @csrf @if($editando) @method('PUT') @endif
    <label class="campo"><span>Nombre *</span>
        <input name="nombre" value="{{ old('nombre', $especialidad->nombre) }}" required minlength="3" maxlength="100" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s\-\.]+" data-mensaje="Solo letras y espacios."></label>
    <label class="campo"><span>Descripción</span>
        <textarea name="descripcion" rows="3" maxlength="255">{{ old('descripcion', $especialidad->descripcion) }}</textarea></label>
    <div class="acciones-formulario">
        <a href="{{ route('especialidades.index') }}" class="boton boton-plano">Cancelar</a>
        <button class="boton boton-principal">Guardar</button>
    </div>
</form>
@endsection

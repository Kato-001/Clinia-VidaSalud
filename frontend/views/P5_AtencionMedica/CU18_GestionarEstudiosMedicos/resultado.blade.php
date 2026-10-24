@extends('compartido.layouts.app')
@section('titulo', 'Resultado de estudio')
@section('migas', 'Mi consulta · Estudios · Resultado')

@section('contenido')
@php($paciente = $estudio->consulta->cita->paciente)
<div class="encabezado"><div><h1>{{ $estudio->catalogo->nombre }}</h1>
    <p class="texto-suave">{{ $paciente->nombre_completo }} · CI {{ $paciente->ci }} · Solicitado el {{ $estudio->fechasolicitud->format('d/m/Y') }}</p></div>
    @include('compartido.partials.estado-cita', ['estado' => $estudio->estado])</div>

{{-- ESTUDIO.registrarResultado() + adjuntarArchivo() --}}
<form class="panel formulario angosto" method="POST" action="{{ route('estudios.resultado.guardar', $estudio) }}" data-validacion novalidate>
    @csrf @method('PUT')
    @if($estudio->indicaciones)<p class="nota">Indicaciones: {{ $estudio->indicaciones }}</p>@endif
    <label class="campo"><span>Resultado *</span>
        <textarea name="resultado" rows="6" required maxlength="5000">{{ old('resultado', $estudio->resultado) }}</textarea></label>
    <label class="campo"><span>Enlace al archivo (opcional)</span>
        <input type="url" name="archivoresultado" value="{{ old('archivoresultado', $estudio->archivoresultado) }}" maxlength="500" placeholder="https://…"></label>
    <p class="nota">Al guardar, el estudio queda como completado y se notifica al paciente.</p>
    <div class="acciones-formulario">
        <a href="{{ route('estudios.index') }}" class="boton boton-plano">Volver</a>
        <button class="boton boton-principal">Guardar resultado</button>
    </div>
</form>
@endsection

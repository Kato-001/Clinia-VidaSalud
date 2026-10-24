@extends('compartido.layouts.app')
@section('titulo', 'Reprogramar ' . $cita->codigo)
@section('migas', 'Citas · ' . $cita->codigo . ' · Reprogramar')

@section('contenido')
<div class="encabezado"><div><h1>Reprogramar cita {{ $cita->codigo }}</h1>
    <p class="texto-suave">{{ $cita->paciente->nombre_completo }} · {{ $cita->especialidad->nombre }} con Dr(a). {{ $cita->medico->nombre_completo }}</p></div></div>

<form method="POST" action="{{ route('citas.reprogramar.guardar', $cita) }}" id="form-reprogramar" class="columnas-lateral" data-validacion novalidate
      data-url-bloques="{{ url('api/medicos') }}" data-medico="{{ $cita->idmedico }}" data-actual="{{ $cita->dia }} {{ $cita->hora }}">
    @csrf @method('PUT')
    <section class="panel">
        <h2 style="margin-bottom:1rem">Nuevo horario</h2>
        <label class="campo angosto"><span>Nueva fecha <em>*</em></span>
            <input type="date" name="fecha" required min="{{ today()->toDateString() }}" value="{{ old('fecha', $cita->dia) }}"></label>
        <input type="hidden" name="horainicio" value="{{ old('horainicio') }}" required data-mensaje="Elija el nuevo turno.">
        <div id="turnos"><p class="nota">Elija la fecha para ver los turnos libres.</p></div>
        <label class="campo"><span>¿Por qué cambia la cita?</span>
            <input name="motivo" maxlength="255" value="{{ old('motivo') }}" placeholder="Opcional"></label>
        <p class="nota">El horario anterior queda libre para otros pacientes. Cada ficha dura {{ $duracion }} minutos.</p>
    </section>

    <aside class="panel resumen-lateral panel-destacado">
        <h2 style="margin-bottom:1rem">Cambio de horario</h2>
        <dl class="ficha-datos">
            <dt>Actual</dt><dd>{{ $cita->fecha->format('d/m/Y') }} · <span class="mono">{{ $cita->horario }}</span></dd>
            <dt>Nuevo</dt><dd data-resumen="nuevo" class="mono">—</dd>
        </dl>
        <button class="boton boton-principal boton-ancho" type="submit">Guardar nuevo horario</button>
        <a href="{{ route('citas.show', $cita) }}" class="boton boton-plano boton-ancho">Volver</a>
    </aside>
</form>
@endsection

@push('scripts')
<script src="{{ url('assets/js/compartido/turnos.js') }}"></script>
<script src="{{ url('assets/js/P4_GestionCitas/CU11_ReprogramarCita/reprogramar.js') }}"></script>
@endpush

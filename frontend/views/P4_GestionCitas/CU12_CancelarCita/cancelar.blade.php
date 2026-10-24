@extends('compartido.layouts.app')
@section('titulo', 'Cancelar ' . $cita->codigo)
@section('migas', 'Citas · ' . $cita->codigo . ' · Cancelar')

@section('contenido')
<div class="encabezado"><div><h1>Cancelar cita {{ $cita->codigo }}</h1><p class="texto-suave">La cita queda en el historial y el horario se libera para otros pacientes.</p></div></div>

<div class="panel angosto">
    <dl class="ficha-datos">
        <dt>Paciente</dt><dd>{{ $cita->paciente->nombre_completo }}</dd>
        <dt>Médico</dt><dd>{{ $cita->medico->nombre_completo }} · {{ $cita->especialidad->nombre }}</dd>
        <dt>Fecha</dt><dd>{{ ucfirst($cita->fecha->locale('es')->isoFormat('dddd D [de] MMMM')) }} · <span class="mono">{{ $cita->horario }}</span></dd>
    </dl>
</div>

<form class="panel formulario angosto" method="POST" action="{{ route('citas.cancelar.guardar', $cita) }}" data-validacion novalidate data-confirmacion="¿Confirma la cancelación de la cita {{ $cita->codigo }}?">
    @csrf
    <label class="campo"><span>Motivo de la cancelación *</span>
        <textarea name="motivo" rows="3" required minlength="3" maxlength="255" placeholder="Ej. Viaje, mejoró mi salud, problema de horario…">{{ old('motivo') }}</textarea></label>
    @if(auth()->user()->tieneRol('PACIENTE'))
        <p class="nota">Puede cancelar hasta {{ \App\Models\Configuracion::numero('HORAS_MIN_CANCELACION', 2) }} hora(s) antes de la cita. Después, comuníquese con la clínica.</p>
    @endif
    <div class="acciones-formulario">
        <a href="{{ route('citas.show', $cita) }}" class="boton boton-plano">Volver</a>
        <button class="boton boton-peligro">Cancelar cita</button>
    </div>
</form>
@endsection

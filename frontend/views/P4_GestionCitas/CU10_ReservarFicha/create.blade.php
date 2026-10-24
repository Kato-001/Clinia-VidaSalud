@extends('compartido.layouts.app')
@section('titulo', $esPaciente ? 'Reservar ficha' : 'Programar cita')
@section('migas', $esPaciente ? 'Mis fichas · Reservar' : 'Recepción · Programar cita')

@section('contenido')
<div class="encabezado"><div><h1>{{ $esPaciente ? 'Reservar ficha' : 'Programar cita' }}</h1>
    <p class="texto-suave">Solo se ofrecen turnos libres. Al confirmar, el sistema vuelve a verificar la disponibilidad.</p></div></div>

<form method="POST" action="{{ route('citas.store') }}" id="form-reserva" class="columnas-lateral" data-validacion novalidate
      data-url-medicos="{{ url('api/especialidades') }}" data-url-bloques="{{ url('api/medicos') }}" data-url-pacientes="{{ route('api.pacientes') }}"
      data-medico-anterior="{{ old('idmedico') }}" data-hora-anterior="{{ old('horainicio') }}">
    @csrf
    <div class="pasos">
        <section class="panel paso">
            <h2>¿Para quién es la cita?</h2>
            @if($esPaciente)
                <p><strong>{{ $pacienteSeleccionado->nombre_completo }}</strong> <span class="texto-suave">· CI {{ $pacienteSeleccionado->ci }} · {{ $pacienteSeleccionado->codigo }}</span></p>
            @else
                <input type="hidden" name="idpaciente" value="{{ old('idpaciente', $pacienteSeleccionado?->idpaciente) }}" required data-mensaje="Busque y elija al paciente.">
                <div class="campo buscador">
                    <span>Buscar paciente <em>*</em></span>
                    <input type="search" id="buscar-paciente" autocomplete="off" placeholder="CI, nombre o apellido (mínimo 2 letras)" value="{{ $pacienteSeleccionado?->nombre_completo }}">
                    <ul class="sugerencias" id="sugerencias" hidden></ul>
                </div>
                <p class="nota">¿No está registrado? <a href="{{ route('pacientes.create') }}">Registre al paciente</a>.</p>
            @endif
        </section>

        <section class="panel paso">
            <h2>Especialidad y médico</h2>
            <div class="columnas-2">
                <label class="campo"><span>Especialidad <em>*</em></span>
                    <select name="idespecialidad" required>
                        <option value="">Elija…</option>
                        @foreach($especialidades as $e)<option value="{{ $e->idespecialidad }}" @selected(old('idespecialidad') == $e->idespecialidad)>{{ $e->nombre }}</option>@endforeach
                    </select></label>
                <label class="campo"><span>Médico <em>*</em></span>
                    <select name="idmedico" required disabled><option value="">Primero elija la especialidad</option></select></label>
            </div>
            <p class="nota" id="dias-atencion"></p>
        </section>

        <section class="panel paso">
            <h2>Fecha y turno</h2>
            <label class="campo angosto"><span>Fecha <em>*</em></span>
                <input type="date" name="fecha" required min="{{ today()->toDateString() }}" value="{{ old('fecha', today()->addDay()->toDateString()) }}"></label>
            <input type="hidden" name="horainicio" value="" required data-mensaje="Elija uno de los turnos libres.">
            <div id="turnos"><p class="nota">Elija médico y fecha para ver los turnos.</p></div>
            <div class="columnas-2">
                <label class="campo"><span>Modalidad <em>*</em></span>
                    <select name="modalidad" required>
                        @foreach(\App\Models\Cita::MODALIDADES as $k => $v)<option value="{{ $k }}" @selected(old('modalidad', 'PRESENCIAL') === $k)>{{ $v }}</option>@endforeach
                    </select></label>
                <label class="campo"><span>Motivo de la consulta</span>
                    <input name="motivo" maxlength="255" value="{{ old('motivo') }}" placeholder="Ej. Control de presión"></label>
            </div>
        </section>
    </div>

    <aside class="panel resumen-lateral panel-destacado">
        <h2 style="margin-bottom:1rem">Resumen de la ficha</h2>
        <dl class="ficha-datos">
            <dt>Paciente</dt><dd data-resumen="paciente">{{ $pacienteSeleccionado?->nombre_completo ?? '—' }}</dd>
            <dt>Especialidad</dt><dd data-resumen="especialidad">—</dd>
            <dt>Médico</dt><dd data-resumen="medico">—</dd>
            <dt>Fecha</dt><dd data-resumen="fecha">—</dd>
            <dt>Turno</dt><dd data-resumen="hora" class="mono">—</dd>
        </dl>
        <p class="nota">Duración: {{ $duracion }} minutos.</p>
        <button class="boton boton-verde boton-ancho" type="submit">Confirmar reserva</button>
        <a href="{{ $esPaciente ? route('dashboard') : route('citas.index') }}" class="boton boton-plano boton-ancho">Volver</a>
    </aside>
</form>
@endsection

@push('scripts')
<script src="{{ url('assets/js/compartido/turnos.js') }}"></script>
<script src="{{ url('assets/js/P4_GestionCitas/CU10_ReservarFicha/reservar-ficha.js') }}"></script>
@endpush

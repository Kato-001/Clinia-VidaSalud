@extends('compartido.layouts.app')
@section('titulo', $paciente->nombre_completo)
@section('migas', 'Atención · Pacientes · ' . $paciente->codigo)

@section('contenido')
@php($activo = $paciente->estado === 'ACTIVO')
<div class="encabezado">
    <div><h1>{{ $paciente->nombre_completo }}</h1>
        <p class="texto-suave"><span class="mono">{{ $paciente->codigo }}</span> · CI {{ $paciente->ci }} · {{ $paciente->edad }} años · @include('compartido.partials.estado', ['estado' => $paciente->estado])</p></div>
    <div class="grupo-botones">
        @if($activo)<a href="{{ route('citas.create', ['paciente' => $paciente->idpaciente]) }}" class="boton boton-principal">+ Programar cita</a>@endif
        <a href="{{ route('pacientes.edit', $paciente) }}" class="boton boton-borde">Editar</a>
        <form method="POST" action="{{ route('pacientes.estado', $paciente) }}" data-confirmacion="{{ $activo ? 'El paciente se desactivará, pero su historial se conserva. ¿Continuar?' : '¿Activar al paciente?' }}">
            @csrf @method('PATCH')<button class="boton boton-plano">{{ $activo ? 'Desactivar' : 'Activar' }}</button>
        </form>
    </div>
</div>
<div class="columnas-3">
    <div class="panel"><h2>Contacto</h2><dl class="ficha-datos">
        <dt>Teléfono</dt><dd>{{ $paciente->telefono ?? '—' }}</dd>
        <dt>Correo</dt><dd>{{ $paciente->email ?? '—' }}</dd>
        <dt>Dirección</dt><dd>{{ $paciente->direccion ?? '—' }}</dd>
        <dt>Notificaciones</dt><dd>{{ $paciente->aceptanotificaciones ? 'Acepta' : 'No acepta' }}</dd></dl></div>
    <div class="panel"><h2>Información médica</h2><dl class="ficha-datos">
        <dt>Nacimiento</dt><dd>{{ $paciente->fechanacimiento->format('d/m/Y') }}</dd>
        <dt>Sexo</dt><dd>{{ \App\Models\Paciente::SEXOS[$paciente->sexo] ?? $paciente->sexo }}</dd>
        <dt>Tipo de sangre</dt><dd>{{ $paciente->tiposangre ?? '—' }}</dd>
        <dt>Alergias</dt><dd>{{ $paciente->historiaClinica?->alergias ?? 'Ninguna registrada' }}</dd>
        <dt>Historia clínica</dt><dd class="mono">{{ $paciente->historiaClinica?->numero ?? '—' }}</dd></dl></div>
    <div class="panel"><h2>Emergencia</h2><dl class="ficha-datos">
        @forelse($paciente->contactos as $ce)
            <dt>{{ $ce->parentesco }}</dt><dd>{{ $ce->nombre }} · {{ $ce->telefono }}</dd>
        @empty
            <dt>Contacto</dt><dd>—</dd>
        @endforelse</dl></div>
</div>
<div class="panel">
    <div class="panel-cabecera"><h2>Citas del paciente</h2><span class="texto-suave">{{ $paciente->citas->count() }} registradas</span></div>
    <table class="tabla-datos">
        <thead><tr><th>Código</th><th>Fecha</th><th>Horario</th><th>Médico</th><th>Especialidad</th><th>Estado</th></tr></thead>
        <tbody>
        @forelse($paciente->citas as $c)
            <tr>
                <td><a href="{{ route('citas.show', $c) }}">{{ $c->codigo }}</a></td>
                <td>{{ $c->fecha->format('d/m/Y') }}</td>
                <td class="mono">{{ $c->horario }}</td>
                <td>{{ $c->medico->nombre_completo }}</td>
                <td>{{ $c->especialidad->nombre }}</td>
                <td>@include('compartido.partials.estado-cita', ['estado' => $c->codigoestado])</td>
            </tr>
        @empty
            <tr><td colspan="6" class="sin-datos">El paciente aún no tiene citas.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection

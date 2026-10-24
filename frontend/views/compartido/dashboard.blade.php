@extends('compartido.layouts.app')
@section('titulo', 'Inicio')
@section('migas', 'Recepción · Inicio')

@section('contenido')
<div class="encabezado">
    <div>
        <h1>Buen día, {{ explode(' ', auth()->user()->nombre)[0] }}</h1>
        <p class="texto-suave">{{ ucfirst(now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY')) }}</p>
    </div>
    <div class="grupo-botones">
        <a href="{{ route('pacientes.create') }}" class="boton boton-borde">Nuevo paciente</a>
        <a href="{{ route('citas.create') }}" class="boton boton-principal">@include('compartido.partials.icono', ['n' => 'calendario-mas']) Programar cita</a>
    </div>
</div>

<div class="indicadores">
    <div class="indicador"><span class="indicador-icono">@include('compartido.partials.icono', ['n' => 'calendario'])</span><div><span>Citas de hoy</span><strong>{{ $totales['citasHoy'] }}</strong></div></div>
    <div class="indicador"><span class="indicador-icono ambar">@include('compartido.partials.icono', ['n' => 'reloj'])</span><div><span>Próximas pendientes</span><strong>{{ $totales['proximas'] }}</strong></div></div>
    <div class="indicador"><span class="indicador-icono verde">@include('compartido.partials.icono', ['n' => 'pacientes'])</span><div><span>Pacientes activos</span><strong>{{ $totales['pacientes'] }}</strong></div></div>
    <div class="indicador"><span class="indicador-icono verde">@include('compartido.partials.icono', ['n' => 'estetoscopio'])</span><div><span>Médicos activos</span><strong>{{ $totales['medicos'] }}</strong></div></div>
</div>

<section class="panel">
    <div class="panel-cabecera">
        <h2>@include('compartido.partials.icono', ['n' => 'reloj']) Citas de hoy</h2>
        <a href="{{ route('asistencia.index') }}" class="boton boton-borde boton-chico">Registrar asistencia</a>
    </div>
    <div class="tabla-contenedor">
        <table class="tabla-datos">
            <thead><tr><th>Hora</th><th>Paciente</th><th>Médico</th><th>Especialidad</th><th>Estado</th><th></th></tr></thead>
            <tbody>
            @forelse($citasHoy as $c)
                <tr>
                    <td class="mono">{{ $c->horario }}</td>
                    <td><strong>{{ $c->paciente->nombre_completo }}</strong></td>
                    <td>{{ $c->medico->nombre_completo }}</td>
                    <td>{{ $c->especialidad->nombre }}</td>
                    <td>@include('compartido.partials.estado-cita', ['estado' => $c->codigoestado])</td>
                    <td class="celda-acciones"><a href="{{ route('citas.show', $c) }}" class="boton boton-plano boton-chico">{{ $c->codigo }}</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="sin-datos">No hay citas programadas para hoy.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection

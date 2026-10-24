@extends('compartido.layouts.app')
@section('titulo', 'Registrar asistencia')
@section('migas', 'Atención · Asistencia')

@section('contenido')
<div class="encabezado"><div><h1>Registrar asistencia</h1>
    <p class="texto-suave">{{ ucfirst(\Illuminate\Support\Carbon::parse($fecha)->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY')) }} ·
        {{ $esMedico ? 'Marque si el paciente asistió para iniciar la consulta.' : 'Marque a los pacientes que no se presentaron.' }}</p></div></div>

<form class="barra-filtros" method="GET">
    <input type="date" name="fecha" value="{{ $fecha }}" max="{{ today()->toDateString() }}" aria-label="Fecha">
    @if($medicos->isNotEmpty())
    <select name="medico">
        <option value="">Todos los médicos</option>
        @foreach($medicos as $m)<option value="{{ $m->idmedico }}" @selected(request('medico') == $m->idmedico)>{{ $m->nombre_completo }}</option>@endforeach
    </select>
    @endif
    <button class="boton boton-borde">Ver</button>
</form>

<div class="panel">
    <table class="tabla-datos">
        <thead><tr><th>Hora</th><th>Paciente</th>@unless($esMedico)<th>Médico</th>@endunless<th>Especialidad</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @forelse($citas as $c)
            <tr>
                <td class="mono">{{ $c->horario }}</td>
                <td><a href="{{ route('citas.show', $c) }}"><strong>{{ $c->paciente->nombre_completo }}</strong></a><br><small class="texto-suave">CI {{ $c->paciente->ci }}</small></td>
                @unless($esMedico)<td>{{ $c->medico->nombre_completo }}</td>@endunless
                <td>{{ $c->especialidad->nombre }}</td>
                <td>@include('compartido.partials.estado-cita', ['estado' => $c->codigoestado])</td>
                <td class="celda-acciones">
                    @if($esMedico && ($c->estaPendiente() || $c->codigoestado === 'EN_ATENCION'))
                        <form method="POST" action="{{ route('asistencia.atender', $c) }}">@csrf<button class="boton boton-principal boton-chico">{{ $c->codigoestado === 'EN_ATENCION' ? 'Continuar consulta' : 'Asistió' }}</button></form>
                    @endif
                    @if($c->estaPendiente())
                        <form method="POST" action="{{ route('asistencia.no-asistio', $c) }}" data-confirmacion="¿Registrar que {{ $c->paciente->nombre_completo }} no asistió?">@csrf<button class="boton boton-plano boton-chico">No asistió</button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="sin-datos">No hay citas para esta fecha.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection

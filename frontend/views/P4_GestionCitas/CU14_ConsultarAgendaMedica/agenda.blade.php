@extends('compartido.layouts.app')
@section('titulo', 'Agenda médica')
@section('migas', auth()->user()->tieneRol('MEDICO') ? 'Mi consulta · Agenda' : 'Atención · Agenda médica')

@section('contenido')
@php($esMedico = auth()->user()->tieneRol('MEDICO'))
@php($atendidas = $citas->where('codigoestado', 'ATENDIDA')->count())
@php($pendientes = $citas->filter(fn ($c) => $c->estaPendiente())->count())
<div class="encabezado">
    <div><h1>{{ $esMedico ? 'Mi agenda' : 'Agenda médica' }}</h1>
        <p class="texto-suave">{{ $medico?->nombre_completo }} · {{ ucfirst($fecha->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY')) }}</p></div>
    <div class="grupo-botones">
        <a class="boton boton-plano boton-chico" href="{{ route('agenda.index', ['fecha' => $fecha->copy()->subDay()->toDateString(), 'medico' => $medico?->idmedico]) }}">← Día anterior</a>
        <a class="boton boton-plano boton-chico" href="{{ route('agenda.index', ['medico' => $medico?->idmedico]) }}">Hoy</a>
        <a class="boton boton-plano boton-chico" href="{{ route('agenda.index', ['fecha' => $fecha->copy()->addDay()->toDateString(), 'medico' => $medico?->idmedico]) }}">Día siguiente →</a>
    </div>
</div>

<form class="barra-filtros" method="GET">
    <input type="date" name="fecha" value="{{ $fecha->toDateString() }}" aria-label="Fecha">
    @if($medicos->isNotEmpty())
    <select name="medico">
        @foreach($medicos as $m)<option value="{{ $m->idmedico }}" @selected($medico?->idmedico === $m->idmedico)>{{ $m->nombre_completo }}</option>@endforeach
    </select>
    @endif
    <button class="boton boton-borde">Ver agenda</button>
</form>

<div class="indicadores">
    <div class="indicador"><span>Citas del día</span><strong>{{ $citas->count() }}</strong></div>
    <div class="indicador"><span>Pendientes</span><strong>{{ $pendientes }}</strong></div>
    <div class="indicador"><span>Atendidas</span><strong>{{ $atendidas }}</strong></div>
    <div class="indicador"><span>Horario</span><strong style="font-size:1rem">
        @forelse($horarios as $h){{ substr($h->horainicio,0,5) }}–{{ substr($h->horafin,0,5) }}@if(! $loop->last), @endif @empty No atiende @endforelse</strong></div>
</div>

@foreach($bloqueos as $b)
    <div class="aviso aviso-advertencia">Agenda bloqueada {{ $b->horainicio ? substr($b->horainicio,0,5) . ' – ' . substr($b->horafin,0,5) : 'todo el día' }}
        ({{ \App\Models\BloqueoAgenda::TIPOS[$b->tipo] ?? $b->tipo }}{{ $b->motivo ? ': ' . $b->motivo : '' }})</div>
@endforeach

<div class="panel">
    <table class="tabla-datos">
        <thead><tr><th>Horario</th><th>Paciente</th><th>Especialidad</th><th>Motivo</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @forelse($citas as $c)
            <tr>
                <td class="mono">{{ $c->horario }}</td>
                <td><strong>{{ $c->paciente->nombre_completo }}</strong><br><small class="texto-suave">{{ $c->paciente->edad }} años · {{ \App\Models\Cita::MODALIDADES[$c->modalidad] ?? $c->modalidad }}</small></td>
                <td>{{ $c->especialidad->nombre }}</td>
                <td>{{ $c->motivo ?? '—' }}</td>
                <td>@include('compartido.partials.estado-cita', ['estado' => $c->codigoestado])</td>
                <td class="celda-acciones">
                    @if($esMedico && ($c->estaPendiente() || $c->codigoestado === 'EN_ATENCION') && ! $c->fecha->isFuture())
                        <form method="POST" action="{{ route('asistencia.atender', $c) }}">@csrf<button class="boton boton-principal boton-chico">{{ $c->codigoestado === 'EN_ATENCION' ? 'Continuar' : 'Atender' }}</button></form>
                    @elseif($esMedico && $c->consulta)
                        <a href="{{ route('consultas.show', $c->consulta) }}" class="boton boton-plano boton-chico">Ver consulta</a>
                    @endif
                    @if($esMedico)
                        <a href="{{ route('historia.show', $c->paciente) }}" class="boton boton-plano boton-chico">Historia</a>
                    @else
                        <a href="{{ route('citas.show', $c) }}" class="boton boton-plano boton-chico">Ver</a>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="sin-datos">Sin citas para este día.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@if($esMedico)<p class="nota">¿No atenderá algún día? <a href="{{ route('agenda.bloqueos') }}">Bloquee su agenda</a>.</p>@endif
@endsection

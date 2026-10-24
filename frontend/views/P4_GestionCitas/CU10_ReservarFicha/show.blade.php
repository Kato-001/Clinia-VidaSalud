@extends('compartido.layouts.app')
@section('titulo', 'Cita ' . $cita->codigo)
@section('migas', 'Citas · ' . $cita->codigo)

@section('contenido')
@php($u = auth()->user())
@php($pendiente = $cita->estaPendiente())
<div class="encabezado">
    <div><h1>Cita médica {{ $cita->codigo }}</h1><p class="texto-suave">Reservada el {{ $cita->historial->first()?->fecha?->format('d/m/Y') ?? '—' }}</p></div>
    @include('compartido.partials.estado-cita', ['estado' => $cita->codigoestado])
</div>
<div class="comprobante panel">
    <div class="comprobante-cabecera">
        <div><small>Fecha</small><strong>{{ ucfirst($cita->fecha->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY')) }}</strong></div>
        <div><small>Horario</small><strong class="mono">{{ $cita->horario }}</strong></div>
    </div>
    <dl class="ficha-datos">
        <dt>Paciente</dt><dd>{{ $cita->paciente->nombre_completo }} <span class="texto-suave">({{ $cita->paciente->codigo }} · CI {{ $cita->paciente->ci }})</span></dd>
        <dt>Especialidad</dt><dd>{{ $cita->especialidad->nombre }}</dd>
        <dt>Modalidad</dt><dd>{{ \App\Models\Cita::MODALIDADES[$cita->modalidad] ?? $cita->modalidad }}</dd>
        <dt>Médico</dt><dd>{{ $cita->medico->nombre_completo }}</dd>
        <dt>Motivo</dt><dd>{{ $cita->motivo ?? '—' }}</dd>
    </dl>
</div>

<div class="grupo-botones">
    <a href="{{ route('citas.index') }}" class="boton boton-plano">Volver a {{ $u->tieneRol('PACIENTE') ? 'mis citas' : 'citas' }}</a>
    @if($pendiente && in_array($cita->codigoestado, ['PROGRAMADA', 'REPROGRAMADA']) && ! $u->tieneRol('MEDICO'))
        <form method="POST" action="{{ route('citas.confirmar', $cita) }}">@csrf<button class="boton boton-principal">Confirmar asistencia</button></form>
    @endif
    @if($pendiente && ! $u->tieneRol('MEDICO'))
        <a href="{{ route('citas.reprogramar', $cita) }}" class="boton boton-borde">Reprogramar</a>
    @endif
    @if($pendiente)
        <a href="{{ route('citas.cancelar', $cita) }}" class="boton boton-borde">Cancelar cita</a>
    @endif
    @if($u->tieneRol('MEDICO') && ($pendiente || $cita->codigoestado === 'EN_ATENCION') && ! $cita->fecha->isFuture())
        <form method="POST" action="{{ route('asistencia.atender', $cita) }}">@csrf<button class="boton boton-principal">{{ $cita->codigoestado === 'EN_ATENCION' ? 'Continuar consulta' : 'Asistió: atender' }}</button></form>
    @endif
    @if($u->tieneRol('MEDICO') && $cita->consulta && $cita->codigoestado === 'ATENDIDA')
        <a href="{{ route('consultas.show', $cita->consulta) }}" class="boton boton-borde">Ver consulta</a>
    @endif
    @if($u->esPersonal())
        <a href="{{ route('pacientes.show', $cita->paciente) }}" class="boton boton-borde">Ver paciente</a>
    @endif
</div>

<div class="panel" style="margin-top:1.25rem">
    <h2>Historial de la cita</h2>
    <table class="tabla-datos">
        <thead><tr><th>Fecha</th><th>Acción</th><th>Estado</th><th>Horario anterior</th><th>Horario nuevo</th><th>Motivo</th></tr></thead>
        <tbody>
        @foreach($cita->historial as $h)
            <tr>
                <td>{{ $h->fecha->format('d/m/Y') }}</td>
                <td>{{ ucfirst(mb_strtolower(str_replace('_', ' ', $h->accion))) }}</td>
                <td>@include('compartido.partials.estado-cita', ['estado' => $h->codigoestado])</td>
                <td class="mono">{{ $h->fechaanterior ? $h->fechaanterior->format('d/m/Y') . ' ' . substr($h->horaanterior, 0, 5) : '—' }}</td>
                <td class="mono">{{ $h->fechanueva ? $h->fechanueva->format('d/m/Y') . ' ' . substr($h->horanueva, 0, 5) : '—' }}</td>
                <td>{{ $h->motivo ?? '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection

@extends('compartido.layouts.app')
@section('titulo', 'Horarios')
@section('migas', 'Atención · Médicos · ' . $medico->codigo . ' · Horarios')

@section('contenido')
<div class="encabezado">
    <div><h1>Horarios de atención</h1><p class="texto-suave">{{ $medico->nombre_completo }} · {{ $medico->especialidades->pluck('nombre')->join(', ') }}</p></div>
    <a href="{{ route('medicos.show', $medico) }}" class="boton boton-plano">Volver al médico</a>
</div>
<div class="columnas-2 columnas-formulario">
    <form class="panel formulario" method="POST" action="{{ route('horarios.store', $medico) }}" data-validacion novalidate>
        @csrf
        <h2>Nuevo bloque</h2>
        <label class="campo"><span>Día *</span>
            <select name="diasemana" required>
                <option value="">Seleccione…</option>
                @foreach($dias as $n => $d)<option value="{{ $n }}" @selected(old('diasemana') == $n)>{{ $d }}</option>@endforeach
            </select></label>
        <div class="columnas-2">
            <label class="campo"><span>Hora inicial *</span><input type="time" name="horainicio" value="{{ old('horainicio', '08:00') }}" required></label>
            <label class="campo"><span>Hora final *</span><input type="time" name="horafin" value="{{ old('horafin', '12:00') }}" required data-mayor-que="horainicio"></label>
        </div>
        <p class="nota">Cada ficha dura <strong>{{ $duracion }} minutos</strong> por paciente; el sistema calcula los cupos automáticamente.</p>
        <div class="acciones-formulario"><button class="boton boton-principal">Agregar horario</button></div>
    </form>

    <div class="panel">
        <h2>Semana</h2>
        <table class="tabla-datos">
            <thead><tr><th>Día</th><th>Horario</th><th>Cupos</th><th>Estado</th><th></th></tr></thead>
            <tbody>
            @forelse($medico->horarios as $h)
                @php($cupos = intdiv(intdiv(strtotime($h->horafin) - strtotime($h->horainicio), 60), $duracion))
                <tr class="{{ $h->estado === 'ACTIVO' ? '' : 'atenuado' }}">
                    <td><strong>{{ $h->dia }}</strong></td>
                    <td class="mono">{{ substr($h->horainicio,0,5) }} – {{ substr($h->horafin,0,5) }}</td>
                    <td>{{ $cupos }} fichas</td>
                    <td>@include('compartido.partials.estado', ['estado' => $h->estado])</td>
                    <td class="celda-acciones">
                        <form method="POST" action="{{ route('horarios.estado', [$medico, $h]) }}">@csrf @method('PATCH')<button class="boton boton-plano boton-chico">{{ $h->estado === 'ACTIVO' ? 'Desactivar' : 'Activar' }}</button></form>
                        <form method="POST" action="{{ route('horarios.destroy', [$medico, $h]) }}" data-confirmacion="¿Eliminar el bloque del {{ $h->dia }}?">@csrf @method('DELETE')<button class="boton boton-plano boton-chico">Quitar</button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="sin-datos">Sin horarios. Agregue el primer bloque.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

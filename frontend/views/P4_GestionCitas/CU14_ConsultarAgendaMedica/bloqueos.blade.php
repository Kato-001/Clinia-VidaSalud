@extends('compartido.layouts.app')
@section('titulo', 'Bloquear agenda')
@section('migas', 'Mi consulta · Bloquear agenda')

@section('contenido')
<div class="encabezado"><div><h1>Bloquear agenda</h1><p class="texto-suave">Indique los días u horas en que no atenderá (vacaciones, permisos, capacitaciones).</p></div></div>

<div class="columnas-2 columnas-formulario">
    <form class="panel formulario" method="POST" action="{{ route('agenda.bloquear') }}" data-validacion novalidate>
        @csrf
        <h2>Nuevo bloqueo</h2>
        <label class="campo"><span>Fecha *</span>
            <input type="date" name="fecha" value="{{ old('fecha', today()->toDateString()) }}" required min="{{ today()->toDateString() }}"></label>
        <label class="casilla"><input type="checkbox" name="diacompleto" value="1" id="diacompleto" @checked(old('diacompleto', true))> Bloquear el día completo</label>
        <div class="columnas-2" id="horas-bloqueo">
            <label class="campo"><span>Desde</span><input type="time" name="horainicio" value="{{ old('horainicio') }}"></label>
            <label class="campo"><span>Hasta</span><input type="time" name="horafin" value="{{ old('horafin') }}" data-mayor-que="horainicio"></label>
        </div>
        <label class="campo"><span>Tipo *</span>
            <select name="tipo" required>
                <option value="">Seleccione…</option>
                @foreach($tipos as $k => $v)<option value="{{ $k }}" @selected(old('tipo') === $k)>{{ $v }}</option>@endforeach
            </select></label>
        <label class="campo"><span>Motivo</span>
            <input name="motivo" value="{{ old('motivo') }}" maxlength="255"></label>
        <p class="nota">No se puede bloquear un horario que ya tiene citas pendientes: reprográmelas o cancélelas primero.</p>
        <div class="acciones-formulario"><button class="boton boton-peligro">Bloquear</button></div>
    </form>

    <div class="panel">
        <h2>Bloqueos vigentes</h2>
        <table class="tabla-datos">
            <thead><tr><th>Fecha</th><th>Horario</th><th>Tipo</th><th>Motivo</th><th></th></tr></thead>
            <tbody>
            @forelse($bloqueos as $b)
                <tr>
                    <td>{{ ucfirst($b->fecha->locale('es')->isoFormat('ddd D/MM/YYYY')) }}</td>
                    <td class="mono">{{ $b->esDiaCompleto() ? 'Día completo' : substr($b->horainicio,0,5) . ' – ' . substr($b->horafin,0,5) }}</td>
                    <td>{{ $tipos[$b->tipo] ?? $b->tipo }}</td>
                    <td>{{ $b->motivo ?? '—' }}</td>
                    <td class="celda-acciones">
                        <form method="POST" action="{{ route('agenda.liberar', $b) }}" data-confirmacion="¿Liberar el bloqueo? Esos horarios volverán a estar disponibles.">
                            @csrf @method('DELETE')<button class="boton boton-plano boton-chico">Liberar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="sin-datos">No tiene bloqueos programados.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Muestra las horas solo si no es día completo
    (function () {
        const check = document.getElementById('diacompleto'), horas = document.getElementById('horas-bloqueo');
        const actualizar = () => { horas.hidden = check.checked; horas.querySelectorAll('input').forEach(i => { i.required = !check.checked; }); };
        check.addEventListener('change', actualizar);
        actualizar();
    })();
</script>
@endpush

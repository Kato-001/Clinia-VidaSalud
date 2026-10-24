@extends('compartido.layouts.app')
@section('titulo', 'Reportes')
@section('migas', 'Reportes · Citas')

@section('contenido')
<div class="encabezado">
    <div><h1>Reporte de citas</h1><p class="texto-suave">Del {{ \Illuminate\Support\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Illuminate\Support\Carbon::parse($hasta)->format('d/m/Y') }} · {{ $total }} cita(s)</p></div>
    <a href="{{ route('reportes.exportar', ['desde' => $desde, 'hasta' => $hasta]) }}" class="boton boton-borde">Exportar a Excel (CSV)</a>
</div>

<form class="barra-filtros" method="GET">
    <label class="campo"><span>Desde</span><input type="date" name="desde" value="{{ $desde }}"></label>
    <label class="campo"><span>Hasta</span><input type="date" name="hasta" value="{{ $hasta }}"></label>
    <button class="boton boton-principal" style="align-self:flex-end;margin-bottom:1rem">Generar</button>
</form>

@php($maxEstado = max(1, $porEstado->max('total') ?? 1))
@php($maxEsp = max(1, $porEspecialidad->max('total') ?? 1))
<div class="columnas-2">
    <div class="panel">
        <h2>Citas por estado</h2>
        @forelse($porEstado as $f)
            <p style="margin:.4rem 0 .2rem">{{ $f->nombre }} <strong style="float:right">{{ $f->total }}</strong></p>
            <div class="barra-progreso"><span style="width: {{ $f->total / $maxEstado * 100 }}%"></span></div>
        @empty
            <p class="sin-datos">Sin citas en el rango.</p>
        @endforelse
    </div>
    <div class="panel">
        <h2>Citas por especialidad</h2>
        @forelse($porEspecialidad as $f)
            <p style="margin:.4rem 0 .2rem">{{ $f->nombre }} <strong style="float:right">{{ $f->total }}</strong></p>
            <div class="barra-progreso"><span style="width: {{ $f->total / $maxEsp * 100 }}%"></span></div>
        @empty
            <p class="sin-datos">Sin citas en el rango.</p>
        @endforelse
    </div>
</div>

<div class="panel">
    <h2>Productividad por médico</h2>
    <table class="tabla-datos">
        <thead><tr><th>Médico</th><th>Citas</th><th>Atendidas</th><th>No asistió</th><th>% atención</th></tr></thead>
        <tbody>
        @forelse($porMedico as $m)
            <tr>
                <td><strong>{{ $m->nombre }}</strong></td>
                <td>{{ $m->total }}</td>
                <td>{{ $m->atendidas }}</td>
                <td>{{ $m->inasistencias }}</td>
                <td>{{ $m->total ? round($m->atendidas / $m->total * 100) : 0 }} %</td>
            </tr>
        @empty
            <tr><td colspan="5" class="sin-datos">Sin citas en el rango.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection

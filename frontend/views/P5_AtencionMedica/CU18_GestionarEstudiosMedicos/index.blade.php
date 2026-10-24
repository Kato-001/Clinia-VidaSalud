@extends('compartido.layouts.app')
@section('titulo', 'Estudios')
@section('migas', 'Mi consulta · Estudios')

@section('contenido')
<div class="encabezado"><div><h1>Estudios solicitados</h1><p class="texto-suave">Registre los resultados de los estudios de sus pacientes.</p></div></div>

<nav class="pestanas">
    <a href="{{ route('estudios.index', ['ver' => 'pendientes']) }}" class="{{ $pendientes ? 'actual' : '' }}">Pendientes</a>
    <a href="{{ route('estudios.index', ['ver' => 'todos']) }}" class="{{ $pendientes ? '' : 'actual' }}">Todos</a>
</nav>

<div class="panel">
    <table class="tabla-datos">
        <thead><tr><th>Paciente</th><th>Estudio</th><th>Solicitado</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @forelse($estudios as $e)
            <tr>
                <td><strong>{{ $e->consulta->cita->paciente->nombre_completo }}</strong><br><small class="texto-suave">Cita {{ $e->consulta->cita->codigo }}</small></td>
                <td>{{ $e->catalogo->nombre }}@if($e->indicaciones)<br><small class="texto-suave">{{ $e->indicaciones }}</small>@endif</td>
                <td>{{ $e->fechasolicitud->format('d/m/Y H:i') }}</td>
                <td>@include('compartido.partials.estado-cita', ['estado' => $e->estado])</td>
                <td class="celda-acciones">
                    <a href="{{ route('estudios.resultado', $e) }}" class="boton {{ $e->estado === 'SOLICITADO' ? 'boton-principal' : 'boton-plano' }} boton-chico">{{ $e->estado === 'SOLICITADO' ? 'Registrar resultado' : 'Ver / editar' }}</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="sin-datos">{{ $pendientes ? 'No hay estudios pendientes.' : 'Aún no solicitó estudios.' }}</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $estudios->links('compartido.partials.paginacion') }}
</div>
@endsection

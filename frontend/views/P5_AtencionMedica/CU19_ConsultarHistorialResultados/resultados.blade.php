@extends('compartido.layouts.app')
@section('titulo', 'Resultados')
@section('migas', 'Mi salud · Resultados')

@section('contenido')
<div class="encabezado"><div><h1>Resultados de estudios</h1><p class="texto-suave">Estudios solicitados por sus médicos y sus resultados.</p></div></div>

@forelse($estudios as $e)
    <div class="panel">
        <div class="panel-cabecera">
            <h2>{{ $e->catalogo->nombre }}</h2>
            @include('compartido.partials.estado-cita', ['estado' => $e->estado])
        </div>
        <p class="texto-suave">{{ $e->catalogo->categoria }} · Solicitado por {{ $e->consulta->cita->medico->nombre_completo }} el {{ $e->fechasolicitud->format('d/m/Y') }}</p>
        @if($e->indicaciones)<p>Indicaciones: {{ $e->indicaciones }}</p>@endif
        @if($e->estado === 'COMPLETADO')
            <div class="aviso aviso-exito">
                <strong>Resultado ({{ $e->fecharesultado?->format('d/m/Y H:i') }}):</strong><br>{!! nl2br(e($e->resultado)) !!}
                @if($e->archivoresultado)<br><a href="{{ $e->archivoresultado }}" target="_blank" rel="noopener noreferrer">Ver archivo adjunto ↗</a>@endif
            </div>
        @else
            <p class="nota">El resultado aún no está disponible. Le avisaremos por notificación.</p>
        @endif
    </div>
@empty
    <div class="panel"><p class="sin-datos">No tiene estudios solicitados.</p></div>
@endforelse
@endsection

@extends('compartido.layouts.app')
@section('titulo', 'Historial clínico')
@section('migas', 'Mi salud · Historial clínico')

@section('contenido')
@php($historia = $paciente->historiaClinica)
<div class="encabezado"><div><h1>Mi historial clínico</h1><p class="texto-suave">Historia {{ $historia?->numero ?? '—' }} · Sus consultas, diagnósticos y tratamientos.</p></div></div>

<div class="columnas-2 columnas-formulario" style="grid-template-columns: 340px 1fr">
    <div class="panel">
        <h2>Antecedentes</h2>
        <dl class="ficha-datos" style="grid-template-columns: 1fr">
            @foreach($antecedentes as $campo => $etiqueta)
                <dt>{{ $etiqueta }}</dt><dd>{{ $historia?->$campo ?: 'Sin registrar' }}</dd>
            @endforeach
        </dl>
    </div>
    <div>
        @forelse($consultas as $c)
            @include('P5_AtencionMedica.CU19_ConsultarHistorialResultados._consulta', ['c' => $c])
        @empty
            <div class="panel"><p class="sin-datos">Aquí aparecerán sus consultas cuando un médico lo atienda.</p></div>
        @endforelse
    </div>
</div>
@endsection

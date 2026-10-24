@extends('compartido.layouts.app')
@section('titulo', 'Historia clínica')
@section('migas', 'Mi consulta · Historia clínica')

@section('contenido')
@php($historia = $paciente->historiaClinica)
<div class="encabezado">
    <div><h1>Historia clínica {{ $historia?->numero }}</h1>
        <p class="texto-suave">{{ $paciente->nombre_completo }} · {{ $paciente->edad }} años · CI {{ $paciente->ci }} · Sangre {{ $paciente->tiposangre ?? '—' }}</p></div>
    <a href="{{ url()->previous() }}" class="boton boton-plano">Volver</a>
</div>

<div class="columnas-2 columnas-formulario" style="grid-template-columns: 380px 1fr">
    {{-- HISTORIACLINICA.actualizarAntecedentes() --}}
    <form class="panel formulario" method="POST" action="{{ route('historia.update', $paciente) }}" data-validacion novalidate>
        @csrf @method('PUT')
        <h2>Antecedentes</h2>
        @foreach($antecedentes as $campo => $etiqueta)
            <label class="campo"><span>{{ $etiqueta }}</span>
                <textarea name="{{ $campo }}" rows="2" maxlength="5000">{{ old($campo, $historia?->$campo) }}</textarea></label>
        @endforeach
        <div class="acciones-formulario"><button class="boton boton-principal">Guardar antecedentes</button></div>
    </form>

    <div>
        <h2>Consultas ({{ $consultas->count() }})</h2>
        @forelse($consultas as $c)
            @include('P5_AtencionMedica.CU19_ConsultarHistorialResultados._consulta', ['c' => $c])
        @empty
            <div class="panel"><p class="sin-datos">El paciente aún no tiene consultas registradas.</p></div>
        @endforelse
    </div>
</div>
@endsection

@extends('compartido.layouts.app')
@section('titulo', 'Consulta ' . $cita->codigo)
@section('migas', 'Mi consulta · Agenda · ' . $cita->codigo)

@section('contenido')
@php($historia = $paciente->historiaClinica)
<div class="encabezado">
    <div><h1>{{ $paciente->nombre_completo }}</h1>
        <p class="texto-suave">{{ $paciente->edad }} años · CI {{ $paciente->ci }} · Sangre {{ $paciente->tiposangre ?? '—' }} · Cita {{ $cita->codigo }} ({{ $cita->especialidad->nombre }}, {{ $cita->horario }})</p></div>
    <div class="grupo-botones">
        @include('compartido.partials.estado-cita', ['estado' => $cita->codigoestado])
        <a href="{{ route('historia.show', $paciente) }}" class="boton boton-borde">Historia clínica</a>
        @if($editable)
            <form method="POST" action="{{ route('consultas.cerrar', $consulta) }}" data-confirmacion="Guarde antes los cambios. Al finalizar, la consulta ya no podrá editarse. ¿Finalizar?">
                @csrf<button class="boton boton-principal">Finalizar consulta</button>
            </form>
        @endif
    </div>
</div>

@if($historia?->alergias)
    <div class="aviso aviso-error"><strong>Alergias:</strong> {{ $historia->alergias }}</div>
@endif
@unless($editable)
    <div class="aviso aviso-exito">Esta consulta ya fue finalizada. Se muestra solo para lectura.</div>
@endunless

<div class="columnas-2 columnas-formulario" style="grid-template-columns: 1fr 340px">
    <div>
        {{-- CU15: signos vitales, examen y diagnóstico (CONSULTA.registrarSignos / diagnosticar) --}}
        <form class="panel formulario" method="POST" action="{{ route('consultas.update', $consulta) }}" data-validacion novalidate>
            @csrf @method('PUT')
            <fieldset @disabled(! $editable) style="border:0;padding:0;margin:0">
                <h2>Signos vitales</h2>
                <div class="signos">
                    <label class="campo"><span>Peso (kg)</span><input type="number" step="0.1" min="0.5" max="500" name="peso" value="{{ old('peso', $consulta->peso) }}"></label>
                    <label class="campo"><span>Talla (cm)</span><input type="number" step="0.1" min="20" max="300" name="talla" value="{{ old('talla', $consulta->talla) }}"></label>
                    <label class="campo"><span>Presión (mmHg)</span><input name="presionarterial" value="{{ old('presionarterial', $consulta->presionarterial) }}" placeholder="120/80" pattern="\d{2,3}/\d{2,3}" data-mensaje="Use el formato 120/80."></label>
                    <label class="campo"><span>Temp. (°C)</span><input type="number" step="0.1" min="30" max="45" name="temperatura" value="{{ old('temperatura', $consulta->temperatura) }}"></label>
                    <label class="campo"><span>F. cardiaca (lpm)</span><input type="number" step="1" min="20" max="250" name="frecuenciacardiaca" value="{{ old('frecuenciacardiaca', $consulta->frecuenciacardiaca) }}"></label>
                </div>
                <h2>Consulta</h2>
                <label class="campo"><span>Motivo de consulta</span><input name="motivo" maxlength="255" value="{{ old('motivo', $consulta->motivo) }}"></label>
                <label class="campo"><span>Examen físico</span><textarea name="examenfisico" rows="3" maxlength="5000">{{ old('examenfisico', $consulta->examenfisico) }}</textarea></label>
                <label class="campo"><span>Diagnóstico (obligatorio para finalizar)</span><textarea name="diagnostico" rows="3" maxlength="5000">{{ old('diagnostico', $consulta->diagnostico) }}</textarea></label>
                <label class="campo"><span>Observaciones</span><textarea name="observaciones" rows="2" maxlength="5000">{{ old('observaciones', $consulta->observaciones) }}</textarea></label>
            </fieldset>
            @if($editable)<div class="acciones-formulario"><button class="boton boton-principal">Guardar consulta</button></div>@endif
        </form>

        <div class="columnas-2">
            @include('P5_AtencionMedica.CU17_RegistrarTratamiento._receta')
            @include('P5_AtencionMedica.CU18_GestionarEstudiosMedicos._solicitud')
        </div>
    </div>

    <aside>
        <div class="panel">
            <h2>Antecedentes</h2>
            <dl class="ficha-datos" style="grid-template-columns: 1fr">
                @foreach(\App\Models\HistoriaClinica::ANTECEDENTES as $campo => $etiqueta)
                    <dt>{{ $etiqueta }}</dt><dd>{{ $historia?->$campo ?: 'Sin registrar' }}</dd>
                @endforeach
            </dl>
        </div>
        <div class="panel">
            <h2>Consultas anteriores</h2>
            @forelse($anteriores as $a)
                <p><strong>{{ $a->fechahora->format('d/m/Y') }}</strong> · {{ $a->cita->especialidad->nombre }}<br>
                    <span class="texto-suave">{{ $a->diagnostico ?? 'Sin diagnóstico' }} · {{ $a->cita->medico->nombre_completo }}</span></p>
            @empty
                <p class="texto-suave">Primera consulta del paciente.</p>
            @endforelse
        </div>
        @if($paciente->contactos->isNotEmpty())
            <div class="panel">
                <h2>Emergencia</h2>
                @foreach($paciente->contactos as $c)<p>{{ $c->nombre }} ({{ $c->parentesco }})<br><span class="mono">{{ $c->telefono }}</span></p>@endforeach
            </div>
        @endif
    </aside>
</div>
@endsection

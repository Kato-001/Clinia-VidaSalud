{{-- Ficha de una consulta: signos, diagnóstico, tratamientos y estudios (CU16 y CU19) --}}
<div class="panel">
    <div class="panel-cabecera">
        <h2>{{ $c->cita->especialidad->nombre }} · {{ $c->cita->medico->nombre_completo }}</h2>
        @include('compartido.partials.estado-cita', ['estado' => $c->cita->codigoestado])
    </div>
    <p class="texto-suave">{{ ucfirst($c->fechahora->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY')) }} · Cita {{ $c->cita->codigo }}</p>
    <p>
        @foreach(['peso' => ['Peso', 'kg'], 'talla' => ['Talla', 'cm'], 'presionarterial' => ['Presión', 'mmHg'], 'temperatura' => ['Temperatura', '°C'], 'frecuenciacardiaca' => ['F. cardiaca', 'lpm']] as $campo => [$nombre, $unidad])
            @if(! is_null($c->$campo))<span class="signo">{{ $nombre }}: <strong>{{ $c->$campo }}</strong> {{ $unidad }}</span>@endif
        @endforeach
    </p>
    <dl class="ficha-datos">
        <dt>Motivo</dt><dd>{{ $c->motivo ?? '—' }}</dd>
        <dt>Diagnóstico</dt><dd>{{ $c->diagnostico ?? 'En proceso' }}</dd>
        @if($c->examenfisico)<dt>Examen físico</dt><dd>{{ $c->examenfisico }}</dd>@endif
        @if($c->observaciones)<dt>Observaciones</dt><dd>{{ $c->observaciones }}</dd>@endif
        @if($c->tratamientos->isNotEmpty())
            <dt>Tratamiento</dt>
            <dd>@foreach($c->tratamientos as $t)<div><strong>{{ $t->medicamento->nombre }}</strong> — {{ $t->dosis }}, {{ $t->frecuencia }}, {{ $t->duracion }}@if($t->indicaciones) <span class="texto-suave">({{ $t->indicaciones }})</span>@endif</div>@endforeach</dd>
        @endif
        @if($c->estudios->isNotEmpty())
            <dt>Estudios</dt>
            <dd>@foreach($c->estudios as $e)<span class="pastilla">{{ $e->catalogo->nombre }} · {{ $e->estado === 'COMPLETADO' ? 'resultado listo' : 'pendiente' }}</span>@endforeach</dd>
        @endif
    </dl>
</div>

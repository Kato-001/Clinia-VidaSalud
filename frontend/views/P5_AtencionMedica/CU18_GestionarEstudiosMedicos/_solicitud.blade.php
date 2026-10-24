{{-- CU18 Gestionar estudios médicos: solicitud dentro de la consulta (MEDICO.solicitarEstudio) --}}
<div class="panel">
    <h2>Estudios solicitados</h2>
    @forelse($consulta->estudios as $e)
        <div class="elemento">
            <div><strong>{{ $e->catalogo->nombre }}</strong> <span class="texto-suave">{{ $e->catalogo->categoria }}</span>
                @if($e->indicaciones)<small>{{ $e->indicaciones }}</small>@endif</div>
            @include('compartido.partials.estado-cita', ['estado' => $e->estado])
        </div>
    @empty
        <p class="texto-suave">Sin estudios solicitados.</p>
    @endforelse

    @if($editable)
        <form method="POST" action="{{ route('estudios.store', $consulta) }}" class="formulario" data-validacion novalidate>
            @csrf
            <label class="campo"><span>Estudio *</span>
                <select name="idcatalogo" required>
                    <option value="">Seleccione…</option>
                    @foreach($catalogo->groupBy('categoria') as $categoria => $estudios)
                        <optgroup label="{{ $categoria ?: 'Otros' }}">
                            @foreach($estudios as $c)<option value="{{ $c->idcatalogo }}">{{ $c->nombre }}</option>@endforeach
                        </optgroup>
                    @endforeach
                </select></label>
            <label class="campo"><span>Indicaciones</span><input name="indicaciones" maxlength="2000" placeholder="Ej. En ayunas de 8 horas"></label>
            <div class="acciones-formulario"><button class="boton boton-borde">Solicitar estudio</button></div>
        </form>
    @endif
</div>

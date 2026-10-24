{{-- CU17 Registrar tratamiento: receta dentro de la consulta (MEDICO.recetarTratamiento) --}}
<div class="panel">
    <h2>Receta / tratamiento</h2>
    @forelse($consulta->tratamientos as $t)
        <div class="elemento">
            <div><strong>{{ $t->medicamento->nombre }}</strong> <span class="texto-suave">{{ $t->medicamento->presentacion }}</span>
                <small>{{ $t->dosis }} · {{ $t->frecuencia }} · {{ $t->duracion }}</small>
                @if($t->indicaciones)<small>{{ $t->indicaciones }}</small>@endif</div>
            @if($editable)
                <form method="POST" action="{{ route('tratamientos.destroy', $t) }}" data-confirmacion="¿Quitar {{ $t->medicamento->nombre }} de la receta?">
                    @csrf @method('DELETE')<button class="boton boton-plano boton-chico">Quitar</button>
                </form>
            @endif
        </div>
    @empty
        <p class="texto-suave">Sin medicamentos recetados.</p>
    @endforelse

    @if($editable)
        <form method="POST" action="{{ route('tratamientos.store', $consulta) }}" class="formulario" data-validacion novalidate>
            @csrf
            <label class="campo"><span>Medicamento *</span>
                <select name="idmedicamento" required>
                    <option value="">Seleccione…</option>
                    @foreach($medicamentos as $m)<option value="{{ $m->idmedicamento }}">{{ $m->nombre }}{{ $m->presentacion ? ' — ' . $m->presentacion : '' }}</option>@endforeach
                </select></label>
            <div class="columnas-3">
                <label class="campo"><span>Dosis *</span><input name="dosis" required maxlength="100" placeholder="500 mg"></label>
                <label class="campo"><span>Frecuencia *</span><input name="frecuencia" required maxlength="100" placeholder="Cada 8 h"></label>
                <label class="campo"><span>Duración *</span><input name="duracion" required maxlength="100" placeholder="5 días"></label>
            </div>
            <label class="campo"><span>Indicaciones</span><input name="indicaciones" maxlength="2000" placeholder="Después de las comidas"></label>
            <div class="acciones-formulario"><button class="boton boton-borde">Agregar a la receta</button></div>
        </form>
    @endif
</div>

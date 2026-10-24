{{-- Estado de una cita (ESTADOCITA.codigo) o de un estudio --}}
@php($nombres = [
    'PROGRAMADA' => 'Programada', 'CONFIRMADA' => 'Confirmada', 'REPROGRAMADA' => 'Reprogramada', 'EN_ATENCION' => 'En atención',
    'ATENDIDA' => 'Atendida', 'CANCELADA' => 'Cancelada', 'NO_ASISTIO' => 'No asistió',
    'SOLICITADO' => 'Pendiente', 'COMPLETADO' => 'Resultado listo', 'ANULADO' => 'Anulado',
])
<span class="etiqueta etiqueta-{{ strtolower($estado) }}">{{ $nombres[$estado] ?? ucfirst(strtolower($estado)) }}</span>

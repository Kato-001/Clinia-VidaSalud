{{-- Estado ACTIVO / INACTIVO --}}
@php($activo = ($estado ?? '') === 'ACTIVO')
<span class="etiqueta {{ $activo ? 'etiqueta-activo' : 'etiqueta-inactivo' }}">{{ $activo ? 'Activo' : 'Inactivo' }}</span>

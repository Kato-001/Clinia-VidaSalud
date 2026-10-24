@extends('compartido.layouts.acceso')
@section('titulo', 'Ocurrió un problema')

@section('contenido')
<div class="acceso-tarjeta error-panel">
    <div class="error-codigo">500</div>
    <h1>Ocurrió un problema</h1>
    <p class="texto-suave">Estamos trabajando para solucionarlo. Intente nuevamente en unos minutos.</p>
    <a href="{{ url('/') }}" class="boton boton-principal">Volver al inicio</a>
</div>
@endsection

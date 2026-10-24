@extends('compartido.layouts.acceso')
@section('titulo', 'La sesión expiró')

@section('contenido')
<div class="acceso-tarjeta error-panel">
    <div class="error-codigo">419</div>
    <h1>La sesión expiró</h1>
    <p class="texto-suave">Por seguridad, vuelva a cargar la página e intente de nuevo.</p>
    <a href="{{ url('/') }}" class="boton boton-principal">Volver al inicio</a>
</div>
@endsection

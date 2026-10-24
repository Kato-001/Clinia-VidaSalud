@extends('compartido.layouts.acceso')
@section('titulo', 'Página no encontrada')

@section('contenido')
<div class="acceso-tarjeta error-panel">
    <div class="error-codigo">404</div>
    <h1>Página no encontrada</h1>
    <p class="texto-suave">La dirección no existe o fue movida.</p>
    <a href="{{ url('/') }}" class="boton boton-principal">Volver al inicio</a>
</div>
@endsection

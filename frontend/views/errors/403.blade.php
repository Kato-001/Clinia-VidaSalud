@extends('compartido.layouts.acceso')
@section('titulo', 'Acceso restringido')

@section('contenido')
<div class="acceso-tarjeta error-panel">
    <div class="error-codigo">403</div>
    <h1>Acceso restringido</h1>
    <p class="texto-suave">Su usuario no tiene permiso para esta opción.</p>
    <a href="{{ url('/') }}" class="boton boton-principal">Volver al inicio</a>
</div>
@endsection

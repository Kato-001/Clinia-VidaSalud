@extends('compartido.layouts.app')
@section('titulo', 'Cambiar contraseña')
@section('migas', 'Mi cuenta · Contraseña')

@section('contenido')
<div class="encabezado"><div><h1>Cambiar contraseña</h1><p class="texto-suave">Use al menos 8 caracteres con letras y números.</p></div></div>

<form class="panel formulario angosto" method="POST" action="{{ route('password.update') }}" data-validacion novalidate>
    @csrf @method('PUT')
    <label class="campo"><span>Contraseña actual *</span>
        <input type="password" name="actual" required autocomplete="current-password"></label>
    <label class="campo"><span>Nueva contraseña *</span>
        <input type="password" name="password" required minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d).{8,}" data-mensaje="Mínimo 8 caracteres con letras y números." autocomplete="new-password"></label>
    <label class="campo"><span>Repetir nueva contraseña *</span>
        <input type="password" name="password_confirmation" required data-coincide="password" autocomplete="new-password"></label>
    <div class="acciones-formulario"><button class="boton boton-principal">Guardar contraseña</button></div>
</form>
@endsection

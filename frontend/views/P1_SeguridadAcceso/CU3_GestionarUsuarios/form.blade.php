@extends('compartido.layouts.app')
@php($editando = $usuario->exists)
@section('titulo', $editando ? 'Editar usuario' : 'Nuevo usuario')
@section('migas', 'Administración · Usuarios · ' . ($editando ? 'Editar' : 'Nuevo'))

@section('contenido')
<div class="encabezado"><div><h1>{{ $editando ? 'Editar usuario' : 'Registrar usuario' }}</h1><p class="texto-suave">Los campos con * son obligatorios.</p></div></div>

<form class="panel formulario" method="POST" action="{{ $editando ? route('usuarios.update', $usuario) : route('usuarios.store') }}" data-validacion novalidate>
    @csrf
    @if($editando) @method('PUT') @endif
    <div class="columnas-2">
        <label class="campo"><span>Nombre completo *</span>
            <input name="nombre" value="{{ old('nombre', $usuario->nombre) }}" required minlength="3" maxlength="150"></label>
        <label class="campo"><span>Rol *</span>
            <select name="idrol" required>
                <option value="">Seleccione…</option>
                @foreach($roles as $r)<option value="{{ $r->idrol }}" @selected(old('idrol', $usuario->idrol) == $r->idrol)>{{ $r->nombre }}</option>@endforeach
            </select></label>
        <label class="campo"><span>Nombre de usuario *</span>
            <input name="username" value="{{ old('username', $usuario->username) }}" required minlength="3" maxlength="50" pattern="[A-Za-z0-9_\-\.]+" data-mensaje="Solo letras, números, guiones y puntos."></label>
        <label class="campo"><span>Correo electrónico *</span>
            <input type="email" name="email" value="{{ old('email', $usuario->email) }}" required maxlength="150"></label>
        <label class="campo"><span>Contraseña {{ $editando ? '(dejar vacío para no cambiar)' : '*' }}</span>
            <input type="password" name="password" {{ $editando ? '' : 'required' }} minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d).{8,}" data-mensaje="Mínimo 8 caracteres con letras y números." autocomplete="new-password"></label>
        <label class="campo"><span>Confirmar contraseña</span>
            <input type="password" name="password_confirmation" data-coincide="password" autocomplete="new-password"></label>
    </div>
    <div class="acciones-formulario">
        <a href="{{ route('usuarios.index') }}" class="boton boton-plano">Cancelar</a>
        <button class="boton boton-principal">Guardar</button>
    </div>
</form>
@endsection

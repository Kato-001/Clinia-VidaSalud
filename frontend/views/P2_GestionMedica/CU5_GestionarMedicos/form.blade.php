@extends('compartido.layouts.app')
@php($editando = $medico->exists)
@section('titulo', $editando ? 'Editar médico' : 'Nuevo médico')
@section('migas', 'Atención · Médicos · ' . ($editando ? 'Editar' : 'Nuevo'))

@section('contenido')
@php($seleccionadas = array_map('intval', old('especialidades', $editando ? $medico->especialidades->pluck('idespecialidad')->all() : [])))
<div class="encabezado"><div><h1>{{ $editando ? 'Editar médico' : 'Registrar médico' }}</h1><p class="texto-suave">Los campos con * son obligatorios.</p></div></div>

<form class="panel formulario" method="POST" action="{{ $editando ? route('medicos.update', $medico) : route('medicos.store') }}" data-validacion novalidate>
    @csrf @if($editando) @method('PUT') @endif
    <h2>Datos profesionales</h2>
    <div class="columnas-2">
        <label class="campo"><span>Nombres *</span>
            <input name="nombres" value="{{ old('nombres', $medico->nombres) }}" required minlength="2" maxlength="100" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+" data-mensaje="Solo letras."></label>
        <label class="campo"><span>Apellidos *</span>
            <input name="apellidos" value="{{ old('apellidos', $medico->apellidos) }}" required minlength="2" maxlength="100" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+" data-mensaje="Solo letras."></label>
        <label class="campo"><span>Código *</span>
            <input name="codigo" value="{{ old('codigo', $medico->codigo) }}" required maxlength="20" class="mono"></label>
        <label class="campo"><span>N.º de registro profesional *</span>
            <input name="registroprofesional" value="{{ old('registroprofesional', $medico->registroprofesional) }}" required maxlength="50"></label>
        <label class="campo"><span>Teléfono *</span>
            <input name="telefono" value="{{ old('telefono', $medico->telefono) }}" required pattern="[0-9+\s\-]{7,20}" data-mensaje="Entre 7 y 20 dígitos."></label>
        <label class="campo"><span>Correo electrónico *</span>
            <input type="email" name="email" value="{{ old('email', $medico->email) }}" required maxlength="150"></label>
    </div>

    @unless($editando)
        <h2>Cuenta de acceso (rol Médico)</h2>
        <div class="columnas-3">
            <label class="campo"><span>Nombre de usuario *</span>
                <input name="username" value="{{ old('username') }}" required minlength="3" maxlength="50" pattern="[A-Za-z0-9_\-\.]+" data-mensaje="Solo letras, números, guiones y puntos." placeholder="Ej. dr.vargas"></label>
            <label class="campo"><span>Contraseña inicial *</span>
                <input type="password" name="password" required minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d).{8,}" data-mensaje="Mínimo 8 caracteres con letras y números." autocomplete="new-password"></label>
            <label class="campo"><span>Confirmar contraseña *</span>
                <input type="password" name="password_confirmation" required data-coincide="password" autocomplete="new-password"></label>
        </div>
    @endunless

    <fieldset class="campo">
        <span>Especialidades *</span>
        <div class="opciones" data-minimo-uno="Seleccione al menos una especialidad.">
            @foreach($especialidades as $e)
                <label class="opcion-tarjeta"><input type="checkbox" name="especialidades[]" value="{{ $e->idespecialidad }}" @checked(in_array($e->idespecialidad, $seleccionadas, true))> {{ $e->nombre }}</label>
            @endforeach
        </div>
    </fieldset>
    <div class="acciones-formulario">
        <a href="{{ $editando ? route('medicos.show', $medico) : route('medicos.index') }}" class="boton boton-plano">Cancelar</a>
        <button class="boton boton-principal">Guardar</button>
    </div>
</form>
@endsection

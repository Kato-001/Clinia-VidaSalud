@extends('compartido.layouts.app')
@php($editando = $paciente->exists)
@section('titulo', $editando ? 'Editar paciente' : 'Nuevo paciente')
@section('migas', 'Atención · Pacientes · ' . ($editando ? 'Editar' : 'Nuevo'))

@section('contenido')
@php($contacto = $editando ? $paciente->contactos->first() : null)
<div class="encabezado"><div><h1>{{ $editando ? 'Editar paciente' : 'Registrar paciente' }}</h1>
    <p class="texto-suave">@if($editando)Código: <span class="mono">{{ $paciente->codigo }}</span> · @endif Los campos con * son obligatorios.</p></div></div>

<form class="panel formulario" method="POST" action="{{ $editando ? route('pacientes.update', $paciente) : route('pacientes.store') }}" data-validacion novalidate>
    @csrf @if($editando) @method('PUT') @endif
    <h2>Datos personales</h2>
    <div class="columnas-3">
        <label class="campo"><span>CI *</span>
            <input name="ci" value="{{ old('ci', $paciente->ci) }}" required maxlength="16" pattern="[0-9]{5,12}(-?[A-Za-z0-9]{1,3})?" data-mensaje="Entre 5 y 12 dígitos (ej. 8456123 o 8456123-1B)."></label>
        <label class="campo"><span>Nombres *</span>
            <input name="nombres" value="{{ old('nombres', $paciente->nombres) }}" required minlength="2" maxlength="100" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+" data-mensaje="Solo letras."></label>
        <label class="campo"><span>Apellidos *</span>
            <input name="apellidos" value="{{ old('apellidos', $paciente->apellidos) }}" required minlength="2" maxlength="100" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+" data-mensaje="Solo letras."></label>
        <label class="campo"><span>Fecha de nacimiento *</span>
            <input type="date" name="fechanacimiento" value="{{ old('fechanacimiento', $paciente->fechanacimiento?->format('Y-m-d')) }}" required max="{{ today()->toDateString() }}" min="1900-01-02"></label>
        <label class="campo"><span>Sexo *</span>
            <select name="sexo" required>
                <option value="">Seleccione…</option>
                @foreach(\App\Models\Paciente::SEXOS as $k => $v)<option value="{{ $k }}" @selected(old('sexo', $paciente->sexo) === $k)>{{ $v }}</option>@endforeach
            </select></label>
        <label class="campo"><span>Tipo de sangre</span>
            <select name="tiposangre">
                <option value="">No registrado</option>
                @foreach(\App\Models\Paciente::TIPOS_SANGRE as $t)<option @selected(old('tiposangre', $paciente->tiposangre) === $t)>{{ $t }}</option>@endforeach
            </select></label>
    </div>
    <h2>Contacto</h2>
    <div class="columnas-3">
        <label class="campo"><span>Teléfono *</span>
            <input name="telefono" value="{{ old('telefono', $paciente->telefono) }}" required pattern="[0-9+\s\-]{7,20}" data-mensaje="Entre 7 y 20 dígitos."></label>
        <label class="campo"><span>Correo electrónico *</span>
            <input type="email" name="email" value="{{ old('email', $paciente->email) }}" required maxlength="150"></label>
        <label class="campo"><span>Dirección</span>
            <input name="direccion" value="{{ old('direccion', $paciente->direccion) }}" maxlength="255"></label>
    </div>
    <h2>Información médica y emergencia</h2>
    <div class="columnas-3">
        <label class="campo"><span>Alergias</span>
            <input name="alergias" value="{{ old('alergias', $paciente->historiaClinica?->alergias) }}" maxlength="255" placeholder="Ninguna conocida"></label>
        <label class="campo"><span>Contacto de emergencia</span>
            <input name="contacto_nombre" value="{{ old('contacto_nombre', $contacto?->nombre) }}" maxlength="150" placeholder="Nombre completo"></label>
        <label class="campo"><span>Parentesco</span>
            <input name="contacto_parentesco" value="{{ old('contacto_parentesco', $contacto?->parentesco) }}" maxlength="50" placeholder="Ej. Madre, esposo"></label>
        <label class="campo"><span>Teléfono de emergencia</span>
            <input name="contacto_telefono" value="{{ old('contacto_telefono', $contacto?->telefono) }}" pattern="[0-9+\s\-]{7,20}" data-mensaje="Entre 7 y 20 dígitos."></label>
    </div>
    <label class="casilla"><input type="checkbox" name="aceptanotificaciones" value="1" @checked(old('aceptanotificaciones', $paciente->aceptanotificaciones ?? true))> El paciente acepta recibir recordatorios de sus citas</label>
    @unless($editando)
        <p class="nota">Se crea la cuenta web del paciente: usuario y contraseña inicial = su CI.</p>
    @endunless
    <div class="acciones-formulario">
        <a href="{{ $editando ? route('pacientes.show', $paciente) : route('pacientes.index') }}" class="boton boton-plano">Cancelar</a>
        <button class="boton boton-principal">Guardar paciente</button>
    </div>
</form>
@endsection

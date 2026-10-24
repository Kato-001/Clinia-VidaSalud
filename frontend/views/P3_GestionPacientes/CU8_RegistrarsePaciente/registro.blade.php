@extends('compartido.layouts.acceso')
@section('titulo', 'Crear cuenta')

@section('contenido')
<div class="acceso-tarjeta ancha">
    <div class="acceso-cabecera">
        <a href="{{ route('portal') }}" class="marca">
            <span class="marca-logo">@include('compartido.partials.logo')</span>
            <span class="marca-texto"><strong>MediTurno</strong><small>Clínica Vida Salud</small></span>
        </a>
        <h1>Cree su cuenta de paciente</h1>
        <p>Con su cuenta podrá reservar fichas, recibir avisos y consultar sus resultados. Los campos con <strong>*</strong> son obligatorios.</p>
    </div>

    <form class="acceso-cuerpo formulario" method="POST" action="{{ route('registro.guardar') }}" data-validacion novalidate>
        @csrf
        @include('compartido.partials.alertas')

        <h2>1. Sus datos</h2>
        <div class="columnas-3">
            <label class="campo"><span>Nombres <em>*</em></span>
                <input name="nombres" value="{{ old('nombres') }}" required minlength="2" maxlength="100" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+" data-mensaje="Use solo letras."></label>
            <label class="campo"><span>Apellidos <em>*</em></span>
                <input name="apellidos" value="{{ old('apellidos') }}" required minlength="2" maxlength="100" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+" data-mensaje="Use solo letras."></label>
            <label class="campo"><span>Cédula de identidad <em>*</em></span>
                <input name="ci" value="{{ old('ci') }}" required maxlength="16" pattern="[0-9]{5,12}(-?[A-Za-z0-9]{1,3})?" data-mensaje="Entre 5 y 12 dígitos, ej. 8456123 o 8456123-1B."></label>
            <label class="campo"><span>Fecha de nacimiento <em>*</em></span>
                <input type="date" name="fechanacimiento" value="{{ old('fechanacimiento') }}" required max="{{ today()->toDateString() }}" min="1900-01-02"></label>
            <label class="campo"><span>Sexo <em>*</em></span>
                <select name="sexo" required><option value="">Elija…</option>
                    @foreach(\App\Models\Paciente::SEXOS as $k => $v)<option value="{{ $k }}" @selected(old('sexo') === $k)>{{ $v }}</option>@endforeach
                </select></label>
            <label class="campo"><span>Tipo de sangre</span>
                <select name="tiposangre"><option value="">No lo sé</option>
                    @foreach(\App\Models\Paciente::TIPOS_SANGRE as $t)<option @selected(old('tiposangre') === $t)>{{ $t }}</option>@endforeach
                </select></label>
            <label class="campo"><span>Celular <em>*</em></span>
                <input name="telefono" value="{{ old('telefono') }}" required pattern="[0-9+\s\-]{7,20}" data-mensaje="Entre 7 y 20 dígitos."></label>
            <label class="campo" style="grid-column: span 2"><span>Dirección</span>
                <input name="direccion" value="{{ old('direccion') }}" maxlength="255"></label>
        </div>

        <h2>2. Su acceso</h2>
        <div class="columnas-3">
            <label class="campo"><span>Correo electrónico <em>*</em></span>
                <input type="email" name="email" value="{{ old('email') }}" required maxlength="150"></label>
            <label class="campo"><span>Contraseña <em>*</em></span>
                <input type="password" name="password" required minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d).{8,}" data-mensaje="Mínimo 8 caracteres, con letras y números." autocomplete="new-password"></label>
            <label class="campo"><span>Repita la contraseña <em>*</em></span>
                <input type="password" name="password_confirmation" required data-coincide="password" autocomplete="new-password"></label>
        </div>
        <p class="nota">Para ingresar usará su <strong>CI</strong> o su correo.</p>

        <label class="casilla"><input type="checkbox" name="aceptanotificaciones" value="1" @checked(old('aceptanotificaciones', true))> Quiero recibir avisos de mis citas y resultados.</label>
        <label class="casilla"><input type="checkbox" name="acepta" value="1" required @checked(old('acepta'))> Autorizo el uso de mis datos para mi atención médica.</label>

        <div class="acciones-formulario">
            <a href="{{ route('login') }}" class="boton boton-plano">Ya tengo cuenta</a>
            <button class="boton boton-verde">Crear mi cuenta</button>
        </div>
    </form>
</div>
@endsection

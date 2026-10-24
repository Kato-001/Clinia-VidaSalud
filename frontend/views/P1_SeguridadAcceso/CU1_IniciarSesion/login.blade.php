@extends('compartido.layouts.acceso')
@section('titulo', 'Ingresar')

@section('contenido')
<div class="acceso-tarjeta">
    <div class="acceso-cabecera">
        <a href="{{ route('portal') }}" class="marca">
            <span class="marca-logo">@include('compartido.partials.logo')</span>
            <span class="marca-texto"><strong>MediTurno</strong><small>Clínica Vida Salud</small></span>
        </a>
        <h1>Bienvenido de nuevo</h1>
        <p>Ingrese para gestionar sus fichas y citas médicas.</p>
        <div class="ventajas"><span>Turnos en línea</span><span>Sin filas</span><span>Historial seguro</span></div>
    </div>

    <form class="acceso-cuerpo" method="POST" action="{{ route('login.ingresar') }}" data-validacion novalidate>
        @csrf
        @if(session('ok'))<div class="aviso aviso-exito">{{ session('ok') }}</div>@endif
        @if(session('error'))<div class="aviso aviso-error">{{ session('error') }}</div>@endif
        @error('login')<div class="aviso aviso-error" role="alert">@include('compartido.partials.icono', ['n' => 'alerta'])<div>{{ $message }}</div></div>@enderror

        <label class="campo"><span>Usuario, CI o correo</span>
            <input type="text" name="login" value="{{ old('login') }}" required maxlength="150" autocomplete="username" autofocus placeholder="Ej. 1234567 o nombre@correo.com"></label>
        <label class="campo"><span>Contraseña</span>
            <span class="clave">
                <input type="password" name="password" id="clave" required maxlength="100" autocomplete="current-password">
                <button type="button" class="boton boton-plano boton-chico" data-mostrar-clave="clave">Ver</button>
            </span></label>
        <button type="submit" class="boton boton-principal boton-ancho">Ingresar</button>
    </form>

    <p class="acceso-pie">
        ¿Primera vez? <a href="{{ route('registro') }}"><strong>Cree su cuenta de paciente</strong></a><br>
        <a href="{{ route('portal.horarios') }}">Ver turnos disponibles sin ingresar</a>
    </p>
</div>
@endsection

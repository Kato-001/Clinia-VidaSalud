@extends('compartido.layouts.app')
@section('titulo', 'Mis datos')
@section('migas', 'Mi salud · Mis datos')

@section('contenido')
<div class="encabezado"><div><h1>Mis datos</h1><p class="texto-suave">{{ $paciente->codigo }} · CI {{ $paciente->ci }} · Historia {{ $paciente->historiaClinica?->numero ?? '—' }}</p></div></div>

<div class="columnas-2 columnas-formulario">
    <form class="panel formulario" method="POST" action="{{ route('perfil.update') }}" data-validacion novalidate>
        @csrf @method('PUT')
        <h2>Datos personales</h2>
        <label class="campo"><span>Nombres *</span>
            <input name="nombres" value="{{ old('nombres', $paciente->nombres) }}" required minlength="2" maxlength="100" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+" data-mensaje="Solo letras."></label>
        <label class="campo"><span>Apellidos *</span>
            <input name="apellidos" value="{{ old('apellidos', $paciente->apellidos) }}" required minlength="2" maxlength="100" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+" data-mensaje="Solo letras."></label>
        <div class="columnas-2">
            <label class="campo"><span>Fecha de nacimiento *</span>
                <input type="date" name="fechanacimiento" value="{{ old('fechanacimiento', $paciente->fechanacimiento->format('Y-m-d')) }}" required max="{{ today()->toDateString() }}"></label>
            <label class="campo"><span>Sexo *</span>
                <select name="sexo" required>
                    @foreach(\App\Models\Paciente::SEXOS as $k => $v)<option value="{{ $k }}" @selected(old('sexo', $paciente->sexo) === $k)>{{ $v }}</option>@endforeach
                </select></label>
            <label class="campo"><span>Teléfono *</span>
                <input name="telefono" value="{{ old('telefono', $paciente->telefono) }}" required pattern="[0-9+\s\-]{7,20}" data-mensaje="Entre 7 y 20 dígitos."></label>
            <label class="campo"><span>Tipo de sangre</span>
                <select name="tiposangre">
                    <option value="">No registrado</option>
                    @foreach(\App\Models\Paciente::TIPOS_SANGRE as $t)<option @selected(old('tiposangre', $paciente->tiposangre) === $t)>{{ $t }}</option>@endforeach
                </select></label>
        </div>
        <label class="campo"><span>Correo electrónico *</span>
            <input type="email" name="email" value="{{ old('email', $paciente->email) }}" required maxlength="150"></label>
        <label class="campo"><span>Dirección</span>
            <input name="direccion" value="{{ old('direccion', $paciente->direccion) }}" maxlength="255"></label>
        <label class="casilla"><input type="checkbox" name="aceptanotificaciones" value="1" @checked(old('aceptanotificaciones', $paciente->aceptanotificaciones))> Deseo recibir notificaciones de mis citas</label>
        <div class="acciones-formulario"><button class="boton boton-principal">Guardar cambios</button></div>
    </form>

    <div>
        <div class="panel">
            <h2>Contactos de emergencia</h2>
            @forelse($paciente->contactos as $c)
                <div class="elemento">
                    <div><strong>{{ $c->nombre }}</strong><small>{{ $c->parentesco }} · {{ $c->telefono }}</small></div>
                    <form method="POST" action="{{ route('perfil.contactos.destroy', $c) }}" data-confirmacion="¿Eliminar a {{ $c->nombre }} de sus contactos?">
                        @csrf @method('DELETE')<button class="boton boton-plano boton-chico">Quitar</button>
                    </form>
                </div>
            @empty
                <p class="sin-datos">Aún no registró contactos de emergencia.</p>
            @endforelse
        </div>
        <form class="panel formulario" method="POST" action="{{ route('perfil.contactos.store') }}" data-validacion novalidate>
            @csrf
            <h2>Agregar contacto</h2>
            <div class="columnas-3">
                <label class="campo"><span>Nombre *</span><input name="nombre" required maxlength="150"></label>
                <label class="campo"><span>Parentesco *</span><input name="parentesco" required maxlength="50" placeholder="Ej. Madre"></label>
                <label class="campo"><span>Teléfono *</span><input name="telefono" required pattern="[0-9+\s\-]{7,20}" data-mensaje="Entre 7 y 20 dígitos."></label>
            </div>
            <div class="acciones-formulario"><button class="boton boton-borde">Agregar</button></div>
        </form>
    </div>
</div>
@endsection

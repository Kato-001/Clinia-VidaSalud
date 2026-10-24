@extends('compartido.layouts.app')
@section('titulo', 'Roles')
@section('migas', 'Administración · Roles')

@section('contenido')
<div class="encabezado"><div><h1>Roles</h1><p class="texto-suave">Cada usuario tiene un rol que define a qué pantallas puede entrar.</p></div></div>

<div class="columnas-2 columnas-formulario">
    <form class="panel formulario" method="POST" action="{{ route('roles.store') }}" data-validacion novalidate>
        @csrf
        <h2>Nuevo rol</h2>
        <label class="campo"><span>Nombre *</span>
            <input name="nombre" value="{{ old('nombre') }}" required maxlength="50" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ_ ]+" data-mensaje="Solo letras."></label>
        <label class="campo"><span>Descripción</span>
            <textarea name="descripcion" rows="2" maxlength="255">{{ old('descripcion') }}</textarea></label>
        <div class="acciones-formulario"><button class="boton boton-principal">Agregar rol</button></div>
    </form>

    <div class="panel">
        <table class="tabla-datos">
            <thead><tr><th>Rol</th><th>Descripción</th><th>Usuarios</th><th></th></tr></thead>
            <tbody>
            @foreach($roles as $r)
                <tr>
                    <td><strong>{{ $r->nombre }}</strong></td>
                    <td>
                        <form method="POST" action="{{ route('roles.update', $r) }}" class="clave">
                            @csrf @method('PUT')
                            <input type="hidden" name="nombre" value="{{ $r->nombre }}">
                            <input name="descripcion" value="{{ $r->descripcion }}" maxlength="255" aria-label="Descripción de {{ $r->nombre }}">
                            <button class="boton boton-plano boton-chico">Guardar</button>
                        </form>
                    </td>
                    <td>{{ $r->usuarios_count }}</td>
                    <td class="celda-acciones">
                        @unless(in_array($r->nombre, $delSistema, true) || $r->usuarios_count)
                            <form method="POST" action="{{ route('roles.destroy', $r) }}" data-confirmacion="¿Eliminar el rol {{ $r->nombre }}?">
                                @csrf @method('DELETE')<button class="boton boton-plano boton-chico">Eliminar</button>
                            </form>
                        @endunless
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <p class="nota">ADMINISTRADOR, RECEPCIONISTA, MEDICO y PACIENTE son roles del sistema: sus permisos están definidos en las rutas y no se pueden renombrar ni eliminar.</p>
    </div>
</div>
@endsection

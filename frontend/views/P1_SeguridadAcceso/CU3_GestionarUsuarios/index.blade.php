@extends('compartido.layouts.app')
@section('titulo', 'Usuarios')
@section('migas', 'Administración · Usuarios')

@section('contenido')
<div class="encabezado">
    <div><h1>Usuarios del sistema</h1><p class="texto-suave">Cuentas de acceso y rol asignado a cada persona.</p></div>
    <a href="{{ route('usuarios.create') }}" class="boton boton-principal">+ Nuevo usuario</a>
</div>

<form class="barra-filtros" method="GET">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre, usuario o correo">
    <select name="rol">
        <option value="">Todos los roles</option>
        @foreach($roles as $r)<option value="{{ $r->idrol }}" @selected(request('rol') == $r->idrol)>{{ $r->nombre }}</option>@endforeach
    </select>
    <button class="boton boton-borde">Buscar</button>
</form>

<div class="panel">
    <table class="tabla-datos">
        <thead><tr><th>Nombre</th><th>Usuario</th><th>Correo</th><th>Rol</th><th>Último acceso</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @forelse($usuarios as $usuario)
            <tr>
                <td><strong>{{ $usuario->nombre }}</strong></td>
                <td class="mono">{{ $usuario->username }}</td>
                <td>{{ $usuario->email }}</td>
                <td>{{ $usuario->nombre_rol }}</td>
                <td>{{ $usuario->ultimoacceso?->format('d/m/Y H:i') ?? '—' }}</td>
                <td>@include('compartido.partials.estado', ['estado' => $usuario->estado])</td>
                <td class="celda-acciones">
                    <a href="{{ route('usuarios.edit', $usuario) }}" class="boton boton-plano boton-chico">Editar</a>
                    <form method="POST" action="{{ route('usuarios.estado', $usuario) }}" data-confirmacion="¿{{ $usuario->estado === 'ACTIVO' ? 'Desactivar' : 'Activar' }} la cuenta de {{ $usuario->nombre }}?">
                        @csrf @method('PATCH')
                        <button class="boton boton-plano boton-chico">{{ $usuario->estado === 'ACTIVO' ? 'Desactivar' : 'Activar' }}</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="sin-datos">No se encontraron usuarios.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $usuarios->links('compartido.partials.paginacion') }}
</div>
<p class="nota">Las cuentas de médicos se crean desde <a href="{{ route('medicos.create') }}">Médicos</a> y las de pacientes al registrarlos.</p>
@endsection

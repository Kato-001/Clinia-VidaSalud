@extends('compartido.layouts.app')
@section('titulo', $medico->nombre_completo)
@section('migas', 'Atención · Médicos · ' . $medico->codigo)

@section('contenido')
@php($activo = $medico->estado === 'ACTIVO')
<div class="encabezado">
    <div><h1>{{ $medico->nombre_completo }}</h1>
        <p class="texto-suave"><span class="mono">{{ $medico->codigo }}</span> · Reg. {{ $medico->registroprofesional }} · @include('compartido.partials.estado', ['estado' => $medico->estado])</p></div>
    <div class="grupo-botones">
        <a href="{{ route('agenda.index', ['medico' => $medico->idmedico]) }}" class="boton boton-borde">Ver agenda</a>
        @if(auth()->user()->tieneRol('ADMINISTRADOR'))
            <a href="{{ route('horarios.index', $medico) }}" class="boton boton-borde">Horarios</a>
            <a href="{{ route('medicos.edit', $medico) }}" class="boton boton-borde">Editar</a>
            <form method="POST" action="{{ route('medicos.estado', $medico) }}" data-confirmacion="{{ $activo ? 'Un médico inactivo no podrá recibir nuevas citas ni ingresar al sistema. ¿Continuar?' : '¿Activar al médico?' }}">
                @csrf @method('PATCH')<button class="boton {{ $activo ? 'boton-peligro' : 'boton-principal' }}">{{ $activo ? 'Desactivar' : 'Activar' }}</button>
            </form>
        @endif
    </div>
</div>
<div class="columnas-2">
    <div class="panel">
        <h2>Datos</h2>
        <dl class="ficha-datos">
            <dt>Especialidades</dt><dd>@foreach($medico->especialidades as $e)<span class="pastilla">{{ $e->nombre }}</span>@endforeach</dd>
            <dt>Teléfono</dt><dd>{{ $medico->telefono ?? '—' }}</dd>
            <dt>Correo</dt><dd>{{ $medico->email ?? '—' }}</dd>
            <dt>Cuenta de acceso</dt><dd class="mono">{{ $medico->usuario?->username }}</dd>
        </dl>
    </div>
    <div class="panel">
        <h2>Horario de atención</h2>
        @forelse($medico->horarios as $h)
            <div class="linea-horario {{ $h->estado === 'ACTIVO' ? '' : 'atenuado' }}"><strong>{{ $h->dia }}</strong><span class="mono">{{ substr($h->horainicio,0,5) }} – {{ substr($h->horafin,0,5) }}</span><small>fichas de {{ $duracion }} min</small></div>
        @empty
            <p class="sin-datos">Aún no tiene horarios registrados.</p>
        @endforelse
    </div>
</div>
@endsection

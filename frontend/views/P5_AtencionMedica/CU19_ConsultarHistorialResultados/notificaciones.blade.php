@extends('compartido.layouts.app')
@section('titulo', 'Notificaciones')
@section('migas', 'Mi salud · Notificaciones')

@section('contenido')
<div class="encabezado">
    <div><h1>Notificaciones</h1><p class="texto-suave">Avisos sobre sus citas y resultados.</p></div>
    @if($notificaciones->contains(fn ($n) => ! $n->leidaen))
        <form method="POST" action="{{ route('notificaciones.leidas') }}">@csrf<button class="boton boton-borde">Marcar todas como leídas</button></form>
    @endif
</div>
<div class="panel">
    @forelse($notificaciones as $n)
        <div class="notificacion {{ $n->leidaen ? '' : 'nueva' }}">
            <div class="avatar notificacion-icono">{{ ['RESERVA' => '✓', 'REPROGRAMACION' => '↻', 'CANCELACION' => '✕', 'RESULTADO' => '⚗'][$n->tipo] ?? '•' }}</div>
            <div>
                <strong>{{ ucfirst(mb_strtolower($n->tipo)) }}{{ $n->leidaen ? '' : ' · nueva' }}</strong>
                <span>{{ $n->mensaje }}</span><br>
                <small class="texto-suave">{{ $n->enviadaen?->format('d/m/Y H:i') }}</small>
                @if($n->cita)· <a href="{{ route('citas.show', $n->cita) }}" class="enlace">Ver cita</a>@endif
                @if($n->idestudio)· <a href="{{ route('paciente.resultados') }}" class="enlace">Ver resultado</a>@endif
            </div>
        </div>
    @empty
        <p class="sin-datos">No tiene notificaciones.</p>
    @endforelse
    {{ $notificaciones->links('compartido.partials.paginacion') }}
</div>
@endsection

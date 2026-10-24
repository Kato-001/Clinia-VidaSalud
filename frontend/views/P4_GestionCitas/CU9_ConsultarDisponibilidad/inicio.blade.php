@extends('compartido.layouts.publico')
@section('titulo', 'Reserve su ficha médica')

@section('contenido')
<section class="portada">
    <div>
        <span class="pastilla">Clínica Vida Salud · reservas en línea</span>
        <h1>Su ficha médica, <span>sin hacer fila</span>.</h1>
        <p>Consulte los turnos libres de nuestros especialistas y reserve en minutos desde su celular. Cada ficha dura {{ $duracion }} minutos y le avisamos si algo cambia.</p>
        <div class="grupo-botones">
            <a href="{{ route('portal.horarios') }}" class="boton boton-principal">Ver turnos disponibles</a>
            @guest<a href="{{ route('registro') }}" class="boton boton-borde">Crear mi cuenta</a>@endguest
        </div>
    </div>
    <div class="portada-tarjeta">
        <h2 style="margin-bottom:1rem">¿Cómo reservo?</h2>
        <ol>
            <li><b>1</b><div><strong>Cree su cuenta</strong><span class="texto-suave">Con su CI y un correo de contacto.</span></div></li>
            <li><b>2</b><div><strong>Elija especialidad y médico</strong><span class="texto-suave">Vea sus días de atención y turnos libres.</span></div></li>
            <li><b>3</b><div><strong>Reserve y confirme</strong><span class="texto-suave">Llegue 10 minutos antes de su turno.</span></div></li>
        </ol>
    </div>
</section>

<section class="portal-seccion" id="especialidades">
    <h2>Especialidades</h2>
    <div class="grilla-especialidades">
        @foreach($especialidades as $e)
            <div class="tarjeta-especialidad">
                <strong>{{ $e->nombre }}</strong>
                <span>{{ $e->descripcion }}</span>
                <span class="pastilla">{{ $e->medicos_count }} médico(s)</span>
            </div>
        @endforeach
    </div>
</section>

<section class="portal-seccion" id="medicos">
    <h2>Nuestro equipo médico</h2>
    <div class="grilla-medicos">
        @foreach($medicos as $m)
            <div class="panel tarjeta-medico">
                <div class="avatar">{{ mb_substr($m->nombres, 0, 1) }}{{ mb_substr($m->apellidos, 0, 1) }}</div>
                <div>
                    <h3>Dr(a). {{ $m->nombre_completo }}</h3>
                    <div style="margin:.3rem 0">@foreach($m->especialidades as $e)<span class="pastilla">{{ $e->nombre }}</span>@endforeach</div>
                    {{-- Horarios agrupados por día: "Lun 08:00–12:00, 14:00–18:00" --}}
                    <ul class="horario-dias">
                        @forelse($m->horarios->groupBy('diasemana') as $rangos)
                            <li><b>{{ mb_substr($rangos->first()->dia, 0, 3) }}</b>
                                {{ $rangos->map(fn ($h) => substr($h->horainicio, 0, 5) . '–' . substr($h->horafin, 0, 5))->join(', ') }}</li>
                        @empty
                            <li>Sin horario publicado</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endsection

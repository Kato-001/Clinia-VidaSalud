@extends('compartido.layouts.publico')
@section('titulo', 'Turnos disponibles')

@section('contenido')
<section class="portal-seccion">
    <h2>Turnos disponibles</h2>
    <p class="texto-suave">Elija especialidad, médico y fecha. Los turnos en verde están libres; los grises ya están ocupados o pasaron.</p>

    <div class="panel panel-destacado" id="consulta-turnos" data-url-medicos="{{ url('api/especialidades') }}" data-url-bloques="{{ url('api/medicos') }}">
        <div class="columnas-3">
            <label class="campo"><span>Especialidad</span>
                <select name="especialidad"><option value="">Elija…</option>
                    @foreach($especialidades as $e)<option value="{{ $e->idespecialidad }}">{{ $e->nombre }}</option>@endforeach
                </select></label>
            <label class="campo"><span>Médico</span>
                <select name="medico" disabled><option value="">Primero elija la especialidad</option></select></label>
            <label class="campo"><span>Fecha</span>
                <input type="date" name="fecha" min="{{ today()->toDateString() }}" value="{{ today()->toDateString() }}"></label>
        </div>
        <p class="nota" id="dias-atencion"></p>
        <div id="turnos"><p class="nota">Elija médico y fecha para ver los turnos.</p></div>
        <div class="acciones-formulario">
            @auth
                @if(auth()->user()->tieneRol('PACIENTE'))<a href="{{ route('citas.create') }}" class="boton boton-verde">Reservar una ficha</a>@endif
            @else
                <span class="texto-suave">Para reservar <a href="{{ route('login') }}">ingrese</a> o <a href="{{ route('registro') }}">cree su cuenta</a>.</span>
            @endauth
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ url('assets/js/compartido/turnos.js') }}"></script>
<script src="{{ url('assets/js/P4_GestionCitas/CU9_ConsultarDisponibilidad/portal-horarios.js') }}"></script>
@endpush

{{-- Mensajes del sistema: éxito, regla de negocio incumplida y errores de validación --}}
@if(session('ok'))
    <div class="aviso aviso-exito" role="status">@include('compartido.partials.icono', ['n' => 'check'])<div>{{ session('ok') }}</div></div>
@endif
@if(session('error'))
    <div class="aviso aviso-error" role="alert">@include('compartido.partials.icono', ['n' => 'alerta'])
        <div>{{ session('error') }}
            @if(session('detalles'))<ul>@foreach(session('detalles') as $d)<li>{{ $d }}</li>@endforeach</ul>@endif
        </div>
    </div>
@endif
@if($errors->any() && ! request()->routeIs('login'))
    <div class="aviso aviso-error" role="alert">@include('compartido.partials.icono', ['n' => 'alerta'])
        <div><strong>Corrija lo siguiente para continuar:</strong>
            <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    </div>
@endif

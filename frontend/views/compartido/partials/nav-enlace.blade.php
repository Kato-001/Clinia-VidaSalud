{{-- Enlace del menú lateral con ícono; $activa = patrones de ruta que lo marcan como actual --}}
<a href="{{ route($ruta) }}" class="nav-enlace {{ request()->routeIs(...(array) ($activa ?? $ruta)) ? 'actual' : '' }}">
    @include('compartido.partials.icono', ['n' => $icono])
    <span>{{ $texto }}</span>
    @if(! empty($contador))<span class="nav-contador">{{ $contador }}</span>@endif
</a>

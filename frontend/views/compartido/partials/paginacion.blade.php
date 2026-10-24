{{-- Paginador de listados --}}
@if ($paginator->hasPages())
    <nav class="paginador" aria-label="Paginación">
        <span>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }} registros</span>
        <div class="grupo-botones">
            <a class="boton boton-borde boton-chico {{ $paginator->onFirstPage() ? 'deshabilitado' : '' }}" href="{{ $paginator->previousPageUrl() }}">← Anterior</a>
            <span class="texto-suave">Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</span>
            <a class="boton boton-borde boton-chico {{ $paginator->hasMorePages() ? '' : 'deshabilitado' }}" href="{{ $paginator->nextPageUrl() }}">Siguiente →</a>
        </div>
    </nav>
@endif

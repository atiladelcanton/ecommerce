@if ($paginator->hasPages())
    <div class="storefront-pagination">
        <p class="storefront-pagination-summary">Mostrando {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} de {{ $paginator->total() }} {{ $paginator->total() === 1 ? 'pedido' : 'pedidos' }}</p>
        <nav class="storefront-pagination-navigation" aria-label="Paginação de pedidos">
            @if ($paginator->onFirstPage())
                <span class="storefront-pagination-bullet" aria-disabled="true"><x-heroicon-o-chevron-left class="size-4" /></span>
            @else
                <a class="storefront-pagination-bullet" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Página anterior"><x-heroicon-o-chevron-left class="size-4" /></a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="storefront-pagination-ellipsis" aria-hidden="true">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page === $paginator->currentPage())
                            <span class="storefront-pagination-bullet" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="storefront-pagination-bullet" href="{{ $url }}" aria-label="Ver página {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="storefront-pagination-bullet" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Próxima página"><x-heroicon-o-chevron-right class="size-4" /></a>
            @else
                <span class="storefront-pagination-bullet" aria-disabled="true"><x-heroicon-o-chevron-right class="size-4" /></span>
            @endif
        </nav>
    </div>
@endif

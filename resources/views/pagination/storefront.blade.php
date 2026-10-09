@php
    $currentPage = $paginator->currentPage();
    $lastPage = $paginator->lastPage();
    if ($lastPage <= 7) {
        $pages = range(1, $lastPage);
    } elseif ($currentPage <= 3) {
        $pages = [...range(1, 4), $lastPage];
    } elseif ($currentPage >= $lastPage - 2) {
        $pages = [1, ...range($lastPage - 3, $lastPage)];
    } else {
        $pages = array_values(array_unique([1, ...range($currentPage - 2, $currentPage + 2), $lastPage]));
    }
    $mobileStart = max(1, min($currentPage - 1, $lastPage - 2));
    $mobileEnd = min($lastPage, $mobileStart + 2);
    $previousNumber = null;
@endphp

<div class="storefront-pagination" x-data="{
    scrollToGrid() {
        this.$nextTick(() => document.getElementById('store-products-grid')?.scrollIntoView({
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth',
            block: 'start'
        }));
    }
}">
    <p class="storefront-pagination-summary" role="status">Mostrando {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} de {{ $paginator->total() }} {{ $paginator->total() === 1 ? 'produto' : 'produtos' }}</p>

    @if($paginator->hasPages())
        <div class="storefront-pagination-navigation">
            <p class="storefront-pagination-mobile-status">Página {{ $currentPage }} de {{ $lastPage }}</p>
            <nav aria-label="Paginação de produtos">
                <button type="button" class="storefront-pagination-bullet" aria-label="Página anterior"
                    @disabled($paginator->onFirstPage())
                    @if(!$paginator->onFirstPage())
                        x-on:click="$wire.previousPage('{{ $paginator->getPageName() }}').then(() => scrollToGrid())"
                        wire:loading.attr="disabled" wire:target="previousPage,nextPage,gotoPage"
                    @endif>
                    <x-heroicon-o-chevron-left class="size-4" aria-hidden="true" />
                </button>

                @foreach($pages as $page)
                    @if($previousNumber !== null && $page > $previousNumber + 1)
                        <span class="storefront-pagination-ellipsis storefront-pagination-desktop-only" aria-hidden="true">…</span>
                    @endif
                    @php $desktopOnly = $page < $mobileStart || $page > $mobileEnd; @endphp
                    @if($page === $currentPage)
                        <span @class(['storefront-pagination-bullet', 'storefront-pagination-desktop-only' => $desktopOnly]) aria-current="page" aria-label="Página {{ $page }}, atual">{{ $page }}</span>
                    @else
                        <button type="button" @class(['storefront-pagination-bullet', 'storefront-pagination-desktop-only' => $desktopOnly])
                            wire:key="storefront-{{ $paginator->getPageName() }}-{{ $page }}"
                            x-on:click="$wire.gotoPage({{ $page }}, '{{ $paginator->getPageName() }}').then(() => scrollToGrid())"
                            wire:loading.attr="disabled" wire:target="previousPage,nextPage,gotoPage"
                            aria-label="Ver página {{ $page }}">{{ $page }}</button>
                    @endif
                    @php $previousNumber = $page; @endphp
                @endforeach

                <button type="button" class="storefront-pagination-bullet" aria-label="Próxima página"
                    @disabled(!$paginator->hasMorePages())
                    @if($paginator->hasMorePages())
                        x-on:click="$wire.nextPage('{{ $paginator->getPageName() }}').then(() => scrollToGrid())"
                        wire:loading.attr="disabled" wire:target="previousPage,nextPage,gotoPage"
                    @endif>
                    <x-heroicon-o-chevron-right class="size-4" aria-hidden="true" />
                </button>
            </nav>
        </div>
    @endif
</div>

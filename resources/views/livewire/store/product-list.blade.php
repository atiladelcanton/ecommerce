<div x-data class="pb-16">
    <div class="store-section-heading mt-4"><div><p class="store-eyebrow">Catálogo SABA</p><h1 class="store-title mt-2">Produtos</h1></div></div>
    <div class="grid gap-10 lg:grid-cols-[240px_minmax(0,1fr)]">
        <aside class="hidden lg:block"><h2 class="font-semibold mb-6">Filtrar produtos</h2><x-store.filters :categories="$categories" :availabilities="$availabilities" prefix="desktop" /></aside>
        <div class="min-w-0">
            <div class="flex flex-wrap items-center justify-between gap-4 pb-6 border-b border-store-border mb-7">
                <p class="text-sm text-store-muted" role="status">{{ $products->total() }} {{ $products->total() === 1 ? 'produto encontrado' : 'produtos encontrados' }}</p>
                <button type="button" class="store-button-secondary lg:hidden" @click="$refs.filters.showModal()"><x-heroicon-o-adjustments-horizontal class="size-4" /> Filtros</button>
                <div class="flex items-center gap-2 text-sm"><label for="sort" class="sr-only">Ordenar produtos</label><select id="sort" wire:model.live="sort" class="store-input"><option value="">Mais recentes</option><option value="menor-preco">Menor preço</option><option value="maior-preco">Maior preço</option><option value="nome-az">Nome A-Z</option><option value="nome-za">Nome Z-A</option></select></div>
            </div>
            @if($search !== '')<div class="mb-6 text-sm flex items-center gap-3">Resultados para “{{ $search }}” <button class="store-text-link" wire:click="$set('search', '')">Remover busca</button></div>@endif
            <p wire:loading.delay class="text-sm text-store-muted mb-3" role="status">Atualizando produtos…</p>
            <div wire:loading.class="opacity-50" class="transition-opacity">
                @if($products->count())
                    <div class="store-product-grid store-catalog-grid">@foreach($products as $product)<div wire:key="product-{{ $product->id }}"><x-store.product-card :product="$product" /></div>@endforeach</div>
                    <div class="mt-10 store-pagination">{{ $products->links() }}</div>
                @else
                    <div class="py-20 text-center bg-store-soft rounded-xl"><x-heroicon-o-magnifying-glass class="size-10 mx-auto text-store-muted mb-5" /><h2 class="text-xl font-semibold">Nenhum produto encontrado.</h2>@if($search)<p class="text-store-muted mt-3">Nenhum resultado para '{{ $search }}'.</p>@endif<button class="store-button-secondary mt-6" wire:click="clearFilters">Limpar filtros</button></div>
                @endif
            </div>
        </div>
    </div>
    <dialog x-ref="filters" class="store-dialog" aria-labelledby="filter-title">
        <div class="flex items-center justify-between mb-8"><h2 id="filter-title" class="text-xl font-semibold">Filtrar produtos</h2><button class="store-icon-btn" @click="$refs.filters.close()" aria-label="Fechar filtros"><x-heroicon-o-x-mark class="size-6" /></button></div>
        <x-store.filters :categories="$categories" :availabilities="$availabilities" prefix="mobile" />
        <button class="store-button w-full mt-8" @click="$refs.filters.close()">Ver produtos</button>
    </dialog>
</div>

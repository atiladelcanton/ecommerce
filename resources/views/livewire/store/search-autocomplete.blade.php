<div class="relative" x-data="storeSearch" @click.outside="open = false" @keydown.escape="open = false; active = -1" @keydown.arrow-down.prevent="move(1)" @keydown.arrow-up.prevent="move(-1)" @keydown.enter="choose($event)">
    <form action="{{ route('store.products') }}" method="get" role="search">
        <label for="store-search" class="sr-only">Buscar produtos</label>
        <div class="relative"><input id="store-search" name="busca" type="search" wire:model.live.debounce.300ms="term" maxlength="100" placeholder="O que você procura?" class="store-input pr-12" autocomplete="off" role="combobox" aria-autocomplete="list" aria-controls="search-suggestions" :aria-expanded="open" :aria-activedescendant="active >= 0 ? 'search-option-' + active : null" @focus="open = true" @input="open = true; active = -1"><button type="submit" class="absolute right-3 top-2.5" aria-label="Buscar"><x-heroicon-o-magnifying-glass class="size-5 text-store-muted" /></button></div>
    </form>
    <span wire:loading.delay wire:target="term" class="absolute right-0 top-full text-xs bg-white text-store-muted px-2 z-40" role="status">Buscando…</span>
    @if(mb_strlen(trim($term)) >= 2)
        <div x-show="open" x-cloak class="store-dropdown right-0 w-full min-w-0" id="search-suggestions" role="listbox" aria-label="Sugestões de produtos">
            @forelse($suggestions as $product)
                <a data-suggestion id="search-option-{{ $loop->index }}" role="option" :aria-selected="active === {{ $loop->index }}" :class="active === {{ $loop->index }} ? 'bg-store-soft' : ''" href="{{ route('store.product', $product->slug) }}" class="flex items-center gap-3 p-3 rounded-md hover:bg-store-soft" @mouseenter="active = {{ $loop->index }}">
                    @if($product->images->first())<img class="size-12 object-contain bg-store-soft rounded" src="{{ Storage::disk('public')->url($product->images->first()->path) }}" alt="" loading="lazy" width="48" height="48">@else<x-heroicon-o-photo class="size-10 shrink-0 text-store-muted" />@endif
                    <span class="min-w-0"><span class="block text-sm font-medium">{{ $product->name }}</span><span class="block text-xs text-store-muted mt-1">{{ $product->category->name }}</span><span class="block text-xs mt-1 font-semibold">{{ \App\Support\BrazilianMoney::display($product->final_price) }}</span></span>
                </a>
            @empty <p class="p-3 text-sm text-store-muted" role="status">Nenhum resultado para '{{ $term }}'.</p> @endforelse
            <a data-suggestion id="search-option-{{ $suggestions->count() }}" role="option" :aria-selected="active === {{ $suggestions->count() }}" class="block border-t border-store-border p-3 text-sm font-medium text-store-red" :class="active === {{ $suggestions->count() }} ? 'bg-store-soft' : ''" href="{{ route('store.products', ['busca' => $term]) }}">Ver todos os resultados para '{{ $term }}' →</a>
        </div>
    @endif
</div>

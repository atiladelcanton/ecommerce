<a class="store-icon-btn store-cart-link" href="{{ route('store.cart') }}" aria-label="Carrinho, {{ $count }} {{ $count === 1 ? 'item' : 'itens' }}" @if(request()->routeIs('store.cart')) aria-current="page" @endif>
    <x-heroicon-o-shopping-cart class="size-6" aria-hidden="true" />
    <span class="store-cart-badge" data-cart-count="{{ $count }}" aria-live="polite" aria-atomic="true">{{ $count }}</span>
</a>

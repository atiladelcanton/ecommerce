<article class="store-product-card ct-product-card" data-product-id="{{ $product->id }}">
    <a href="{{ route('store.product', $product->slug) }}" class="store-product-card-link">
        <div class="ct-product-image">
            @if($product->images->first())
                <img src="{{ Storage::disk('public')->url($product->images->first()->path) }}" alt="{{ $product->images->first()->alt_text ?: $product->name }}" loading="lazy" width="500" height="500">
            @else
                <span class="ct-product-empty"><x-heroicon-o-photo class="size-10" aria-hidden="true" /> Sem imagem</span>
            @endif
            @if($product->has_promotion)<span class="store-discount">-{{ $product->discount_percentage }}%</span>@endif
        </div>
        <div class="ct-product-copy">
            <p class="ct-product-category">{{ $product->category->name }}</p>
            <h3>{{ $product->name }}</h3>
            <div class="ct-product-availability"><x-store.availability-badge :availability="$product->availability" /></div>
            <x-store.price :product="$product" />
            <span class="ct-product-view">Ver detalhes <x-heroicon-o-arrow-up-right class="size-4" aria-hidden="true" /></span>
        </div>
    </a>
</article>

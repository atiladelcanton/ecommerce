<article class="store-product-card wh-product-card" data-product-id="{{ $product->id }}">
    <a href="{{ route('store.product', $product->slug) }}" class="store-product-card-link">
        <div class="wh-product-picture">
            @if($product->images->first())
                <img src="{{ Storage::disk('public')->url($product->images->first()->path) }}" alt="{{ $product->images->first()->alt_text ?: $product->name }}" loading="lazy" width="500" height="500">
            @else
                <div class="wh-product-no-image"><x-heroicon-o-photo class="size-10" aria-hidden="true" /><span>Imagem não disponível</span></div>
            @endif
            @if($product->has_promotion)<span class="store-discount">-{{ $product->discount_percentage }}%</span>@endif
        </div>
        <div class="wh-product-info">
            <span class="wh-product-category">{{ $product->category->name }}</span>
            <h3>{{ $product->name }}</h3>
            <div class="wh-product-availability"><x-store.availability-badge :availability="$product->availability" /></div>
            <x-store.price :product="$product" />
            <span class="wh-product-detail-link">Ver detalhes <x-heroicon-o-arrow-right class="size-4" aria-hidden="true" /></span>
        </div>
    </a>
</article>

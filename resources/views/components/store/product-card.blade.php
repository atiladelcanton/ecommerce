@props(['product'])
@if(\App\Support\Storefront\Template::is(2))
    @include('components.store.templates.warehouse.product-card', ['product' => $product])
@elseif(in_array(\App\Support\Storefront\Template::active(), [3, 4, 5], true))
    @include('components.store.templates.creative.product-card', ['product' => $product, 'theme' => \App\Support\Storefront\Template::active()])
@else
<article class="store-product-card group" data-product-id="{{ $product->id }}">
    <a href="{{ route('store.product', $product->slug) }}" class="store-product-card-link">
        <div class="store-product-card-image relative aspect-square bg-store-soft overflow-hidden">
            @if($product->images->first())
                <img src="{{ Storage::disk('public')->url($product->images->first()->path) }}" alt="{{ $product->images->first()->alt_text ?: $product->name }}" loading="lazy" width="500" height="500" class="size-full object-contain transition-transform duration-300 motion-safe:group-hover:scale-105">
            @else
                <div class="flex h-full flex-col items-center justify-center gap-3 text-store-muted"><x-heroicon-o-photo class="size-10 opacity-40" /><span class="text-xs">Imagem não disponível</span></div>
            @endif
            @if($product->has_promotion)<span class="store-discount">-{{ $product->discount_percentage }}%</span>@endif
        </div>
        <div class="store-product-card-body"><p class="text-xs text-store-muted">{{ $product->category->name }}</p><h3 class="mt-2 font-medium leading-snug min-h-12">{{ $product->name }}</h3><div class="my-3"><x-store.availability-badge :availability="$product->availability" /></div><x-store.price :product="$product" /></div>
    </a>
</article>

@endif

<x-store.layout :title="$product->name" :description="Str::limit(strip_tags($product->short_description ?: $product->description ?: $product->name), 160)" :canonical="route('store.product', $product->slug)" :image="$product->images->first() ? Storage::disk('public')->url($product->images->first()->path) : null">
    @push('head')<script type="application/ld+json">{!! json_encode($structuredData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>@endpush
    <div class="store-container">
        <x-store.breadcrumb :items="[['label' => 'Produtos', 'url' => route('store.products')], ['label' => $product->category->name, 'url' => route('store.products', ['categoria' => $product->category->slug])], ['label' => $product->name]]" />
        <section class="grid gap-10 lg:grid-cols-2 lg:gap-16 pt-3 pb-16">
            <x-store.product-gallery :product="$product" />
            <div class="lg:py-6"><p class="store-eyebrow">{{ $product->category->name }}</p><h1 class="text-3xl sm:text-4xl xl:text-5xl font-semibold tracking-tight leading-tight mt-4 mb-6">{{ $product->name }}</h1><x-store.availability-badge :availability="$product->availability" />
                @if($product->sku)<p class="text-xs text-store-muted mt-4">Código: {{ $product->sku }}</p>@endif
                <div class="border-y border-store-border py-7 my-7"><x-store.price :product="$product" large />@if($product->has_promotion)<p class="mt-3 text-sm font-medium text-store-red">Economize {{ $product->discount_percentage }}%</p>@endif</div>
                @if($product->short_description)<p class="text-store-muted whitespace-pre-line leading-7">{{ $product->short_description }}</p>@endif
                @if($quoteUrl)<a class="store-button mt-8 w-full sm:w-auto" href="{{ $quoteUrl }}" target="_blank" rel="noopener noreferrer"><x-heroicon-o-chat-bubble-oval-left-ellipsis class="size-5" /> Cotar pelo WhatsApp</a>@endif
            </div>
        </section>
        @if($product->description)<section class="store-section border-t border-store-border pt-10"><h2 class="store-title mb-6">Descrição do produto</h2><div class="whitespace-pre-line leading-8 text-store-muted max-w-5xl">{{ $product->description }}</div></section>@endif
        @if($youtubeEmbed)<section class="store-section"><h2 class="store-title mb-6">Veja a máquina em funcionamento</h2><iframe src="{{ $youtubeEmbed }}" title="Demonstração de {{ $product->name }}" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="fullscreen; picture-in-picture" allowfullscreen class="w-full aspect-video rounded-xl bg-store-soft border-0"></iframe></section>@endif
        @if($related->isNotEmpty())<section class="store-section"><div class="store-section-heading"><h2 class="store-title">Produtos relacionados</h2></div><div class="store-product-grid">@foreach($related as $item)<x-store.product-card :product="$item" />@endforeach</div></section>@endif
    </div>
</x-store.layout>

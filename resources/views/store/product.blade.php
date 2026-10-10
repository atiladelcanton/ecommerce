<x-store.layout :title="$product->name" :description="Str::limit(strip_tags($product->short_description ?: $product->description ?: $product->name), 160)" :canonical="route('store.product', $product->slug)" :image="$product->images->first() ? Storage::disk('public')->url($product->images->first()->path) : null">
    @push('head')
        <script type="application/ld+json">{!! json_encode($structuredData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endpush

    <div class="store-container store-product-detail">
        <x-store.breadcrumb :items="[['label' => 'Produtos', 'url' => route('store.products')], ['label' => $product->category->name, 'url' => route('store.products', ['categoria' => $product->category->slug])], ['label' => $product->name]]" />

        <section class="product-detail-main" aria-labelledby="product-title">
            <x-store.product-gallery :product="$product" />

            <div class="product-detail-info">
                <a class="store-eyebrow product-detail-category" href="{{ route('store.products', ['categoria' => $product->category->slug]) }}">{{ $product->category->name }}</a>
                <h1 id="product-title">{{ $product->name }}</h1>
                <x-store.availability-badge :availability="$product->availability" />

                <div class="product-detail-price">
                    @if($product->has_promotion)
                        <p class="product-detail-original">De: <del>{{ \App\Support\BrazilianMoney::display($product->regular_price) }}</del></p>
                        <p class="product-detail-current product-detail-promotion"><span class="product-detail-price-label">Por:</span> {{ \App\Support\BrazilianMoney::display($product->final_price) }}</p>
                        <p class="product-detail-savings">Economize {{ $product->discount_percentage }}%</p>
                    @else
                        <p class="product-detail-current">{{ \App\Support\BrazilianMoney::display($product->final_price) }}</p>
                    @endif
                </div>

                @if($product->short_description)
                    <p class="product-detail-summary">{{ $product->short_description }}</p>
                @endif

                <livewire:store.product-purchase :product-id="$product->id" :quote-url="$quoteUrl" :variant-quote-urls="$variantQuoteUrls" />

                <dl class="product-detail-meta">
                    @if($product->sku)<div><dt>SKU:</dt><dd>{{ $product->sku }}</dd></div>@endif
                    <div><dt>Categoria:</dt><dd><a href="{{ route('store.products', ['categoria' => $product->category->slug]) }}">{{ $product->category->name }}</a></dd></div>
                    <div><dt>Disponibilidade:</dt><dd>{{ $product->availability->label() }}</dd></div>
                </dl>
            </div>
        </section>

        @if($product->description)
            <section class="product-detail-section" aria-labelledby="product-description-title">
                <h2 id="product-description-title">Descrição</h2>
                <div class="product-detail-description">{!! \App\Support\Catalog\ProductDescription::html($product->description) !!}</div>
            </section>
        @endif

        @if($youtubeEmbed)
            <section class="product-detail-section" aria-labelledby="product-video-title">
                <h2 id="product-video-title">Veja o equipamento em funcionamento</h2>
                <iframe src="{{ $youtubeEmbed }}" title="Demonstração de {{ $product->name }}" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="fullscreen; picture-in-picture" allowfullscreen class="product-detail-video"></iframe>
            </section>
        @endif

        @if($related->isNotEmpty())
            <section class="product-detail-section" aria-labelledby="product-related-title">
                <h2 id="product-related-title">Você também pode gostar</h2>
                <div class="store-product-grid product-detail-related">
                    @foreach($related as $item)<x-store.product-card :product="$item" />@endforeach
                </div>
            </section>
        @endif
    </div>
</x-store.layout>

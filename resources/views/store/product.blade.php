<x-store.layout :title="$product->name" :description="Str::limit(strip_tags($product->short_description ?: $product->description ?: $product->name), 160)" :canonical="route('store.product', $product->slug)" :image="$product->images->first() ? Storage::disk('public')->url($product->images->first()->path) : null">
    @push('head')
        <script type="application/ld+json">{!! json_encode($structuredData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endpush

    <div class="store-container store-product-detail">
        <x-store.breadcrumb :items="[['label' => 'Produtos', 'url' => route('store.products')], ['label' => $product->category->name, 'url' => route('store.products', ['categoria' => $product->category->slug])], ['label' => $product->name]]" />

        <section class="product-detail-main" aria-labelledby="product-title">
            <x-store.product-gallery :product="$product" />

            <div class="product-detail-info" @if($product->variants->isNotEmpty()) x-data="{ selectedColor: null, quoteUrls: {{ Js::from($variantQuoteUrls) }} }" @endif>
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

                @if($product->variants->isNotEmpty())
                    <fieldset class="product-detail-colors" data-color-selector>
                        <legend>Cor</legend>
                        <p id="color-help">Selecione uma cor para solicitar sua cotação.</p>
                        <div class="product-detail-swatches">
                            @foreach($product->variants as $variant)
                                <label class="product-detail-swatch">
                                    <input type="radio" name="color" value="{{ $variant->id }}" x-model="selectedColor" required aria-describedby="color-help" class="sr-only">
                                    <span class="product-detail-color-dot" data-color="{{ $variant->value }}" aria-hidden="true"></span>
                                    <span>{{ $variant->value }}</span>
                                    <x-heroicon-m-check class="product-detail-color-check size-4" aria-hidden="true" />
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endif

                @if($quoteUrl || $variantQuoteUrls)
                    <a class="store-button product-detail-quote"
                        @if($product->variants->isNotEmpty())
                            :href="quoteUrls[selectedColor] || null"
                            :aria-disabled="!selectedColor"
                            aria-disabled="true"
                            aria-describedby="color-help"
                            @click="if (!selectedColor) $event.preventDefault()"
                        @else
                            href="{{ $quoteUrl }}"
                        @endif
                        target="_blank" rel="noopener noreferrer">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.52 3.48A11.91 11.91 0 0 0 12.04 0C5.43 0 .05 5.38.05 11.99c0 2.11.55 4.17 1.6 5.99L0 24l6.16-1.62a11.96 11.96 0 0 0 5.87 1.5h.01C18.65 23.88 24 18.5 24 11.89c0-3.2-1.24-6.2-3.48-8.41ZM12.04 21.86h-.01a9.91 9.91 0 0 1-5.05-1.38l-.36-.21-3.66.96.98-3.57-.24-.37a9.89 9.89 0 0 1-1.52-5.3c0-5.5 4.48-9.97 9.99-9.97a9.87 9.87 0 0 1 7.03 2.92 9.87 9.87 0 0 1 2.89 7.04c0 5.51-4.48 9.88-10.05 9.88Zm5.47-7.43c-.3-.15-1.77-.87-2.04-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.49-.89-.8-1.49-1.79-1.66-2.09-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.18.2-.3.3-.5.1-.2.05-.38-.03-.53-.07-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.21 5.08 4.5.71.3 1.26.48 1.7.62.71.22 1.36.19 1.87.11.57-.09 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35Z" /></svg>
                        Cotar pelo WhatsApp
                    </a>
                @endif

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

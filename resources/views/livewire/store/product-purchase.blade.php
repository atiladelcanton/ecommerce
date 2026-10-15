<div class="product-purchase">
    <div class="product-detail-price" aria-live="polite">
        @if($product->variants->isNotEmpty() && ! $selectedVariant)<p class="text-sm text-store-muted">A partir de</p>@endif
        @if($offer->pricing->hasPromotion())
            <p class="product-detail-original">De: <del>{{ \App\Support\BrazilianMoney::display($offer->pricing->regularPrice) }}</del></p>
        @endif
        <p @class(['product-detail-current', 'product-detail-promotion' => $offer->pricing->hasPromotion()])>@if($offer->pricing->hasPromotion())<span class="product-detail-price-label">Por:</span> @endif{{ \App\Support\BrazilianMoney::display($offer->pricing->finalPrice()) }}</p>
        @if($offer->pricing->hasPromotion())<p class="product-detail-savings">Economize {{ $offer->pricing->discountPercentage() }}%</p>@endif
    </div>
    @foreach($fields as $index => $field)
        <fieldset class="product-detail-colors" @if($field['name'] === 'Cor') data-color-selector @endif>
            <legend class="text-sm font-semibold">{{ $field['name'] }}</legend>
            @if($field['name'] === 'Cor')
                <p id="color-help" class="text-sm text-store-muted">Selecione uma cor para adicionar ao carrinho ou solicitar sua cotação.</p>
                <div class="product-detail-swatches">
                    @foreach($field['values'] as $value)
                        <label class="product-detail-swatch"><input type="radio" wire:model.live="selectedAttributes.{{ $index }}" name="color" value="{{ $value }}" class="sr-only"><span class="product-detail-color-dot" data-color="{{ $value }}" aria-hidden="true"></span><span>{{ $value }}</span><x-heroicon-m-check class="product-detail-color-check size-4" aria-hidden="true" /></label>
                    @endforeach
                </div>
            @else
            <select wire:model.live="selectedAttributes.{{ $index }}" class="mt-2 w-full rounded-lg border border-store-border p-3" aria-label="{{ $field['name'] }}">
                <option value="">Selecione {{ $field['name'] }}</option>
                @foreach($field['values'] as $value)<option value="{{ $value }}">{{ $value }}</option>@endforeach
            </select>
            @endif
        </fieldset>
    @endforeach
    @error('productVariantId')<p class="mt-2 text-sm text-store-red" role="alert">{{ $message }}</p>@enderror
    @if($selectedVariant)<p class="mt-3 text-sm text-store-muted">{{ $selectedVariant->name }}: {{ $selectedVariant->value }}@if($available !== null) · {{ $available }} disponível(is)@endif</p>@endif
    <button type="button" wire:click="add" wire:loading.attr="disabled" wire:target="add" class="store-button product-detail-add" @disabled(! $canPurchase)>
        <x-heroicon-o-shopping-cart class="size-5" aria-hidden="true" />
        <span wire:loading.remove wire:target="add">Adicionar ao carrinho</span>
        <span wire:loading wire:target="add">Adicionando…</span>
    </button>
    @if(! $canPurchase)<p class="store-cart-feedback">{{ $product->variants->isNotEmpty() && ! $selectedVariant ? 'Selecione uma combinação disponível.' : 'Produto sem estoque disponível para compra.' }}</p>@endif
    @if($quoteUrl || $variantQuoteUrls)
        <a class="store-button product-detail-quote"
            @if($product->variants->isNotEmpty())
                href="{{ $selectedVariant ? ($variantQuoteUrls[$selectedVariant->id] ?? '#') : '#' }}"
                aria-disabled="{{ $selectedVariant ? 'false' : 'true' }}"
                @if(! $selectedVariant) onclick="event.preventDefault()" @endif
            @else
                href="{{ $quoteUrl }}"
            @endif
            target="_blank" rel="noopener noreferrer">
            <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.52 3.48A11.91 11.91 0 0 0 12.04 0C5.43 0 .05 5.38.05 11.99c0 2.11.55 4.17 1.6 5.99L0 24l6.16-1.62a11.96 11.96 0 0 0 5.87 1.5h.01C18.65 23.88 24 18.5 24 11.89c0-3.2-1.24-6.2-3.48-8.41ZM12.04 21.86h-.01a9.91 9.91 0 0 1-5.05-1.38l-.36-.21-3.66.96.98-3.57-.24-.37a9.89 9.89 0 0 1-1.52-5.3c0-5.5 4.48-9.97 9.99-9.97a9.87 9.87 0 0 1 7.03 2.92 9.87 9.87 0 0 1 2.89 7.04c0 5.51-4.48 9.88-10.05 9.88Zm5.47-7.43c-.3-.15-1.77-.87-2.04-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.49-.89-.8-1.49-1.79-1.66-2.09-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.18.2-.3.3-.5.1-.2.05-.38-.03-.53-.07-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.21 5.08 4.5.71.3 1.26.48 1.7.62.71.22 1.36.19 1.87.11.57-.09 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35Z" /></svg>
            Cotar pelo WhatsApp
        </a>
    @endif
</div>

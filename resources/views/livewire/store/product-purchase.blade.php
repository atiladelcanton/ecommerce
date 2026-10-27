<div class="product-purchase">
    <div class="product-detail-price" aria-live="polite">
        @if($product->variants->isNotEmpty() && ! $selectedVariant)
            <p class="text-sm text-store-muted">A partir de</p>
        @endif
        @if($offer->pricing->hasPromotion())
            <p class="product-detail-original">De: <del>{{ \App\Support\BrazilianMoney::display($offer->pricing->regularPrice) }}</del></p>
        @endif
        <p @class(['product-detail-current', 'product-detail-promotion' => $offer->pricing->hasPromotion()])>
            @if($offer->pricing->hasPromotion())<span class="product-detail-price-label">Por:</span> @endif{{ \App\Support\BrazilianMoney::display($offer->pricing->finalPrice()) }}
        </p>
        @if($offer->pricing->hasPromotion())
            <p class="product-detail-savings">Economize {{ $offer->pricing->discountPercentage() }}%</p>
        @endif
    </div>

    @if($selectionMode === 'combinations')
        <section class="product-variant-picker" aria-labelledby="variant-picker-title">
            <div class="product-variant-heading">
                <h2 id="variant-picker-title">Escolha a configuração</h2>
                <p>Selecione a opção completa em um clique.</p>
            </div>
            <div class="product-combination-list" x-data="{ expanded: false }">
                @foreach($combinations as $combination)
                    <button
                        type="button"
                        wire:key="product-combination-{{ $combination['id'] }}"
                        wire:click="selectCombination({{ $combination['id'] }})"
                        class="product-combination-option"
                        aria-pressed="{{ $selectedVariant?->id === $combination['id'] ? 'true' : 'false' }}"
                        @disabled(! $combination['available'])
                        @if(! $combination['available']) title="Opção indisponível" @endif
                        @if($loop->index >= 8) x-show="expanded" x-cloak @endif
                    >
                        <span class="product-variant-indicator" aria-hidden="true"><x-heroicon-m-check class="size-3.5" /></span>
                        <span class="product-combination-content">
                            <span class="product-combination-attributes">
                                @foreach($combination['attributes'] as $attribute)
                                    <span><span class="product-combination-attribute-name">{{ $attribute['name'] }}:</span> {{ $attribute['value'] }}</span>
                                @endforeach
                            </span>
                            @unless($combination['available'])<span class="product-variant-unavailable">Indisponível</span>@endunless
                        </span>
                        <span class="product-combination-price">{{ \App\Support\BrazilianMoney::display($combination['price']) }}</span>
                    </button>
                @endforeach
                @if(count($combinations) > 8)
                    <button type="button" class="product-combination-more" @click="expanded = ! expanded" :aria-expanded="expanded.toString()">
                        <span x-text="expanded ? 'Mostrar menos opções' : 'Ver todas as {{ count($combinations) }} opções'">Ver todas as {{ count($combinations) }} opções</span>
                        <x-heroicon-o-chevron-down class="size-4" x-bind:class="expanded ? 'rotate-180' : ''" aria-hidden="true" />
                    </button>
                @endif
            </div>
        </section>
    @else
        @foreach($fields as $index => $field)
            <fieldset class="product-variant-group" @if($field['name'] === 'Cor') data-color-selector @endif>
                <legend>
                    {{ $field['name'] }}
                    @if(filled($selectedAttributes[$index] ?? null))
                        <span class="product-variant-selected">{{ $selectedAttributes[$index] }}</span>
                    @endif
                </legend>
                @if($index > 0 && blank($selectedAttributes[$index - 1] ?? null))
                    <p class="product-variant-hint">Escolha {{ $fields[$index - 1]['name'] }} primeiro para ver as opções.</p>
                @else
                    <div class="product-variant-options">
                        @foreach($field['values'] as $option)
                            <button
                                type="button"
                                wire:key="product-attribute-{{ $index }}-{{ $loop->index }}"
                                wire:click="selectOption({{ $index }}, @js($option['value']))"
                                class="product-variant-option"
                                aria-pressed="{{ ($selectedAttributes[$index] ?? null) === $option['value'] ? 'true' : 'false' }}"
                                @disabled(! $option['available'])
                                @unless($option['available']) title="Opção indisponível" @endunless
                            >
                                @if($field['name'] === 'Cor')
                                    <span class="product-detail-color-dot" data-color="{{ $option['value'] }}" aria-hidden="true"></span>
                                @endif
                                <span>{{ $option['value'] }}</span>
                                @if(($selectedAttributes[$index] ?? null) === $option['value'])
                                    <x-heroicon-m-check class="product-variant-check size-4" aria-hidden="true" />
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endif
            </fieldset>
        @endforeach
    @endif

    @error('productVariantId')<p class="mt-2 text-sm text-store-red" role="alert">{{ $message }}</p>@enderror

    @if($selectedVariant)
        <div class="product-variant-confirmation" role="status">
            <x-heroicon-m-check-circle class="size-5 shrink-0" aria-hidden="true" />
            <span><strong>Opção selecionada</strong>
                @if($available !== null)
                    <span class="product-variant-stock">· {{ $available > 0 ? $available.' em estoque' : 'Sem estoque' }}</span>
                @endif
            </span>
        </div>
    @endif

    <button type="button" wire:click="add" wire:loading.attr="disabled" wire:target="add,selectOption,selectCombination" class="store-button product-detail-add" @disabled(! $canPurchase)>
        <x-heroicon-o-shopping-cart class="size-5" aria-hidden="true" />
        <span wire:loading.remove wire:target="add">Adicionar ao carrinho</span>
        <span wire:loading wire:target="add">Adicionando…</span>
    </button>
    @if(! $canPurchase)
        <p class="store-cart-feedback">
            @if($product->variants->isNotEmpty() && ! $selectedVariant)
                {{ $combinations === [] && $fields === [] ? 'Nenhuma opção disponível no momento.' : 'Escolha uma opção disponível para comprar.' }}
            @else
                Produto sem estoque disponível para compra.
            @endif
        </p>
    @endif

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

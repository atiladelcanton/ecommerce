<div>
    @if($cart['stockError'] ?? null)
        <div class="store-cart-error" role="alert">{{ $cart['stockError'] }}</div>
    @endif
    @if($errors->any())
        <div class="store-cart-error" role="alert">{{ $errors->first() }}</div>
    @endif

    @if($cart['items'] === [])
        <div class="store-cart-empty">
            <x-heroicon-o-shopping-cart class="size-12 mx-auto" aria-hidden="true" />
            <p>Seu carrinho está vazio.</p>
            <a href="{{ route('store.products') }}" class="store-button">Ver produtos</a>
        </div>
    @else
        <div class="store-cart-grid">
            <section aria-label="Itens do carrinho" class="min-w-0">
                <table class="store-cart-table">
                    <thead><tr><th scope="col">Produto</th><th scope="col">Preço</th><th scope="col">Quantidade</th><th scope="col">Subtotal</th><th scope="col">Remover</th></tr></thead>
                    <tbody>
                        @foreach($cart['items'] as $key => $line)
                            <tr wire:key="cart-{{ $key }}">
                                <td class="store-cart-product">
                                    <a href="{{ route('store.product', $line['product']->slug) }}" class="store-cart-image" tabindex="-1" aria-hidden="true">
                                        @if($image = $line['product']->images->first())
                                            <img src="{{ Storage::disk('public')->url($image->path) }}" alt="" width="88" height="88" loading="lazy">
                                        @else
                                            <x-heroicon-o-photo class="size-8" />
                                        @endif
                                    </a>
                                    <div>
                                        <a href="{{ route('store.product', $line['product']->slug) }}" class="store-cart-name">{{ $line['product']->name }}</a>
                                        @if($line['variant'])<p class="store-cart-color">{{ $line['variant']->name }}: {{ $line['variant']->value }}</p>@endif
                                    </div>
                                </td>
                                <td data-label="Preço">{{ \App\Support\BrazilianMoney::display($line['unitPrice']) }}</td>
                                <td data-label="Quantidade">
                                    <div class="store-cart-quantity" role="group" aria-label="Quantidade de {{ $line['product']->name }}{{ $line['variant'] ? ', '.$line['variant']->value : '' }}">
                                        <button type="button" wire:click="changeQuantity('{{ $key }}', -1)" wire:loading.attr="disabled" @disabled($line['item']->quantity === 1) aria-label="Diminuir quantidade de {{ $line['product']->name }}">−</button>
                                        <span aria-live="polite" aria-atomic="true">{{ $line['item']->quantity }}</span>
                                        <button type="button" wire:click="changeQuantity('{{ $key }}', 1)" wire:loading.attr="disabled" @disabled($line['item']->quantity === \App\Domain\Cart\CartItem::MAX_QUANTITY) aria-label="Aumentar quantidade de {{ $line['product']->name }}">+</button>
                                    </div>
                                </td>
                                <td data-label="Subtotal" class="font-semibold">{{ \App\Support\BrazilianMoney::display($line['subtotal']) }}</td>
                                <td class="store-cart-remove-cell"><button type="button" wire:click="remove('{{ $key }}')" wire:loading.attr="disabled" class="store-cart-remove" aria-label="Remover {{ $line['product']->name }}{{ $line['variant'] ? ', '.$line['variant']->value : '' }}"><x-heroicon-o-trash class="size-5" aria-hidden="true" /><span>Remover</span></button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="store-cart-actions">
                    <a href="{{ route('store.products') }}" class="store-text-link">← Continuar comprando</a>
                    <button type="button" wire:click="clear" wire:confirm="Deseja remover todos os itens do carrinho?" wire:loading.attr="disabled" class="store-cart-remove">Limpar carrinho</button>
                </div>
            </section>

            <aside class="store-cart-summary" aria-labelledby="cart-summary-title">
                <h2 id="cart-summary-title">Resumo do carrinho</h2>
                <div class="mb-5">
                    <label for="cart-coupon" class="store-label">Possui um cupom de desconto?</label>
                    <div class="mt-2 flex gap-2"><input id="cart-coupon" wire:model="couponInput" maxlength="64" placeholder="Ex.: BEMVINDO10" class="store-input min-w-0 flex-1" autocomplete="off">
                        <button type="button" wire:click="applyCoupon" class="store-button">Aplicar</button></div>
                    @if($couponCode)<p class="mt-2 text-sm">Cupom {{ $couponCode }} aplicado. <button type="button" wire:click="removeCoupon" class="store-text-link">Remover</button></p>@endif
                    @if($couponError)<p class="mt-2 text-sm text-store-red">{{ $couponError }}</p>@endif
                    @error('coupon')<p class="mt-2 text-sm text-store-red">{{ $message }}</p>@enderror
                </div>
                <dl>
                    <div><dt>Subtotal</dt><dd>{{ \App\Support\BrazilianMoney::display($cart['subtotal']) }}</dd></div>
                    <div><dt>Frete</dt><dd class="store-cart-shipping">Calculado no checkout</dd></div>
                    @if($discount > 0)<div><dt>Desconto ({{ $couponCode }})</dt><dd>- {{ \App\Support\BrazilianMoney::display($discount) }}</dd></div>@endif
                    <div class="store-cart-total"><dt>Total parcial</dt><dd aria-live="polite" aria-atomic="true">{{ \App\Support\BrazilianMoney::display($cart['subtotal'] - $discount) }}</dd></div>
                </dl>
                <a href="{{ url('/checkout') }}" class="store-button">Continuar para o checkout<x-heroicon-m-arrow-right class="size-4" aria-hidden="true" /></a>
            </aside>
        </div>
    @endif
</div>

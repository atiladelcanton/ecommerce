<div>
    @if($summary['cart']['items'] === [])
        <div class="store-cart-empty">
            <x-heroicon-o-shopping-cart class="size-12 mx-auto" aria-hidden="true" />
            <p>Seu carrinho está vazio.</p>
            <a href="{{ route('store.products') }}" class="store-button">Ver produtos</a>
        </div>
    @else
        <form wire:submit="submit" class="grid items-start gap-8 xl:grid-cols-[minmax(0,1fr)_380px]">
            <div class="grid min-w-0 gap-6">
                @if($errors->any())
                    <div class="rounded-lg border border-red-100 bg-red-50 p-4 text-sm text-store-red-dark" role="alert">{{ $errors->first() }}</div>
                @endif

                <section class="rounded-xl border border-store-border bg-white p-5 sm:p-7" aria-labelledby="checkout-customer-heading">
                    <h2 id="checkout-customer-heading" class="mb-6 text-lg font-semibold">Dados do cliente</h2>
                    @auth('customer')
                        <p class="mb-5 rounded-lg bg-store-soft p-3 text-sm text-store-muted">Usando os dados da sua conta. Você pode alterá-los em <a class="store-text-link" href="{{ route('customer.profile') }}">Meus dados</a>.</p>
                    @endauth
                    <div class="grid gap-x-5 gap-y-3 sm:grid-cols-2">
                        <x-store.checkout-field name="name" label="Nome" autocomplete="name" class="sm:col-span-2" :readonly="auth('customer')->check()" />
                        <x-store.checkout-field name="email" label="E-mail" type="email" autocomplete="email" inputmode="email" :readonly="auth('customer')->check()" />
                        <x-store.checkout-field name="phone" label="Telefone" type="tel" autocomplete="tel" inputmode="tel" :maxlength="16" mask="phone" :readonly="auth('customer')->check()" />
                        <x-store.checkout-field name="document" label="CPF/CNPJ" inputmode="numeric" :maxlength="18" mask="document" class="sm:col-span-2" :readonly="auth('customer')->check()" />
                    </div>
                </section>

                <section class="rounded-xl border border-store-border bg-white p-5 sm:p-7" aria-labelledby="checkout-address-heading">
                    <h2 id="checkout-address-heading" class="mb-2 text-lg font-semibold">Endereço de entrega</h2>
                    @auth('customer')
                        @if($savedAddresses !== [])
                            <p class="mb-5 text-sm text-store-muted">Escolha um endereço salvo ou use um novo endereço.</p>
                            <div class="mb-6 grid gap-3 md:grid-cols-2">
                                @foreach($savedAddresses as $address)
                                    <label class="cursor-pointer rounded-xl border p-4 transition has-checked:border-store-red has-checked:bg-red-50/30">
                                        <div class="flex items-start gap-3">
                                            <input class="mt-1 accent-store-red" type="radio" wire:model.live="addressChoice" value="saved:{{ $address['id'] }}">
                                            <span class="min-w-0 text-sm">
                                                <span class="flex flex-wrap items-center gap-2 font-semibold">{{ $address['label'] }} @if($address['is_default'])<span class="rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-store-red">Principal</span>@endif</span>
                                                <span class="mt-1 block text-store-muted">{{ $address['street'] }}, {{ $address['number'] }}@if($address['complement']) - {{ $address['complement'] }}@endif</span>
                                                <span class="block text-store-muted">{{ $address['neighborhood'] }} · {{ $address['city'] }}/{{ $address['state'] }}</span>
                                            </span>
                                        </div>
                                    </label>
                                @endforeach
                                <label class="cursor-pointer rounded-xl border border-dashed border-store-border p-4 transition has-checked:border-store-red has-checked:bg-red-50/30">
                                    <span class="flex items-center gap-3 text-sm font-semibold"><input class="accent-store-red" type="radio" wire:model.live="addressChoice" value="new"> + Usar novo endereço</span>
                                </label>
                            </div>
                        @else
                            <p class="mb-5 text-sm text-store-muted">Você ainda não possui endereços salvos. Este primeiro endereço poderá ser salvo como principal.</p>
                        @endif
                    @endauth

                    @if(!auth('customer')->check() || $addressChoice === 'new')
                        <div class="grid gap-x-5 gap-y-3 sm:grid-cols-2">
                            <x-store.checkout-field name="postal_code" label="CEP" autocomplete="postal-code" inputmode="numeric" :maxlength="9" mask="postalCode" :blur="true">
                                <p wire:loading wire:target="form.postal_code" class="text-xs text-store-muted" role="status">Buscando endereço...</p>
                                <p wire:loading.remove wire:target="form.postal_code" class="text-xs leading-5 text-store-muted" role="status" aria-live="polite">{{ $postalCodeMessage }}</p>
                            </x-store.checkout-field>
                            <x-store.checkout-field name="street" label="Rua" autocomplete="address-line1" class="sm:col-span-2" />
                            <x-store.checkout-field name="number" label="Número" :maxlength="30" />
                            <x-store.checkout-field name="complement" label="Complemento" autocomplete="address-line2" :required="false" />
                            <x-store.checkout-field name="neighborhood" label="Bairro" />
                            <x-store.checkout-field name="city" label="Cidade" autocomplete="address-level2" />
                            <div>
                                <label for="checkout-state" class="store-label">Estado</label>
                                <select id="checkout-state" name="state" wire:model="form.state" autocomplete="address-level1" class="store-input" required aria-describedby="checkout-state-error" @error('state') aria-invalid="true" @enderror>
                                    <option value="">Selecione</option>
                                    @foreach($states as $state)<option value="{{ $state }}">{{ $state }}</option>@endforeach
                                </select>
                                <p id="checkout-state-error" class="mt-2 text-sm text-store-red">@error('state'){{ $message }}@enderror</p>
                            </div>
                        </div>
                        @auth('customer')
                            <div class="mt-5 grid gap-2 text-sm">
                                <label class="flex items-center gap-2"><input type="checkbox" wire:model.live="saveNewAddress" class="accent-store-red"> Salvar este endereço na minha conta</label>
                                @if($saveNewAddress)
                                    <label class="flex items-center gap-2"><input type="checkbox" wire:model="makeDefaultAddress" class="accent-store-red"> Tornar endereço principal</label>
                                @endif
                            </div>
                        @endauth
                    @else
                        @php($selected = collect($savedAddresses)->firstWhere('id', (int) str_replace('saved:', '', $addressChoice)))
                        @if($selected)
                            <div class="rounded-xl bg-store-soft p-4 text-sm">
                                <p class="font-semibold">{{ $selected['label'] }}</p>
                                <p class="mt-1 text-store-muted">{{ $selected['street'] }}, {{ $selected['number'] }}@if($selected['complement']) - {{ $selected['complement'] }}@endif</p>
                                <p class="text-store-muted">{{ $selected['neighborhood'] }} · {{ $selected['city'] }}/{{ $selected['state'] }} · {{ \App\Support\BrazilianData::postalCode($selected['postal_code']) }}</p>
                            </div>
                        @endif
                    @endif

                    <fieldset class="mt-6 border-t border-store-border pt-6">
                        <legend class="pt-6 text-sm font-semibold">Forma de entrega</legend>
                        <div class="mt-3 grid gap-3">
                            @foreach($summary['shippingOptions'] as $option)
                                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-store-border p-4 has-checked:border-store-red">
                                    <input type="radio" wire:model.live="form.shipping_option" name="shipping_option" value="{{ $option->id }}" required class="mt-1 accent-store-red" aria-describedby="shipping-option-error">
                                    <span class="min-w-0 flex-1"><span class="block text-sm font-semibold">{{ $option->name }}</span><span class="mt-1 block text-xs leading-5 text-store-muted">{{ $option->carrierName }}</span><span class="mt-1 block text-xs leading-5 text-store-muted">{{ $option->description }}</span></span>
                                    <span class="shrink-0 text-sm font-semibold">{{ $option->fulfillmentMode === 'pickup' ? 'Grátis' : \App\Support\BrazilianMoney::display($option->price) }}</span>
                                </label>
                            @endforeach
                            @if($summary['shippingOptions'] === [] && !$summary['shippingError'])
                                <div class="rounded-lg border border-dashed border-store-border bg-store-soft p-4 text-sm text-store-muted">
                                    Informe o CEP de entrega para consultar as opções e prazos disponíveis.
                                </div>
                            @endif
                        </div>
                        <p id="shipping-option-error" class="mt-2 text-sm text-store-red" role="status">{{ $summary['shippingError'] }}@error('shipping_option'){{ $message }}@enderror</p>
                        @if($summary['canRequestShippingQuote'])
                            <div class="mt-4 rounded-lg border border-store-border bg-store-soft p-4">
                                <p class="text-sm">Solicite a cotação da entrega. Seus itens serão reservados por 24 horas e você receberá pelo atendimento um link para revisar o frete e concluir a compra.</p>
                                <button type="button" wire:click="requestShippingQuote" wire:loading.attr="disabled" class="store-button mt-3">Solicitar cotação de entrega</button>
                            </div>
                        @endif
                        @if($summary['shippingError'] && $summary['shippingContact'])
                            <a class="store-text-link mt-2 inline-flex" href="https://wa.me/{{ preg_replace('/\D/', '', $summary['shippingContact']) }}?text={{ rawurlencode('Olá! Preciso consultar o frete dos produtos do meu carrinho.') }}" target="_blank" rel="noopener noreferrer">Consultar entrega com a loja</a>
                        @endif
                    </fieldset>
                </section>

                <section class="rounded-xl border border-store-border bg-white p-5 sm:p-7" aria-labelledby="checkout-payment-heading">
                    <h2 id="checkout-payment-heading" class="mb-3 text-lg font-semibold">Pagamento</h2>
                    <p class="text-sm leading-6 text-store-muted">@if(app(\App\Infrastructure\Payments\PaymentGatewayRegistry::class)->active()) Você será encaminhado ao Asaas para concluir sua compra. O pedido será considerado pago somente após confirmação da cobrança. Seus itens ficarão reservados até o pagamento ser confirmado, reprovado ou a cobrança expirar. @else Ambiente de demonstração: nenhuma cobrança real será realizada. @endif</p>
                </section>
                <a href="{{ route('store.cart') }}" class="store-text-link">← Voltar ao carrinho</a>
            </div>

            <aside class="min-w-0 rounded-xl border border-store-border bg-store-soft p-5 sm:p-6 xl:sticky xl:top-28" aria-labelledby="checkout-summary-heading">
                <h2 id="checkout-summary-heading" class="mb-6 text-lg font-semibold">Seu pedido</h2>
                <ul class="grid gap-5">
                    @foreach($summary['cart']['items'] as $key => $line)
                        <li wire:key="checkout-item-{{ $key }}" class="flex justify-between gap-4 text-sm">
                            <div class="min-w-0"><p class="font-semibold wrap-anywhere">{{ $line['product']->name }}</p>
                                @if($line['variant'])<p class="mt-1 text-xs text-store-muted">{{ $line['variant']->name }}: {{ $line['variant']->value }}</p>@endif
                                <p class="mt-1 text-xs text-store-muted">{{ $line['item']->quantity }} × {{ \App\Support\BrazilianMoney::display($line['unitPrice']) }}</p>
                            </div>
                            <span class="shrink-0">{{ \App\Support\BrazilianMoney::display($line['subtotal']) }}</span>
                        </li>
                    @endforeach
                </ul>
                <dl class="mt-6 border-t border-store-border pt-4 text-sm">
                    <div class="flex justify-between gap-4 py-2"><dt>Subtotal</dt><dd>{{ \App\Support\BrazilianMoney::display($summary['cart']['subtotal']) }}</dd></div>
                    <div class="flex justify-between gap-4 py-2"><dt>Frete</dt><dd>{{ $summary['selectedShipping'] ? \App\Support\BrazilianMoney::display($summary['selectedShipping']->price) : 'Selecione a entrega' }}</dd></div>
                    <div class="mt-4 flex justify-between gap-4 border-t border-store-border pt-5 text-lg font-semibold"><dt>Total</dt><dd aria-live="polite">{{ $summary['selectedShipping'] && $summary['totals'] ? \App\Support\BrazilianMoney::display($summary['totals']->total) : 'A definir' }}</dd></div>
                </dl>
                @if($summary['totalError'])<p class="mt-4 text-sm text-store-red" role="alert">{{ $summary['totalError'] }}</p>@endif
                @if($summary['canRequestShippingQuote'] && ! $summary['selectedShipping'])
                    <button type="button" wire:click="requestShippingQuote" wire:loading.attr="disabled" class="store-button mt-6 w-full">Solicitar cotação de entrega</button>
                    <p class="mt-3 text-sm text-store-muted">Você revisará o frete e o total antes do pagamento. Nenhuma cobrança será realizada nesta solicitação.</p>
                @else
                <button type="submit" wire:loading.attr="disabled" wire:target="submit" @disabled($summary['shippingError'] || $summary['totalError'] || !$summary['selectedShipping']) class="store-button mt-6 w-full disabled:cursor-wait disabled:opacity-50">
                    <span wire:loading.remove wire:target="submit">{{ app(\App\Infrastructure\Payments\PaymentGatewayRegistry::class)->active() ? 'Continuar para o pagamento' : 'Finalizar pedido de teste' }}</span>
                    <span wire:loading wire:target="submit">Finalizando pedido…</span>
                </button>
                @endif
                <p class="mt-3 text-center text-xs leading-5 text-store-muted">{{ auth('customer')->check() ? 'Este pedido será vinculado à sua conta.' : 'Você pode finalizar sem criar uma conta e criar seu acesso depois.' }}</p>
            </aside>
        </form>
    @endif
</div>

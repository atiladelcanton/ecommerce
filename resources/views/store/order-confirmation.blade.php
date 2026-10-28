<x-store.layout title="Pedido recebido">
    <div class="store-container py-10 pb-20">
        <div class="mb-8 flex items-start gap-4">
            <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-green-50 text-store-green"><x-heroicon-o-check class="size-6" aria-hidden="true" /></span>
            <div><h1 class="store-title">{{ $order->shipping_pricing_status === \App\Domain\Shipping\Enums\ShippingPricingStatus::AWAITING_QUOTE ? 'Solicitação de entrega recebida' : 'Pedido realizado com sucesso' }}</h1><p class="mt-3 text-store-muted">Número do pedido: <strong class="text-store-ink">{{ $order->number }}</strong></p></div>
        </div>
        @if(app()->environment(['local', 'development']) && $order->payments->contains('is_demo', true))
            <p class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-store-amber" role="status">Este pedido utilizou pagamento simulado.</p>
        @endif
        <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_380px]">
            <section class="min-w-0 rounded-xl border border-store-border bg-white p-5 sm:p-7" aria-labelledby="confirmation-summary-heading">
                <h2 id="confirmation-summary-heading" class="mb-6 text-lg font-semibold">Resumo do pedido</h2>
                <ul class="divide-y divide-store-border">
                    @foreach($order->items as $item)
                        <li class="flex flex-wrap justify-between gap-3 py-4 text-sm">
                            <div class="min-w-0"><p class="font-semibold wrap-anywhere">{{ $item->product_name }}</p>
                                @if($item->product_sku)<p class="mt-1 text-xs text-store-muted">SKU: {{ $item->product_sku }}</p>@endif
                                @if($item->variant_value)<p class="mt-1 text-xs text-store-muted">{{ $item->variant_name }}: {{ $item->variant_value }}</p>@endif
                                <p class="mt-1 text-xs text-store-muted">{{ $item->quantity }} × {{ \App\Support\BrazilianMoney::display($item->unit_price) }}</p>
                            </div>
                            <span class="shrink-0 font-semibold">{{ \App\Support\BrazilianMoney::display($item->total) }}</span>
                        </li>
                    @endforeach
                </ul>
                <dl class="mt-4 border-t border-store-border pt-4 text-sm">
                    <div class="flex justify-between gap-4 py-2"><dt>Subtotal</dt><dd>{{ \App\Support\BrazilianMoney::display($order->subtotal_cents) }}</dd></div>
                    @if($order->discount_cents > 0)<div class="flex justify-between gap-4 py-2 text-green-700"><dt>Desconto @if($order->coupon_code)({{ $order->coupon_code }})@endif</dt><dd>− {{ \App\Support\BrazilianMoney::display($order->discount_cents) }}</dd></div>@endif
                    <div class="flex justify-between gap-4 py-2"><dt>Frete</dt><dd>{{ $order->formattedShipping() }}</dd></div>
                    <div class="mt-3 flex justify-between gap-4 border-t border-store-border pt-4 text-lg font-semibold"><dt>Total</dt><dd>{{ $order->formattedTotal() }}</dd></div>
                </dl>
            </section>
            <div class="grid min-w-0 gap-6">
                <section class="rounded-xl border border-store-border bg-white p-5 sm:p-6" aria-labelledby="confirmation-customer-heading">
                    <h2 id="confirmation-customer-heading" class="mb-4 font-semibold">Dados do cliente</h2>
                    <dl class="grid gap-3 text-sm wrap-anywhere">
                        <div><dt class="text-xs text-store-muted">Nome</dt><dd>{{ $order->customer_name }}</dd></div>
                        <div><dt class="text-xs text-store-muted">E-mail</dt><dd>{{ $order->customer_email }}</dd></div>
                        <div><dt class="text-xs text-store-muted">Telefone</dt><dd>{{ $order->customer_phone }}</dd></div>
                        <div><dt class="text-xs text-store-muted">CPF/CNPJ</dt><dd>{{ $order->customer_document }}</dd></div>
                    </dl>
                </section>
                <section class="rounded-xl border border-store-border bg-white p-5 sm:p-6" aria-labelledby="confirmation-delivery-heading">
                    <h2 id="confirmation-delivery-heading" class="mb-4 font-semibold">Endereço e forma de entrega</h2>
                    <address class="text-sm leading-6 not-italic wrap-anywhere">
                        {{ $order->address->street }}, {{ $order->address->number }}<br>
                        @if($order->address->complement){{ $order->address->complement }}<br>@endif
                        {{ $order->address->neighborhood }}<br>
                        {{ $order->address->city }} / {{ $order->address->state }}<br>
                        CEP: {{ $order->address->postal_code }}
                    </address>
                    <p class="mt-4 text-sm font-semibold">{{ $order->shipping_name }}</p>
                    <p class="mt-1 text-xs leading-5 text-store-muted">{{ $order->shipping_description }}</p>
                </section>
                <section class="rounded-xl border border-store-border bg-store-soft p-5 sm:p-6" aria-labelledby="confirmation-payment-heading">
                    <h2 id="confirmation-payment-heading" class="mb-3 font-semibold">Pagamento</h2>
                    @if($order->payment_status === \App\Domain\Payment\Enums\PaymentStatus::APPROVED)
                        <p class="text-sm text-store-green">Aprovado — ambiente de demonstração</p>
                    @else
                        <p class="text-sm text-store-muted">Aguardando cotação do frete. Nenhum pagamento foi realizado.</p>
                        <p class="mt-3 text-sm">Seus itens de pronta entrega ficam reservados por 24 horas. A SABA informará o frete e enviará o link para concluir a compra.</p>
                    @endif
                </section>
            </div>
        </div>
        @if($order->shipping_pricing_status === \App\Domain\Shipping\Enums\ShippingPricingStatus::AWAITING_QUOTE)
            @if($shippingContactUrl)
                <a href="{{ $shippingContactUrl }}" target="_blank" rel="noopener noreferrer" class="store-button mt-6">Conversar sobre a entrega pelo WhatsApp</a>
            @endif
        @endif
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('store.products') }}" class="store-button-secondary">Continuar comprando</a>
            @if(auth('customer')->check())
                <a href="{{ route('customer.order', $order->number) }}" class="store-button">Acompanhar este pedido</a>
            @else
                <a href="{{ route('customer.register', ['email' => $order->customer_email]) }}" class="store-button">Criar conta e acompanhar pedido</a>
            @endif
        </div>
    </div>
</x-store.layout>

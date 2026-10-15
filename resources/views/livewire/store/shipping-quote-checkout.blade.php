<div class="mx-auto max-w-3xl space-y-6">
    <h1 class="store-title">{{ $order->status === \App\Domain\Order\Enums\OrderStatus::CANCELLED ? 'Pedido cancelado' : ($order->payment_status === \App\Domain\Payment\Enums\PaymentStatus::APPROVED ? 'Pedido confirmado' : 'Revise e conclua seu pedido') }}</h1>
    <p class="text-store-muted">Pedido {{ $order->number }} · {{ $order->customer_name }}</p>
    @if($errors->any())<div class="store-cart-error" role="alert">{{ $errors->first() }}</div>@endif
    <section class="rounded-xl border border-store-border bg-white p-6">
        <h2 class="font-semibold">Produtos</h2>
        @foreach($order->items as $item)
            <div class="flex justify-between gap-4 border-b border-store-border py-4">
                <div><p>{{ $item->product_name }}</p><p class="text-sm text-store-muted">{{ $item->variant_name }} {{ $item->variant_value }}</p><p class="text-sm">{{ $item->quantity }} × {{ \App\Support\BrazilianMoney::display($item->unit_price) }}</p></div>
                <strong>{{ \App\Support\BrazilianMoney::display($item->total) }}</strong>
            </div>
        @endforeach
    </section>
    <section class="rounded-xl border border-store-border bg-white p-6">
        <h2 class="font-semibold">Entrega combinada</h2>
        <p class="mt-3">{{ $order->carrier }} · {{ $order->shipping_name }}</p>
        <p class="mt-2 text-store-muted">{{ $order->shipping_description }}</p>
        <p class="mt-2 whitespace-pre-line">{{ $order->latestShippingQuote->instructions }}</p>
        @if($order->address)<p class="mt-4 text-sm">{{ $order->address->street }}, {{ $order->address->number }} · {{ $order->address->city }}/{{ $order->address->state }} · CEP {{ \App\Support\BrazilianData::postalCode($order->address->postal_code) }}</p>@endif
        <p class="mt-3 text-sm text-store-muted">Cotação válida até {{ $order->latestShippingQuote->expires_at->format('d/m/Y H:i') }}. A reserva inicial de estoque dura 24 horas; após esse prazo, a disponibilidade é conferida novamente.</p>
    </section>
    <section class="rounded-xl border border-store-border bg-white p-6">
        <dl class="space-y-3"><div class="flex justify-between"><dt>Produtos</dt><dd>{{ \App\Support\BrazilianMoney::display($order->subtotal_cents) }}</dd></div><div class="flex justify-between"><dt>Frete</dt><dd>{{ $order->formattedShipping() }}</dd></div><div class="flex justify-between border-t border-store-border pt-3 text-lg font-bold"><dt>Total</dt><dd>{{ $order->formattedTotal() }}</dd></div></dl>
        @if($order->status === \App\Domain\Order\Enums\OrderStatus::CANCELLED)
            <p class="mt-5 text-store-red">Este pedido foi cancelado. Entre em contato com a SABA.</p>
        @elseif($order->payment_status === \App\Domain\Payment\Enums\PaymentStatus::APPROVED)
            <p class="mt-5 font-semibold text-store-green" role="status">Pagamento aprovado — ambiente de demonstração.</p>
        @elseif($order->status === \App\Domain\Order\Enums\OrderStatus::RECEIVED)
            <p class="mt-5 text-sm text-store-muted">Pagamento de demonstração. Nenhuma cobrança real será realizada.</p>
            <button type="button" wire:click="confirmPayment" wire:loading.attr="disabled" class="store-button mt-5 w-full">Confirmar pedido e pagamento de teste</button>
        @else
            <p class="mt-5 text-store-red">Este pedido não está disponível para pagamento. Entre em contato com a SABA.</p>
        @endif
    </section>
</div>

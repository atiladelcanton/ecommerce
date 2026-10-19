<div wire:poll.15s class="grid gap-6" aria-live="polite">
    @if($order->shipping_pricing_status && $order->status !== \App\Domain\Order\Enums\OrderStatus::CANCELLED)
        <section class="mb-5 rounded-xl border border-store-border bg-white p-5">
            <h2 class="font-semibold">{{ $order->shipping_pricing_status->label() }}</h2>
            <p class="mt-2 text-sm text-store-muted">A reserva inicial de estoque dura 24 horas. Depois disso, a disponibilidade será conferida novamente ao concluir a compra.</p>
            @if($order->latestShippingQuote && $order->latestShippingQuote->expires_at->isFuture() && $order->status === \App\Domain\Order\Enums\OrderStatus::RECEIVED)
                <a class="store-button mt-4" href="{{ app(\App\Actions\Shipping\GetOrderShippingQuoteLinkAction::class)->execute($order->latestShippingQuote) }}">Revisar frete e concluir pedido</a>
            @endif
        </section>
    @endif

    <section class="store-account-card">
        <div class="store-order-tracking-header">
            <div class="min-w-0">
                <p class="store-eyebrow">Pedido {{ $order->number }}</p>
                <h1 class="store-title mt-2">Acompanhe seu pedido</h1>
                <p class="store-order-live-note mt-2">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 11a8 8 0 1 0-2.34 5.66" />
                        <path d="M20 5v6h-6" />
                    </svg>
                    Atualização automática a cada 15 segundos
                </p>
            </div>
            <div class="store-order-current-status">
                <span class="store-order-current-status-icon store-order-current-status-icon--{{ $order->status->value }}" aria-hidden="true">
                    <x-store.order-status-icon :status="$order->status" />
                </span>
                <div class="min-w-0">
                    <span class="store-order-status store-order-status--{{ $order->status->value }}">{{ $order->status->label() }}</span>
                    <p class="mt-1 text-xs text-store-muted">Atualizado em {{ $order->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        @if($order->status === \App\Domain\Order\Enums\OrderStatus::CANCELLED)
            <div class="mt-6 rounded-lg border border-red-100 bg-red-50 p-4 text-sm text-store-red-dark">Este pedido foi cancelado. Se precisar de ajuda, entre em contato com a loja.</div>
        @else
            <ol class="store-order-progress mt-8" style="--order-step-count: {{ count($steps) }}" aria-label="Progresso do pedido">
                @foreach($steps as $step)
                    <li
                        class="{{ $step['complete'] ? 'is-complete' : '' }} {{ $step['current'] ? 'is-current' : '' }} {{ $step['status'] === \App\Domain\Order\Enums\OrderStatus::DELIVERED ? 'is-delivered' : '' }}"
                        @if($step['current']) aria-current="step" @endif
                    >
                        <span class="store-order-progress-dot" aria-hidden="true">
                            <x-store.order-status-icon :status="$step['status']" />
                        </span>
                        <span class="store-order-progress-copy">
                            <span class="store-order-progress-label">{{ $step['label'] }}</span>
                            <span class="store-order-progress-description">{{ $step['description'] }}</span>
                        </span>
                    </li>
                @endforeach
            </ol>
        @endif
    </section>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
        <section class="store-account-card min-w-0">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-lg font-semibold">Itens do pedido</h2>
                <span class="text-sm text-store-muted">{{ $order->items->sum('quantity') }} item(ns)</span>
            </div>
            <ul class="mt-4 divide-y divide-store-border">
                @foreach($order->items as $item)
                    <li class="flex justify-between gap-4 py-4 text-sm">
                        <div class="min-w-0">
                            <p class="font-semibold wrap-anywhere">{{ $item->product_name }}</p>
                            @if($item->variant_value)<p class="mt-1 text-xs text-store-muted">{{ $item->variant_name }}: {{ $item->variant_value }}</p>@endif
                            <p class="mt-1 text-xs text-store-muted">{{ $item->quantity }} × {{ \App\Support\BrazilianMoney::display($item->unit_price) }}</p>
                        </div>
                        <strong class="shrink-0">{{ \App\Support\BrazilianMoney::display($item->total) }}</strong>
                    </li>
                @endforeach
            </ul>
            <dl class="mt-4 border-t border-store-border pt-4 text-sm">
                <div class="flex justify-between py-2"><dt>Subtotal</dt><dd>{{ \App\Support\BrazilianMoney::display($order->subtotal_cents) }}</dd></div>
                <div class="flex justify-between py-2"><dt>Frete</dt><dd>{{ $order->formattedShipping() }}</dd></div>
                <div class="mt-2 flex justify-between border-t border-store-border pt-4 text-lg font-semibold"><dt>Total</dt><dd>{{ $order->formattedTotal() }}</dd></div>
            </dl>
        </section>

        <aside class="grid gap-6">
            <section class="store-account-card">
                <h2 class="font-semibold">Entrega</h2>
                <p class="mt-3 text-sm font-medium">{{ $order->shipping_name }}</p>
                <p class="mt-1 text-xs leading-5 text-store-muted">{{ $order->shipping_description }}</p>
                @if($order->status === \App\Domain\Order\Enums\OrderStatus::READY_FOR_PICKUP)
                    <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">Seu pedido está pronto para retirada. Entre em contato com a SABA se precisar combinar o horário.</div>
                @endif
                @if($order->status === \App\Domain\Order\Enums\OrderStatus::READY_FOR_SHIPPING)
                    <div class="mt-4 rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm text-blue-900">Seu pedido está embalado, com etiqueta gerada e aguardando a postagem na transportadora.</div>
                @endif
                @php($shipmentAlert = $order->shipments->sortByDesc('id')->first(fn($shipment) => in_array($shipment->status->value, ['undelivered', 'paused', 'suspended'], true)))
                @if($shipmentAlert)
                    <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">A transportadora informou uma ocorrência no envio ({{ $shipmentAlert->status->label() }}). A loja está acompanhando a situação.</div>
                @endif
                @if(\App\Domain\Shipping\OrderShipping::mode($order) === 'pickup')
                    <p class="mt-3 text-sm text-store-muted">{{ $order->shipping_quote_snapshot['metadata']['address'] ?? '' }}</p>
                    <p class="mt-2 text-sm text-store-muted">{{ $order->shipping_quote_snapshot['metadata']['instructions'] ?? '' }}</p>
                @endif
                @foreach($order->shipments->sortByDesc('attempt')->unique('fulfillment_key') as $shipment)
                    <div class="mt-4 rounded-lg border border-store-border p-4 text-sm">
                        <p class="font-semibold">{{ $shipment->carrier_name }} · {{ $shipment->service_name }}</p>
                        <p class="mt-1">{{ $shipment->status->label() }}</p>
                        @if($shipment->tracking_code)<p class="mt-2">Rastreio: {{ $shipment->tracking_code }}</p>@endif
                        @if($shipment->tracking_url)<a class="store-text-link mt-2 inline-flex" href="{{ $shipment->tracking_url }}" target="_blank" rel="noopener noreferrer">Rastrear entrega ↗</a>@endif
                        <ul class="mt-3 grid gap-2 text-xs text-store-muted">
                            @foreach($shipment->events->sortByDesc('occurred_at')->take(5) as $event)
                                @if($event->status && \App\Domain\Shipping\Enums\ShipmentStatus::tryFrom($event->status))
                                    <li>{{ $event->occurred_at?->format('d/m/Y H:i') }} · {{ \App\Domain\Shipping\Enums\ShipmentStatus::from($event->status)->label() }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endforeach
                @if($order->tracking_code)
                    <div class="mt-4 rounded-lg bg-store-soft p-4">
                        @if($order->carrier)<p class="mb-2 text-xs text-store-muted">Transportadora: <strong class="text-store-ink">{{ $order->carrier }}</strong></p>@endif
                        <p class="text-xs text-store-muted">Código de rastreio</p>
                        <p class="mt-1 font-semibold">{{ $order->tracking_code }}</p>
                        @if($order->tracking_url)<a class="store-text-link mt-2 inline-flex" href="{{ $order->tracking_url }}" target="_blank" rel="noopener noreferrer">Rastrear entrega ↗</a>@endif
                    </div>
                @endif
                @if($order->address)
                    <address class="mt-4 text-sm leading-6 not-italic text-store-muted">
                        {{ $order->address->street }}, {{ $order->address->number }}<br>
                        @if($order->address->complement){{ $order->address->complement }}<br>@endif
                        {{ $order->address->neighborhood }}<br>
                        {{ $order->address->city }} / {{ $order->address->state }} — CEP {{ \App\Support\BrazilianData::postalCode($order->address->postal_code) }}
                    </address>
                @endif
            </section>

            <section class="store-account-card">
                <h2 class="font-semibold">Pagamento</h2>
                <p class="mt-3"><span class="store-payment-status store-payment-status--{{ $order->payment_status->value }}">{{ $order->payment_status->label() }}</span></p>
                @if($order->payments->last()?->is_demo)
                    <p class="mt-3 text-xs leading-5 text-store-muted">Pagamento simulado no ambiente de demonstração.</p>
                @endif
            </section>

            <section class="store-account-card">
                <h2 class="font-semibold">Histórico</h2>
                <ol class="mt-4 grid gap-4">
                    @foreach($order->statusHistories->sortByDesc('created_at') as $history)
                        <li class="relative border-l border-store-border pl-4 text-sm">
                            <span class="absolute -left-1.5 top-1 size-3 rounded-full bg-store-red" aria-hidden="true"></span>
                            <p class="font-medium">{{ $history->to_status instanceof \App\Domain\Order\Enums\OrderStatus ? $history->to_status->label() : ucfirst((string) $history->to_status) }}</p>
                            <p class="mt-1 text-xs leading-5 text-store-muted">{{ $history->description }}</p>
                            <time class="mt-1 block text-xs text-store-muted">{{ $history->created_at->format('d/m/Y H:i') }}</time>
                        </li>
                    @endforeach
                </ol>
            </section>
        </aside>
    </div>
</div>

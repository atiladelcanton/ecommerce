<x-filament-panels::page>
    @php($order = $this->record->loadMissing(['items', 'address', 'payments', 'statusHistories.user', 'shipments.events']))

    @if($order->shipping_pricing_status)
        <x-filament::section :heading="$order->shipping_pricing_status->label()">
            <p class="text-sm text-gray-600">A solicitação reserva estoque por 24 horas. Depois desse prazo, a compra depende de uma nova verificação de disponibilidade.</p>
            @if($order->latestShippingQuote && $order->latestShippingQuote->expires_at->isFuture() && ! $order->latestShippingQuote->superseded_at && $order->status === \App\Domain\Order\Enums\OrderStatus::RECEIVED)
                @php($quoteLink = app(\App\Actions\Shipping\GetOrderShippingQuoteLinkAction::class)->execute($order->latestShippingQuote))
                <div class="mt-3 flex flex-wrap items-center gap-3" x-data="{ copied: false }" data-quote-link="{{ $quoteLink }}">
                    <x-filament::button color="gray" x-on:click="navigator.clipboard.writeText($root.dataset.quoteLink).then(() => copied = true)"><span x-text="copied ? 'Link copiado' : 'Copiar link para o cliente'">Copiar link para o cliente</span></x-filament::button>
                    <a class="text-sm text-primary-600" href="{{ app(\App\Actions\Shipping\GetCustomerQuoteContactLinkAction::class)->execute($order->latestShippingQuote) }}" target="_blank" rel="noopener noreferrer">Compartilhar pelo WhatsApp</a>
                </div>
            @endif
            @if($order->shippingQuotes->isNotEmpty())
                <div class="mt-4 space-y-2 text-sm">
                    @foreach($order->shippingQuotes as $quote)
                        <p>Revisão {{ $quote->revision }} · {{ $quote->carrier_name }} · {{ \App\Support\BrazilianMoney::display($quote->price_cents) }} · Válida até {{ $quote->expires_at->format('d/m/Y H:i') }}{{ $quote->superseded_at ? ' · Substituída' : '' }}{{ $quote->accepted_at ? ' · Aceita' : '' }}</p>
                    @endforeach
                </div>
            @endif
        </x-filament::section>
    @endif
    <div class="grid gap-6 xl:grid-cols-12">
        <section class="fi-section xl:col-span-4 p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Pedido</p>
                    <h2 class="mt-1 text-xl font-semibold text-zinc-900">{{ $order->number }}</h2>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $order->status->color() === 'success' ? 'bg-green-50 text-green-700' : ($order->status->color() === 'warning' ? 'bg-amber-50 text-amber-700' : ($order->status->color() === 'danger' ? 'bg-red-50 text-red-700' : 'bg-zinc-100 text-zinc-700')) }}">{{ $order->status->label() }}</span>
            </div>
            <dl class="mt-6 grid gap-4 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-zinc-500">Criado em</dt><dd class="font-medium text-zinc-900">{{ $order->created_at->format('d/m/Y H:i') }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-zinc-500">Pagamento</dt><dd class="font-medium text-zinc-900">{{ $order->payment_status->label() }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-zinc-500">Itens</dt><dd class="font-medium text-zinc-900">{{ $order->items->sum('quantity') }}</dd></div>
                <div class="flex justify-between gap-4 border-t border-zinc-200 pt-4"><dt class="font-medium text-zinc-700">Total</dt><dd class="text-lg font-bold text-zinc-950">{{ $order->formattedTotal() }}</dd></div>
            </dl>
        </section>

        <section class="fi-section xl:col-span-4 p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Cliente</p>
            <h2 class="mt-1 text-lg font-semibold text-zinc-900">{{ $order->customer_name }}</h2>
            <dl class="mt-5 grid gap-3 text-sm">
                <div><dt class="text-zinc-500">E-mail</dt><dd class="mt-1 font-medium text-zinc-900">{{ $order->customer_email }}</dd></div>
                <div><dt class="text-zinc-500">Telefone</dt><dd class="mt-1 font-medium text-zinc-900">{{ \App\Support\BrazilianData::phone($order->customer_phone) }}</dd></div>
                <div><dt class="text-zinc-500">CPF/CNPJ</dt><dd class="mt-1 font-medium text-zinc-900">{{ \App\Support\BrazilianData::document($order->customer_document) }}</dd></div>
            </dl>
        </section>

        <section class="fi-section xl:col-span-4 p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Entrega</p>
            <h2 class="mt-1 text-lg font-semibold text-zinc-900">{{ $order->shipping_name }}</h2>
            <p class="mt-2 text-sm text-zinc-500">{{ $order->shipping_description }}</p>
            @if($order->shipping_provider)<p class="mt-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">Provider: {{ str_replace('_', ' ', ucfirst($order->shipping_provider)) }}</p>@endif
            @if($order->address)
                <address class="mt-5 text-sm leading-6 not-italic text-zinc-700">
                    {{ $order->address->street }}, {{ $order->address->number }}<br>
                    @if($order->address->complement){{ $order->address->complement }}<br>@endif
                    {{ $order->address->neighborhood }} — {{ $order->address->city }}/{{ $order->address->state }}<br>
                    CEP {{ \App\Support\BrazilianData::postalCode($order->address->postal_code) }}
                </address>
            @endif
            @if($order->tracking_code)
                <div class="mt-5 rounded-lg bg-zinc-50 p-4 text-sm">
                    @if($order->carrier)<p><span class="text-zinc-500">Transportadora:</span> <strong>{{ $order->carrier }}</strong></p>@endif
                    <p class="mt-1"><span class="text-zinc-500">Rastreio:</span> <strong>{{ $order->tracking_code }}</strong></p>
                    @if($order->tracking_url)<a href="{{ $order->tracking_url }}" class="mt-2 inline-flex font-semibold text-red-600 hover:text-red-700" target="_blank" rel="noopener noreferrer">Abrir rastreio ↗</a>@endif
                </div>
            @endif
        </section>

        <section class="fi-section xl:col-span-8 p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Produtos</p>
                    <h2 class="mt-1 text-lg font-semibold text-zinc-900">Itens do pedido</h2>
                </div>
                <span class="text-sm text-zinc-500">{{ $order->items->sum('quantity') }} item(ns)</span>
            </div>
            <div class="mt-5 divide-y divide-zinc-200">
                @foreach($order->items as $item)
                    <div class="grid gap-3 py-4 sm:grid-cols-[minmax(0,1fr)_90px_130px] sm:items-center">
                        <div>
                            <p class="font-semibold text-zinc-900">{{ $item->product_name }}</p>
                            @if($item->product_sku)<p class="mt-1 text-xs text-zinc-500">SKU {{ $item->product_sku }}</p>@endif
                            @if($item->variant_value)<p class="mt-1 text-xs text-zinc-500">{{ $item->variant_name }}: {{ $item->variant_value }}</p>@endif
                            @if($item->shipping_measurements_snapshot)<p class="mt-1 text-xs text-zinc-500">Embalagem: {{ $item->shipping_measurements_snapshot['weight'] ?? '—' }} kg · {{ $item->shipping_measurements_snapshot['length'] ?? '—' }} × {{ $item->shipping_measurements_snapshot['width'] ?? '—' }} × {{ $item->shipping_measurements_snapshot['height'] ?? '—' }} cm</p>@endif
                        </div>
                        <p class="text-sm text-zinc-600">{{ $item->quantity }} × {{ \App\Support\BrazilianMoney::display($item->unit_price) }}</p>
                        <p class="text-right font-semibold text-zinc-900">{{ \App\Support\BrazilianMoney::display($item->total) }}</p>
                    </div>
                @endforeach
            </div>
            <dl class="ml-auto mt-5 max-w-sm border-t border-zinc-200 pt-4 text-sm">
                <div class="flex justify-between py-1.5"><dt class="text-zinc-500">Subtotal</dt><dd>{{ \App\Support\BrazilianMoney::display($order->subtotal_cents) }}</dd></div>
                <div class="flex justify-between py-1.5"><dt class="text-zinc-500">Frete</dt><dd>{{ $order->formattedShipping() }}</dd></div>
                @if($order->discount_cents > 0)<div class="flex justify-between py-1.5 text-green-700"><dt>Desconto</dt><dd>- {{ \App\Support\BrazilianMoney::display($order->discount_cents) }}</dd></div>@endif
                <div class="mt-2 flex justify-between border-t border-zinc-200 pt-3 text-base font-bold"><dt>Total</dt><dd>{{ $order->formattedTotal() }}</dd></div>
            </dl>
        </section>

        <section class="fi-section xl:col-span-4 p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Logística</p>
            <h2 class="mt-1 text-lg font-semibold text-zinc-900">Envios e etiquetas</h2>
            <div class="mt-5 grid gap-3">
                @forelse($order->shipments as $shipment)
                    <div class="rounded-lg border border-zinc-200 p-4 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <strong>{{ $shipment->carrier_name ?: ucfirst(str_replace('_', ' ', $shipment->provider)) }}</strong>
                            <span class="rounded-full bg-zinc-100 px-2 py-1 text-[11px] font-semibold text-zinc-600">{{ $shipment->status->label() }}</span>
                        </div>
                        @if($shipment->service_name)<p class="mt-2 text-zinc-500">{{ $shipment->service_name }}</p>@endif
                        @if($shipment->external_id)<p class="mt-1 break-all text-xs text-zinc-400">ID {{ $shipment->external_id }}</p>@endif
                        @if($shipment->tracking_code)<p class="mt-2"><span class="text-zinc-500">Rastreio:</span> <strong>{{ $shipment->tracking_code }}</strong></p>@endif
                        @if($shipment->operation_phase)<p class="mt-2 text-amber-700">Operação pendente de conciliação</p>@endif
                        @if($shipment->generated_at && !$shipment->cancelled_at)<a class="mt-2 inline-flex font-semibold text-red-600" href="{{ route('admin.shipping.labels.download', ['shipment' => $shipment, 'format' => $shipment->label_format ?? 'pdf']) }}">Imprimir etiqueta</a>@endif
                        @if($shipment->label_url)<a href="{{ $shipment->label_url }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex font-semibold text-red-600 hover:text-red-700">Abrir etiqueta ↗</a>@endif
                    </div>
                @empty
                    <p class="text-sm text-zinc-500">Nenhuma etiqueta gerada para este pedido.</p>
                @endforelse
            </div>
        </section>

        <section class="fi-section xl:col-span-4 p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Pagamentos</p>
            <h2 class="mt-1 text-lg font-semibold text-zinc-900">Tentativas de pagamento</h2>
            <div class="mt-5 grid gap-3">
                @forelse($order->payments as $payment)
                    <div class="rounded-lg border border-zinc-200 p-4 text-sm">
                        <div class="flex items-center justify-between gap-3"><strong>{{ ucfirst($payment->gateway) }}</strong><span class="text-xs font-semibold">{{ $payment->status->label() }}</span></div>
                        <p class="mt-2 text-zinc-500">{{ \App\Support\BrazilianMoney::display($payment->amount_cents) }}</p>
                        <p class="mt-1 break-all text-xs text-zinc-400">{{ $payment->external_id }}</p>
                    </div>
                @empty
                    <p class="text-sm text-zinc-500">Nenhuma tentativa registrada.</p>
                @endforelse
            </div>
        </section>

        <section class="fi-section xl:col-span-12 p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Histórico</p>
            <h2 class="mt-1 text-lg font-semibold text-zinc-900">Linha do tempo</h2>
            <ol class="mt-6 grid gap-0">
                @foreach($order->statusHistories->sortByDesc('created_at') as $history)
                    <li class="relative border-l border-zinc-200 pb-6 pl-6 last:pb-0">
                        <span class="absolute -left-1.5 top-1.5 size-3 rounded-full bg-red-600 ring-4 ring-white"></span>
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <strong class="text-sm text-zinc-900">{{ $history->to_status->label() }}</strong>
                            <time class="text-xs text-zinc-500">{{ $history->created_at->format('d/m/Y H:i') }}</time>
                        </div>
                        <p class="mt-1 text-sm text-zinc-600">{{ $history->description }}</p>
                        @if($history->user)<p class="mt-1 text-xs text-zinc-400">Alterado por {{ $history->user->name }}</p>@endif
                    </li>
                @endforeach
            </ol>
        </section>
    </div>
</x-filament-panels::page>

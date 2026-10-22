<x-filament-panels::page wire:poll.20s="refreshOrderStatus">
    @php($order = $this->record->loadMissing(['items', 'address', 'payments', 'statusHistories.user', 'shipments.events']))

    @if($blockingOperationModalOpen)
        <div
            class="fixed inset-0 z-[70] flex items-center justify-center bg-zinc-950/45 p-4"
            role="presentation"
            x-on:keydown.escape.window="$wire.dismissBlockingOperationModal()"
        >
            <section
                class="w-full max-w-xl rounded-2xl border border-zinc-200 bg-white shadow-2xl"
                role="dialog"
                aria-modal="true"
                aria-labelledby="blocking-operation-title"
                aria-describedby="blocking-operation-message"
            >
                <div class="flex items-start justify-between gap-4 border-b border-zinc-200 px-6 py-5">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wider text-red-600">Ação necessária</p>
                        <h2 id="blocking-operation-title" class="mt-1 text-lg font-semibold text-zinc-950">
                            {{ $blockingOperationTitle }}
                        </h2>
                    </div>
                    <button
                        type="button"
                        wire:click="dismissBlockingOperationModal"
                        class="inline-flex size-10 shrink-0 items-center justify-center rounded-lg text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-red-500"
                        aria-label="Fechar"
                    >
                        <span aria-hidden="true" class="text-2xl leading-none">&times;</span>
                    </button>
                </div>

                <div class="px-6 py-5">
                    <p id="blocking-operation-message" class="whitespace-pre-line text-sm leading-6 text-zinc-700">{{ $blockingOperationMessage }}</p>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-zinc-200 px-6 py-4 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        wire:click="dismissBlockingOperationModal"
                        class="inline-flex min-h-10 items-center justify-center rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-zinc-400"
                    >
                        Voltar ao pedido
                    </button>

                    @if($blockingOperationPrimaryUrl && $blockingOperationPrimaryLabel)
                        <a
                            href="{{ $blockingOperationPrimaryUrl }}"
                            class="inline-flex min-h-10 items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                        >
                            {{ $blockingOperationPrimaryLabel }}
                        </a>
                    @elseif($blockingOperationPrimaryAction && $blockingOperationPrimaryLabel)
                        <button
                            type="button"
                            wire:click="continueBlockingOperation"
                            class="inline-flex min-h-10 items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                        >
                            {{ $blockingOperationPrimaryLabel }}
                        </button>
                    @endif
                </div>
            </section>
        </div>
    @endif

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
            @if($order->status === \App\Domain\Order\Enums\OrderStatus::CANCELLED)
                <div class="mt-5 rounded-lg border border-red-100 bg-red-50 p-4 text-sm text-red-900">
                    <p class="font-semibold">Pedido cancelado{{ $order->cancelled_at ? ' em '.$order->cancelled_at->format('d/m/Y H:i') : '' }}</p>
                    @if($order->cancellation_reason)
                        <p class="mt-2 whitespace-pre-line text-red-800"><span class="font-medium">Motivo interno:</span> {{ $order->cancellation_reason }}</p>
                    @endif
                    @if($order->payment_status === \App\Domain\Payment\Enums\PaymentStatus::APPROVED)
                        <p class="mt-2 text-xs leading-5 text-red-700">O pagamento continua marcado como aprovado. O cancelamento do pedido não realiza estorno automaticamente.</p>
                    @endif
                </div>
            @endif
        </section>

        <section class="fi-section xl:col-span-4 p-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Cliente</p>
                    <h2 class="mt-1 text-lg font-semibold text-zinc-900">{{ $order->customer_name }}</h2>
                </div>
                @if($order->customer)
                    <a href="{{ \App\Filament\Resources\Customers\CustomerResource::getUrl('view', ['record' => $order->customer]) }}" class="text-sm font-semibold text-red-600 hover:text-red-700">Ver cliente</a>
                @endif
            </div>
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
                @if($order->discount_cents > 0)<div class="flex justify-between py-1.5 text-green-700"><dt>Cupom {{ $order->coupon_code ?: 'de desconto' }}</dt><dd>- {{ \App\Support\BrazilianMoney::display($order->discount_cents) }}</dd></div>@endif
                <div class="mt-2 flex justify-between border-t border-zinc-200 pt-3 text-base font-bold"><dt>Total</dt><dd>{{ $order->formattedTotal() }}</dd></div>
            </dl>
        </section>

        <section class="fi-section xl:col-span-4 p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Logística</p>
            <h2 class="mt-1 text-lg font-semibold text-zinc-900">Envios e etiquetas</h2>
            @php($currentShipments = \App\Domain\Shipping\OrderShipping::currentShipments($order))
            @if($order->status === \App\Domain\Order\Enums\OrderStatus::CANCELLED && $currentShipments->contains(fn($shipment) => !$shipment->cancelled_at))
                <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    <p class="font-semibold">O pedido foi cancelado, mas existe envio logístico ativo.</p>
                    <p class="mt-1 text-xs leading-5">O cancelamento comercial não cancela automaticamente etiquetas ou postagens. Use a ação <strong>Cancelar etiqueta</strong> quando o provedor ainda permitir.</p>
                </div>
            @endif
            <div class="mt-5 grid gap-3">
                @forelse($currentShipments as $shipmentIndex => $shipment)
                    <div class="rounded-lg border border-zinc-200 p-4 text-sm">
                        @if($currentShipments->count() > 1)
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">Volume {{ $shipmentIndex + 1 }} de {{ $currentShipments->count() }}</p>
                        @endif
                        <div class="flex items-center justify-between gap-3">
                            <strong>{{ $shipment->carrier_name ?: ucfirst(str_replace('_', ' ', $shipment->provider)) }}</strong>
                            <span class="rounded-full bg-zinc-100 px-2 py-1 text-[11px] font-semibold text-zinc-600">{{ $shipment->status->label() }}</span>
                        </div>
                        @if($shipment->service_name)<p class="mt-2 text-zinc-500">{{ $shipment->service_name }}</p>@endif
                        @if($order->shipping_document_type)
                            <p class="mt-2"><span class="text-zinc-500">Documento:</span> <strong>{{ $order->shipping_document_type->label() }}</strong></p>
                            @if($order->shipping_document_type === \App\Domain\Shipping\Enums\ShipmentDocumentType::INVOICE && $order->invoice_key)
                                <p class="mt-1 break-all text-xs text-zinc-500">NF-e {{ $order->invoice_key }}</p>
                            @elseif($order->shipping_document_type === \App\Domain\Shipping\Enums\ShipmentDocumentType::CONTENT_DECLARATION && $order->dce_key)
                                <p class="mt-1 break-all text-xs text-zinc-500">DC-e externa {{ $order->dce_key }}</p>
                            @endif
                        @endif
                        @if($shipment->tracking_code)<p class="mt-2"><span class="text-zinc-500">Rastreio:</span> <strong>{{ $shipment->tracking_code }}</strong></p>@endif

                        @if($shipment->operation_phase)
                            <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-amber-900">
                                <p class="font-semibold">Não foi possível confirmar a última operação</p>
                                <p class="mt-1 text-xs leading-5">O envio foi pausado temporariamente para evitar uma cobrança duplicada. Use <strong>Verificar envio</strong> nas ações do pedido.</p>
                            </div>
                            <details class="mt-3 text-xs text-zinc-500">
                                <summary class="cursor-pointer font-medium text-zinc-600">Ver detalhes técnicos</summary>
                                <dl class="mt-2 grid gap-1 rounded-lg bg-zinc-50 p-3">
                                    <div><dt class="inline">Provedor:</dt> <dd class="inline">{{ str_replace('_', ' ', $shipment->provider) }}</dd></div>
                                    @if($shipment->external_id)<div><dt class="inline">Identificador externo:</dt> <dd class="inline break-all">{{ $shipment->external_id }}</dd></div>@endif
                                    <div><dt class="inline">Etapa pendente:</dt> <dd class="inline">{{ str_replace('_', ' ', $shipment->operation_phase) }}</dd></div>
                                </dl>
                            </details>
                        @endif

                        @if($shipment->status === \App\Domain\Shipping\Enums\ShipmentStatus::PURCHASED && filled(($shipment->metadata ?? [])['generation_requested_at'] ?? null))
                            <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-amber-900">
                                <p class="font-semibold">Etiqueta em processamento</p>
                                <p class="mt-1 text-xs leading-5">O Melhor Envio recebeu a solicitação de geração. Use <strong>Atualizar status da etiqueta</strong> até a geração ser confirmada.</p>
                            </div>
                        @endif

                        @if($shipment->generated_at && !$shipment->cancelled_at)
                            <div class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-sm">
                                <a class="inline-flex font-semibold text-red-600 hover:text-red-700" href="{{ route('admin.shipping.labels.open', ['shipment' => $shipment]) }}" target="_blank" rel="noopener noreferrer">Abrir / imprimir etiqueta ↗</a>
                                @if($shipment->environment === 'production')
                                    <a class="inline-flex font-semibold text-zinc-600 hover:text-zinc-900" href="{{ route('admin.shipping.labels.download', ['shipment' => $shipment, 'format' => 'pdf']) }}">Baixar PDF</a>
                                @endif
                            </div>
                            @if($shipment->environment === 'sandbox')
                                <p class="mt-2 text-xs leading-5 text-zinc-400">No ambiente de homologação, use <strong>Abrir / imprimir etiqueta</strong>. O download direto de arquivo é limitado pelo Melhor Envio para alguns serviços.</p>
                            @endif
                        @endif
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

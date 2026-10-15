<x-filament-panels::page>
    <div wire:poll.20s class="space-y-5">
        <section class="fi-section p-4 lg:p-5">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-6">
                <div class="xl:col-span-2">
                    <label class="mb-1 block text-xs font-semibold text-zinc-500">Buscar</label>
                    <input wire:model.live.debounce.350ms="search" type="search" placeholder="Pedido, cliente, e-mail ou documento" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-100">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-zinc-500">Pagamento</label>
                    <select wire:model.live="paymentStatus" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm">
                        <option value="">Todos</option>
                        @foreach($paymentStatuses as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-zinc-500">Entrega</label>
                    <select wire:model.live="shippingMethod" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm">
                        <option value="">Todas</option>
                        <option value="standard">Entrega padrão</option>
                        <option value="pickup">Retirada</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-zinc-500">De</label>
                    <input wire:model.live="fromDate" type="date" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-zinc-500">Até</label>
                    <input wire:model.live="toDate" type="date" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm">
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between gap-3 text-xs text-zinc-500">
                <button wire:click="resetFilters" type="button" class="font-semibold text-red-600 hover:text-red-700">Limpar filtros</button>
                @if($cancelledCount)<span>{{ $cancelledCount }} pedido(s) cancelado(s) fora do fluxo principal</span>@endif
            </div>
        </section>

        <div class="saba-kanban-grid overflow-x-auto pb-3">
            @foreach($columns as $column)
                @php($status = $column['status'])
                <section class="saba-kanban-column">
                    <header class="saba-kanban-column-header">
                        <div>
                            <p class="saba-kanban-column-title">{{ $status->label() }}</p>
                            <p class="saba-kanban-column-subtitle">{{ $column['orders']->count() }} pedido(s)</p>
                        </div>
                        <span class="saba-kanban-dot saba-kanban-dot--{{ $status->value }}"></span>
                    </header>

                    <div class="saba-kanban-stack">
                        @forelse($column['orders'] as $order)
                            <article class="saba-kanban-card" wire:key="kanban-order-{{ $order->id }}">
                                <a href="{{ \App\Filament\Resources\Orders\OrderResource::getUrl('view', ['record' => $order]) }}" class="block">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-bold text-zinc-950">{{ $order->number }}</p>
                                            <p class="mt-1 line-clamp-1 text-sm text-zinc-600">{{ $order->customer_name }}</p>
                                        </div>
                                        <span class="shrink-0 rounded-full bg-zinc-100 px-2 py-1 text-[11px] font-semibold text-zinc-600">{{ $order->items->sum('quantity') }} item(ns)</span>
                                    </div>
                                    <div class="mt-4 flex items-end justify-between gap-3">
                                        <div>
                                            <p class="text-xs text-zinc-400">{{ $order->created_at->diffForHumans() }}</p>
                                            <p class="mt-1 text-xs text-zinc-500">{{ $order->shipping_option_id === 'pickup' ? 'Retirada' : $order->shipping_name }}</p>
                                            @if($order->shipping_pricing_status)<p class="mt-1 text-xs font-medium text-amber-700">{{ $order->shipping_pricing_status->label() }}</p>@endif
                                        </div>
                                        <strong class="text-sm text-zinc-950">{{ $order->formattedTotal() }}</strong>
                                    </div>
                                </a>

                                <div class="mt-4 flex flex-wrap gap-2 border-t border-zinc-100 pt-3">
                                    @if($status === \App\Domain\Order\Enums\OrderStatus::PENDING)
                                        <span class="text-xs font-medium text-zinc-400">Aguardando pagamento</span>
                                    @elseif($status === \App\Domain\Order\Enums\OrderStatus::PAID)
                                        <button wire:click="moveOrder({{ $order->id }}, 'processing')" type="button" class="saba-kanban-action">Iniciar preparação</button>
                                    @elseif($status === \App\Domain\Order\Enums\OrderStatus::PROCESSING)
                                        @if(\App\Domain\Shipping\OrderShipping::mode($order) === 'pickup')
                                            <button wire:click="moveOrder({{ $order->id }}, 'ready_for_pickup')" type="button" class="saba-kanban-action">Pronto para retirada</button>
                                        @elseif(\App\Domain\Shipping\OrderShipping::mode($order) === 'automatic')
                                            <a href="{{ \App\Filament\Resources\Orders\OrderResource::getUrl('view', ['record' => $order]) }}" class="saba-kanban-action">Gerar etiqueta</a>
                                        @else
                                            <button wire:click="openShippingModal({{ $order->id }})" type="button" class="saba-kanban-action">Marcar enviado</button>
                                        @endif
                                    @elseif($status === \App\Domain\Order\Enums\OrderStatus::READY_FOR_SHIPPING)
                                        <p class="w-full text-xs text-zinc-500">{{ $order->shipping_quote_snapshot['carrier_name'] ?? $order->carrier }} · {{ $order->shipping_name }} @if($order->shipping_pricing_status)<span class="block text-xs text-amber-700">{{ $order->shipping_pricing_status->label() }}</span>@endif</p>
                                        @foreach($order->shipments->whereNotNull('generated_at')->whereNull('cancelled_at') as $shipment)
                                            <a class="saba-kanban-action" href="{{ route('admin.shipping.labels.download', ['shipment' => $shipment, 'format' => $shipment->label_format ?? 'pdf']) }}" target="_blank">Etiqueta gerada #{{ $shipment->id }} · Imprimir</a>
                                            @if($shipment->tracking_code)<span class="text-xs text-zinc-500">{{ $shipment->tracking_code }}</span>@endif
                                        @endforeach
                                        <a href="{{ \App\Filament\Resources\Orders\OrderResource::getUrl('view', ['record' => $order]) }}" class="saba-kanban-action">Imprimir etiqueta</a>
                                        <span class="text-xs font-medium text-zinc-400">Aguardando postagem</span>
                                    @elseif(in_array($status, [\App\Domain\Order\Enums\OrderStatus::SHIPPED, \App\Domain\Order\Enums\OrderStatus::READY_FOR_PICKUP], true))
                                        @if(\App\Domain\Shipping\OrderShipping::mode($order) !== 'automatic')
                                        <button wire:click="moveOrder({{ $order->id }}, 'delivered')" type="button" class="saba-kanban-action">Marcar entregue</button>
                                        @else
                                            <span class="text-xs text-zinc-500">Entrega confirmada pela transportadora</span>
                                        @endif
                                    @else
                                        <span class="text-xs font-medium text-green-700">Fluxo concluído</span>
                                    @endif

                                    @if(in_array($status, [\App\Domain\Order\Enums\OrderStatus::PENDING, \App\Domain\Order\Enums\OrderStatus::PAID, \App\Domain\Order\Enums\OrderStatus::PROCESSING], true))
                                        <button wire:click="cancel({{ $order->id }})" wire:confirm="Cancelar este pedido?" type="button" class="text-xs font-semibold text-zinc-400 transition hover:text-red-600">Cancelar</button>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="rounded-xl border border-dashed border-zinc-200 bg-white/60 px-4 py-8 text-center text-xs text-zinc-400">Nenhum pedido nesta etapa.</div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    </div>

    @if($showShippingModal)
        <div class="fixed inset-0 z-50 grid place-items-center bg-zinc-950/40 p-4" role="dialog" aria-modal="true" aria-label="Informar rastreio">
            <div class="w-full max-w-lg rounded-xl border border-zinc-200 bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Envio</p>
                        <h2 class="mt-1 text-xl font-semibold text-zinc-950">Informar rastreio</h2>
                        <p class="mt-1 text-sm text-zinc-500">O cliente verá essas informações imediatamente na área dele.</p>
                    </div>
                    <button wire:click="closeShippingModal" type="button" class="rounded-lg p-2 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700" aria-label="Fechar">✕</button>
                </div>
                <div class="mt-6 grid gap-4">
                    <label class="grid gap-1 text-sm font-medium text-zinc-700">Transportadora
                        <input wire:model="carrier" type="text" class="rounded-lg border border-zinc-200 px-3 py-2" placeholder="Ex.: Correios">
                        @error('carrier')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>
                    <label class="grid gap-1 text-sm font-medium text-zinc-700">Código de rastreio
                        <input wire:model="trackingCode" type="text" class="rounded-lg border border-zinc-200 px-3 py-2" placeholder="Código fornecido pela transportadora">
                        @error('trackingCode')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>
                    <label class="grid gap-1 text-sm font-medium text-zinc-700">Link de rastreio <span class="text-xs font-normal text-zinc-400">opcional</span>
                        <input wire:model="trackingUrl" type="url" class="rounded-lg border border-zinc-200 px-3 py-2" placeholder="https://...">
                        @error('trackingUrl')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="closeShippingModal" type="button" class="rounded-lg border border-zinc-200 px-4 py-2 text-sm font-semibold text-zinc-600 hover:bg-zinc-50">Cancelar</button>
                    <button wire:click="confirmShipping" wire:loading.attr="disabled" type="button" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60">Confirmar envio</button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>

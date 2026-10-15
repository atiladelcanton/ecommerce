<x-filament-widgets::widget>
    <section class="saba-dashboard-card saba-attention-card">
        <header class="saba-dashboard-card__header">
            <div>
                <p class="saba-dashboard-card__eyebrow">Operação</p>
                <h2 class="saba-dashboard-card__title">Pedidos que precisam de atenção</h2>
                <p class="saba-dashboard-card__description">Pagamentos aprovados e pedidos em preparação.</p>
            </div>

            <a href="{{ \App\Filament\Pages\OrdersKanban::getUrl() }}" class="saba-dashboard-link">
                Abrir Kanban
                <x-filament::icon icon="heroicon-m-arrow-up-right" class="saba-dashboard-link__icon" />
            </a>
        </header>

        <div class="saba-attention-list">
            @forelse($orders as $order)
                <a href="{{ \App\Filament\Resources\Orders\OrderResource::getUrl('view', ['record' => $order]) }}" class="saba-attention-row">
                    <div class="saba-attention-row__main">
                        <span class="saba-attention-row__number">{{ $order->number }}</span>
                        <span class="saba-attention-row__customer">{{ $order->customer_name }}</span>
                    </div>

                    <div class="saba-attention-row__meta">
                        <span>{{ $order->items_count }} {{ $order->items_count === 1 ? 'item' : 'itens' }}</span>
                        <span aria-hidden="true">•</span>
                        <span>{{ $order->created_at->diffForHumans() }}</span>
                    </div>

                    <div class="saba-attention-row__status">
                        <span class="saba-status-pill saba-status-pill--{{ $order->status->value }}">{{ $order->status->label() }}</span>
                    </div>

                    <strong class="saba-attention-row__total">{{ $order->formattedTotal() }}</strong>

                    <x-filament::icon icon="heroicon-m-chevron-right" class="saba-attention-row__chevron" />
                </a>
            @empty
                <div class="saba-dashboard-empty">
                    <span class="saba-dashboard-empty__icon">
                        <x-filament::icon icon="heroicon-o-check-circle" />
                    </span>
                    <div>
                        <strong>Operação em dia</strong>
                        <p>Nenhum pedido pago ou em preparação aguardando ação.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </section>
</x-filament-widgets::widget>

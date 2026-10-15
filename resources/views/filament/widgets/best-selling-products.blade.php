<x-filament-widgets::widget>
    <section class="saba-dashboard-card saba-best-sellers-card">
        <header class="saba-dashboard-card__header">
            <div>
                <p class="saba-dashboard-card__eyebrow">Catálogo</p>
                <h2 class="saba-dashboard-card__title">Produtos mais vendidos</h2>
                <p class="saba-dashboard-card__description">Ranking por quantidade em {{ $monthLabel }} considerando pedidos pagos.</p>
            </div>

            <a href="{{ \App\Filament\Resources\Products\ProductResource::getUrl() }}" class="saba-dashboard-link">
                Ver produtos
                <x-filament::icon icon="heroicon-m-arrow-up-right" class="saba-dashboard-link__icon" />
            </a>
        </header>

        <div class="saba-best-sellers-list">
            @forelse($products as $index => $product)
                <div class="saba-best-sellers-row">
                    <span class="saba-best-sellers-rank">{{ $index + 1 }}</span>

                    <div class="saba-best-sellers-main">
                        <strong class="saba-best-sellers-name">{{ $product->product_name }}</strong>
                        <span class="saba-best-sellers-meta">
                            {{ number_format((int) $product->total_quantity, 0, ',', '.') }} {{ (int) $product->total_quantity === 1 ? 'unidade' : 'unidades' }}
                        </span>
                    </div>

                    <strong class="saba-best-sellers-revenue">{{ \App\Support\BrazilianMoney::display((int) $product->revenue_cents) }}</strong>
                </div>
            @empty
                <div class="saba-dashboard-empty">
                    <span class="saba-dashboard-empty__icon">
                        <x-filament::icon icon="heroicon-o-chart-bar-square" />
                    </span>
                    <div>
                        <strong>Ainda sem ranking neste mês</strong>
                        <p>Os produtos mais vendidos aparecerão após os primeiros pagamentos aprovados.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </section>
</x-filament-widgets::widget>

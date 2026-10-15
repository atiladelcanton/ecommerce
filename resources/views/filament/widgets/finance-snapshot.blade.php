<x-filament-widgets::widget>
    <section class="saba-dashboard-card saba-finance-card">
        <header class="saba-dashboard-card__header">
            <div>
                <p class="saba-dashboard-card__eyebrow">Financeiro</p>
                <h2 class="saba-dashboard-card__title">Resumo do mês</h2>
                <p class="saba-dashboard-card__description">Movimentação registrada em {{ now()->locale('pt_BR')->translatedFormat('F') }}.</p>
            </div>

            <a href="{{ \App\Filament\Resources\CashFlowEntries\CashFlowEntryResource::getUrl() }}" class="saba-dashboard-link" aria-label="Abrir fluxo de caixa">
                Ver fluxo
                <x-filament::icon icon="heroicon-m-arrow-up-right" class="saba-dashboard-link__icon" />
            </a>
        </header>

        <div class="saba-finance-metrics">
            <div class="saba-finance-metric">
                <span class="saba-finance-metric__icon saba-finance-metric__icon--income">
                    <x-filament::icon icon="heroicon-o-arrow-trending-up" />
                </span>
                <div>
                    <span class="saba-finance-metric__label">Entradas</span>
                    <strong class="saba-finance-metric__value">{{ \App\Support\BrazilianMoney::display($income) }}</strong>
                </div>
            </div>

            <div class="saba-finance-metric">
                <span class="saba-finance-metric__icon saba-finance-metric__icon--expense">
                    <x-filament::icon icon="heroicon-o-arrow-trending-down" />
                </span>
                <div>
                    <span class="saba-finance-metric__label">Saídas</span>
                    <strong class="saba-finance-metric__value">{{ \App\Support\BrazilianMoney::display($expense) }}</strong>
                </div>
            </div>
        </div>

        <div class="saba-finance-balance {{ $balance < 0 ? 'saba-finance-balance--negative' : '' }}">
            <div>
                <span class="saba-finance-balance__label">Saldo do mês</span>
                <strong class="saba-finance-balance__value">{{ \App\Support\BrazilianMoney::display($balance) }}</strong>
            </div>
            <span class="saba-finance-balance__entries">{{ $entriesCount }} {{ $entriesCount === 1 ? 'lançamento' : 'lançamentos' }}</span>
        </div>
    </section>
</x-filament-widgets::widget>

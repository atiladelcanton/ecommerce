<x-filament-widgets::widget>
    <section class="saba-dashboard-card saba-outcomes-card">
        <header class="saba-dashboard-card__header">
            <div>
                <p class="saba-dashboard-card__eyebrow">Desfechos</p>
                <h2 class="saba-dashboard-card__title">Resumo de {{ $monthLabel }}</h2>
                <p class="saba-dashboard-card__description">Somente pedidos já entregues ou cancelados entram na taxa.</p>
            </div>
        </header>

        <div class="saba-outcomes-grid">
            <div class="saba-outcomes-metric saba-outcomes-metric--success">
                <span class="saba-outcomes-metric__label">Entregues</span>
                <strong class="saba-outcomes-metric__value">{{ number_format($delivered, 0, ',', '.') }}</strong>
            </div>

            <div class="saba-outcomes-metric saba-outcomes-metric--danger">
                <span class="saba-outcomes-metric__label">Cancelados</span>
                <strong class="saba-outcomes-metric__value">{{ number_format($cancelled, 0, ',', '.') }}</strong>
            </div>

            <div class="saba-outcomes-metric">
                <span class="saba-outcomes-metric__label">Taxa de cancelamento</span>
                <strong class="saba-outcomes-metric__value">
                    {{ $cancellationRate === null ? '—' : number_format($cancellationRate, 1, ',', '.').'%' }}
                </strong>
                @if($cancellationRateDelta !== null)
                    <span class="saba-outcomes-metric__hint {{ $cancellationRateDelta > 0 ? 'saba-outcomes-metric__hint--bad' : ($cancellationRateDelta < 0 ? 'saba-outcomes-metric__hint--good' : '') }}">
                        @if($cancellationRateDelta > 0)+@endif{{ number_format($cancellationRateDelta, 1, ',', '.') }} p.p. vs. mês anterior
                    </span>
                @else
                    <span class="saba-outcomes-metric__hint">Sem base comparável no mês anterior</span>
                @endif
            </div>

            <div class="saba-outcomes-metric">
                <span class="saba-outcomes-metric__label">Valor dos cancelados</span>
                <strong class="saba-outcomes-metric__value saba-outcomes-metric__value--money">{{ $cancelledValue }}</strong>
            </div>
        </div>
    </section>
</x-filament-widgets::widget>

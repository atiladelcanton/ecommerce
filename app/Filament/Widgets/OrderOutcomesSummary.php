<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Support\BrazilianMoney;
use Filament\Widgets\Widget;

final class OrderOutcomesSummary extends Widget
{
    protected string $view = 'filament.widgets.order-outcomes-summary';

    protected int|string|array $columnSpan = [
        'md' => 12,
        'xl' => 4,
    ];

    protected function getViewData(): array
    {
        $currentRange = [now()->startOfMonth(), now()->endOfMonth()];
        $previousMonth = now()->subMonthNoOverflow();
        $previousRange = [$previousMonth->copy()->startOfMonth(), $previousMonth->copy()->endOfMonth()];

        $delivered = Order::query()
            ->where('status', OrderStatus::DELIVERED->value)
            ->whereBetween('delivered_at', $currentRange)
            ->count();

        $cancelled = Order::query()
            ->where('status', OrderStatus::CANCELLED->value)
            ->whereBetween('cancelled_at', $currentRange)
            ->count();

        $cancelledValue = (int) Order::query()
            ->where('status', OrderStatus::CANCELLED->value)
            ->whereBetween('cancelled_at', $currentRange)
            ->sum('total_cents');

        $previousDelivered = Order::query()
            ->where('status', OrderStatus::DELIVERED->value)
            ->whereBetween('delivered_at', $previousRange)
            ->count();

        $previousCancelled = Order::query()
            ->where('status', OrderStatus::CANCELLED->value)
            ->whereBetween('cancelled_at', $previousRange)
            ->count();

        $currentRate = $this->cancellationRate($delivered, $cancelled);
        $previousRate = $this->cancellationRate($previousDelivered, $previousCancelled);
        $delta = $previousRate === null || $currentRate === null
            ? null
            : round($currentRate - $previousRate, 1);

        return [
            'delivered' => $delivered,
            'cancelled' => $cancelled,
            'cancelledValue' => BrazilianMoney::display($cancelledValue),
            'cancellationRate' => $currentRate,
            'cancellationRateDelta' => $delta,
            'monthLabel' => now()->locale('pt_BR')->translatedFormat('F'),
        ];
    }

    private function cancellationRate(int $delivered, int $cancelled): ?float
    {
        $resolved = $delivered + $cancelled;

        if ($resolved === 0) {
            return null;
        }

        return round(($cancelled / $resolved) * 100, 1);
    }
}

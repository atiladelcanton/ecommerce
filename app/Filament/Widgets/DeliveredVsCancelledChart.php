<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Access\AdminPermissions;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

final class DeliveredVsCancelledChart extends ChartWidget
{
    public static function canView(): bool
    {
        return AdminPermissions::check(AdminPermissions::SALES_ORDERS);
    }

    protected ?string $heading = 'Pedidos concluídos x cancelados';

    protected ?string $description = 'Desfechos dos pedidos nos últimos 12 meses, considerando a data da entrega ou do cancelamento.';

    protected int|string|array $columnSpan = [
        'md' => 12,
        'xl' => 8,
    ];

    protected function getMaxHeight(): ?string
    {
        return '290px';
    }

    protected function getData(): array
    {
        $months = collect(range(11, 0))
            ->map(fn (int $offset): Carbon => now()->subMonthsNoOverflow($offset)->startOfMonth());

        $rangeStart = $months->first()->copy()->startOfMonth();
        $rangeEnd = now()->endOfMonth();

        $delivered = Order::query()
            ->where('status', OrderStatus::DELIVERED->value)
            ->whereNotNull('delivered_at')
            ->whereBetween('delivered_at', [$rangeStart, $rangeEnd])
            ->get(['delivered_at'])
            ->countBy(fn (Order $order): string => $order->delivered_at->format('Y-m'));

        $cancelled = Order::query()
            ->where('status', OrderStatus::CANCELLED->value)
            ->whereNotNull('cancelled_at')
            ->whereBetween('cancelled_at', [$rangeStart, $rangeEnd])
            ->get(['cancelled_at'])
            ->countBy(fn (Order $order): string => $order->cancelled_at->format('Y-m'));

        return [
            'datasets' => [
                [
                    'label' => 'Entregues',
                    'data' => $months
                        ->map(fn (Carbon $month): int => (int) ($delivered[$month->format('Y-m')] ?? 0))
                        ->all(),
                    'backgroundColor' => 'rgba(22, 163, 74, 0.82)',
                    'borderColor' => '#16a34a',
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                    'borderSkipped' => false,
                ],
                [
                    'label' => 'Cancelados',
                    'data' => $months
                        ->map(fn (Carbon $month): int => (int) ($cancelled[$month->format('Y-m')] ?? 0))
                        ->all(),
                    'backgroundColor' => 'rgba(220, 38, 38, 0.78)',
                    'borderColor' => '#dc2626',
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                    'borderSkipped' => false,
                ],
            ],
            'labels' => $months
                ->map(fn (Carbon $month): string => ucfirst($month->locale('pt_BR')->translatedFormat('M/y')))
                ->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'align' => 'start',
                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                        'boxWidth' => 7,
                        'boxHeight' => 7,
                    ],
                ],
                'tooltip' => ['displayColors' => true],
            ],
            'scales' => [
                'x' => [
                    'grid' => ['display' => false],
                    'border' => ['display' => false],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0, 'stepSize' => 1],
                    'grid' => ['color' => 'rgba(113, 113, 122, 0.10)'],
                    'border' => ['display' => false],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}

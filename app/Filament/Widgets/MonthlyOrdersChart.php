<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Order\Models\Order;
use Filament\Widgets\ChartWidget;

final class MonthlyOrdersChart extends ChartWidget
{
    protected ?string $heading = 'Evolução de pedidos';

    protected ?string $description = 'Quantidade de pedidos criados nos últimos 12 meses.';

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
        $labels = [];
        $values = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonthsNoOverflow($i);
            $labels[] = ucfirst($month->locale('pt_BR')->translatedFormat('M/y'));
            $values[] = Order::query()
                ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->count();
        }

        return [
            'datasets' => [[
                'label' => 'Pedidos',
                'data' => $values,
                'borderColor' => '#dc2626',
                'backgroundColor' => 'rgba(220, 38, 38, 0.08)',
                'fill' => true,
                'tension' => 0.35,
                'borderWidth' => 2,
                'pointRadius' => 3,
                'pointHoverRadius' => 5,
                'pointBackgroundColor' => '#ffffff',
                'pointBorderColor' => '#dc2626',
                'pointBorderWidth' => 2,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
            'plugins' => [
                'legend' => ['display' => false],
                'tooltip' => ['displayColors' => false],
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

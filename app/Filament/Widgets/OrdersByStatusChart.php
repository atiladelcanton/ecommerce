<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use Filament\Widgets\ChartWidget;

final class OrdersByStatusChart extends ChartWidget
{
    protected ?string $heading = 'Pedidos por status';

    protected ?string $description = 'Distribuição atual do fluxo operacional.';

    protected int|string|array $columnSpan = [
        'md' => 12,
        'xl' => 4,
    ];

    protected function getMaxHeight(): ?string
    {
        return '290px';
    }

    protected function getData(): array
    {
        $statuses = OrderStatus::cases();
        $counts = Order::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'datasets' => [[
                'label' => 'Pedidos',
                'data' => array_map(fn (OrderStatus $status): int => (int) ($counts[$status->value] ?? 0), $statuses),
                'backgroundColor' => ['#a1a1aa', '#16a34a', '#f59e0b', '#0ea5e9', '#2563eb', '#7c3aed', '#15803d', '#dc2626'],
                'borderRadius' => 6,
                'borderSkipped' => false,
                'barThickness' => 16,
            ]],
            'labels' => array_map(fn (OrderStatus $status): string => $status->label(), $statuses),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => ['display' => false],
                'tooltip' => ['displayColors' => false],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0, 'stepSize' => 1],
                    'grid' => ['color' => 'rgba(113, 113, 122, 0.10)'],
                    'border' => ['display' => false],
                ],
                'y' => [
                    'grid' => ['display' => false],
                    'border' => ['display' => false],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}

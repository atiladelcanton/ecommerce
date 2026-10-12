<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\BestSellingProducts;
use App\Filament\Widgets\MonthlyOrdersChart;
use App\Filament\Widgets\OrdersAttention;
use App\Filament\Widgets\OrdersByStatusChart;
use App\Filament\Widgets\OrdersOverview;
use Filament\Pages\Dashboard as BaseDashboard;
use UnitEnum;

final class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Painel de Controle';

    protected static string|UnitEnum|null $navigationGroup = 'Painel';

    public function getSubheading(): ?string
    {
        return 'Visão geral das vendas e da operação de pedidos.';
    }

    public function getWidgets(): array
    {
        return [
            OrdersOverview::class,
            MonthlyOrdersChart::class,
            OrdersByStatusChart::class,
            OrdersAttention::class,
            BestSellingProducts::class,
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'md' => 12,
            'xl' => 12,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Access\AdminPermissions;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Support\BrazilianMoney;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class OrdersOverview extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        return AdminPermissions::check(AdminPermissions::SALES_ORDERS);
    }

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
        $previousStart = now()->subMonthNoOverflow()->startOfMonth();
        $previousEnd = now()->subMonthNoOverflow()->endOfMonth();

        $ordersTotal = Order::query()->count();
        $ordersThisMonth = Order::query()->whereBetween('created_at', [$start, $end])->count();
        $ordersPrevious = Order::query()->whereBetween('created_at', [$previousStart, $previousEnd])->count();
        $revenue = (int) Order::query()
            ->where('payment_status', PaymentStatus::APPROVED->value)
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_cents');
        $previousRevenue = (int) Order::query()
            ->where('payment_status', PaymentStatus::APPROVED->value)
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->sum('total_cents');
        $paidOrders = Order::query()
            ->where('payment_status', PaymentStatus::APPROVED->value)
            ->whereBetween('created_at', [$start, $end])
            ->count();
        $ticket = $paidOrders > 0 ? intdiv($revenue, $paidOrders) : 0;
        $needsAction = Order::query()
            ->whereIn('status', [OrderStatus::PAID->value, OrderStatus::PROCESSING->value, OrderStatus::READY_FOR_SHIPPING->value])
            ->count();

        return [
            Stat::make('Total de pedidos', number_format($ordersTotal, 0, ',', '.'))
                ->description(number_format($ordersThisMonth, 0, ',', '.').' neste mês · '.$this->variationDescription($ordersThisMonth, $ordersPrevious))
                ->descriptionIcon($ordersThisMonth >= $ordersPrevious ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('primary')
                ->extraAttributes(['class' => 'saba-kpi-card saba-kpi-card--primary']),
            Stat::make('Faturamento pago', BrazilianMoney::display($revenue))
                ->description($this->variationDescription($revenue, $previousRevenue))
                ->descriptionIcon($revenue >= $previousRevenue ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('success')
                ->extraAttributes(['class' => 'saba-kpi-card saba-kpi-card--success']),
            Stat::make('Ticket médio', BrazilianMoney::display($ticket))
                ->description($paidOrders.' '.($paidOrders === 1 ? 'pedido pago' : 'pedidos pagos').' no mês')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('gray')
                ->extraAttributes(['class' => 'saba-kpi-card saba-kpi-card--neutral']),
            Stat::make('Aguardando ação', number_format($needsAction, 0, ',', '.'))
                ->description($needsAction > 0 ? 'Pedidos pagos ou em preparação' : 'Nenhum pedido pendente de ação')
                ->descriptionIcon($needsAction > 0 ? 'heroicon-m-bell-alert' : 'heroicon-m-check-circle')
                ->color($needsAction > 0 ? 'warning' : 'success')
                ->extraAttributes(['class' => 'saba-kpi-card saba-kpi-card--warning']),
        ];
    }

    private function variationDescription(int $current, int $previous): string
    {
        if ($previous === 0) {
            return $current > 0 ? 'Novo movimento neste mês' : 'Sem movimento no período';
        }

        $variation = (int) round((($current - $previous) / $previous) * 100);

        return ($variation >= 0 ? '+' : '').$variation.'% vs. mês anterior';
    }
}

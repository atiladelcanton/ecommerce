<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Access\AdminPermissions;

use App\Domain\Finance\Enums\CashFlowType;
use App\Domain\Finance\Models\CashFlowEntry;
use Filament\Widgets\ChartWidget;

final class CashFlowMonthlyChart extends ChartWidget
{
    public static function canView(): bool
    {
        return AdminPermissions::check(AdminPermissions::SALES_FINANCE);
    }

    protected ?string $heading = 'Histórico financeiro mensal';

    protected ?string $description = 'Entradas e saídas dos últimos 12 meses.';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $labels = [];
        $income = [];
        $expense = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonthsNoOverflow($i);
            $range = [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()];
            $labels[] = ucfirst($month->locale('pt_BR')->translatedFormat('M/y'));
            $income[] = ((int) CashFlowEntry::query()->where('type', CashFlowType::INCOME->value)->whereBetween('occurred_at', $range)->sum('amount_cents')) / 100;
            $expense[] = ((int) CashFlowEntry::query()->where('type', CashFlowType::EXPENSE->value)->whereBetween('occurred_at', $range)->sum('amount_cents')) / 100;
        }

        return [
            'datasets' => [
                ['label' => 'Entradas', 'data' => $income, 'backgroundColor' => 'rgba(22, 163, 74, 0.75)', 'borderRadius' => 6],
                ['label' => 'Saídas', 'data' => $expense, 'backgroundColor' => 'rgba(220, 38, 38, 0.72)', 'borderRadius' => 6],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => ['y' => ['beginAtZero' => true]],
            'maintainAspectRatio' => false,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Finance\Enums\CashFlowType;
use App\Domain\Finance\Models\CashFlowEntry;
use App\Support\BrazilianMoney;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class FinanceOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $range = [now()->startOfMonth(), now()->endOfMonth()];
        $income = (int) CashFlowEntry::query()->where('type', CashFlowType::INCOME->value)->whereBetween('occurred_at', $range)->sum('amount_cents');
        $expense = (int) CashFlowEntry::query()->where('type', CashFlowType::EXPENSE->value)->whereBetween('occurred_at', $range)->sum('amount_cents');
        $balance = $income - $expense;
        $allTimeBalance = (int) CashFlowEntry::query()->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount_cents ELSE -amount_cents END), 0) as balance")->value('balance');

        return [
            Stat::make('Entradas do mês', BrazilianMoney::display($income))->description('Receitas registradas')->descriptionIcon('heroicon-m-arrow-down-left')->color('success'),
            Stat::make('Saídas do mês', BrazilianMoney::display($expense))->description('Despesas registradas')->descriptionIcon('heroicon-m-arrow-up-right')->color('danger'),
            Stat::make('Saldo do mês', BrazilianMoney::display($balance))->description('Entradas menos saídas')->descriptionIcon('heroicon-m-scale')->color($balance >= 0 ? 'success' : 'danger'),
            Stat::make('Saldo acumulado', BrazilianMoney::display($allTimeBalance))->description('Histórico completo')->descriptionIcon('heroicon-m-banknotes')->color($allTimeBalance >= 0 ? 'primary' : 'danger'),
        ];
    }
}

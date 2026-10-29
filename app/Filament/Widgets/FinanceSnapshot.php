<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Access\AdminPermissions;

use App\Domain\Finance\Enums\CashFlowType;
use App\Domain\Finance\Models\CashFlowEntry;
use Filament\Widgets\Widget;

final class FinanceSnapshot extends Widget
{
    public static function canView(): bool
    {
        return AdminPermissions::check(AdminPermissions::SALES_FINANCE);
    }

    protected string $view = 'filament.widgets.finance-snapshot';

    protected int|string|array $columnSpan = [
        'md' => 12,
        'xl' => 4,
    ];

    protected function getViewData(): array
    {
        $range = [now()->startOfMonth(), now()->endOfMonth()];
        $income = (int) CashFlowEntry::query()
            ->where('type', CashFlowType::INCOME->value)
            ->whereBetween('occurred_at', $range)
            ->sum('amount_cents');
        $expense = (int) CashFlowEntry::query()
            ->where('type', CashFlowType::EXPENSE->value)
            ->whereBetween('occurred_at', $range)
            ->sum('amount_cents');

        return [
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'entriesCount' => CashFlowEntry::query()->whereBetween('occurred_at', $range)->count(),
        ];
    }
}

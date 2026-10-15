<?php

declare(strict_types=1);

namespace App\Domain\Finance\Enums;

enum CashFlowType: string
{
    case INCOME = 'income';
    case EXPENSE = 'expense';

    public function label(): string
    {
        return match ($this) {
            self::INCOME => 'Entrada',
            self::EXPENSE => 'Saída',
        };
    }

    public function color(): string
    {
        return $this === self::INCOME ? 'success' : 'danger';
    }
}

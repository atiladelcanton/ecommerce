<?php

declare(strict_types=1);

namespace Tests\Feature\Finance;

use App\Actions\Finance\CreateCashFlowEntryAction;
use App\Domain\Finance\Enums\CashFlowType;
use App\Domain\Finance\Models\CashFlowEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

final class CashFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_income_and_expense_are_stored_in_integer_cents(): void
    {
        // The MVP deliberately drops this table. Exercise the retained legacy
        // money action against its isolated historical schema, never restore it in the app.
        $this->assertFalse(Schema::hasTable('cash_flow_entries'));
        (require database_path('migrations/2026_10_12_000004_remove_cash_flow_module.php'))->down();
        $user = User::factory()->create();
        $action = app(CreateCashFlowEntryAction::class);
        $income = $action->execute(CashFlowType::INCOME, 'Outras receitas', 'Entrada manual', 125000, now(), userId: $user->id);
        $expense = $action->execute(CashFlowType::EXPENSE, 'Manutenção', 'Reparo', 25990, now(), userId: $user->id);

        $this->assertSame(125000, $income->amount_cents);
        $this->assertSame(CashFlowType::EXPENSE, $expense->type);
        $this->assertSame(2, CashFlowEntry::query()->count());
    }

    public function test_zero_or_negative_manual_entry_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        app(CreateCashFlowEntryAction::class)->execute(CashFlowType::EXPENSE, 'Teste', 'Inválido', 0, now());
    }
}

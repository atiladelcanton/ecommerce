<?php

declare(strict_types=1);

namespace App\Actions\Finance;

use App\Domain\Finance\Models\CashFlowEntry;
use Illuminate\Validation\ValidationException;

final class DeleteCashFlowEntryAction
{
    public function execute(CashFlowEntry $entry): void
    {
        if ($entry->isAutomatic()) {
            throw ValidationException::withMessages(['entry' => 'Lançamentos automáticos de pagamento não podem ser excluídos.']);
        }

        $entry->delete();
    }
}

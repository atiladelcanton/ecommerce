<?php

declare(strict_types=1);

namespace App\Actions\Finance;

use App\Domain\Finance\Enums\CashFlowType;
use App\Domain\Finance\Models\CashFlowEntry;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

final class UpdateCashFlowEntryAction
{
    public function execute(
        CashFlowEntry $entry,
        CashFlowType $type,
        string $category,
        string $description,
        int $amountCents,
        CarbonInterface $occurredAt,
        ?string $notes = null,
    ): CashFlowEntry {
        if ($entry->isAutomatic()) {
            throw ValidationException::withMessages(['entry' => 'Lançamentos automáticos de pagamento não podem ser editados.']);
        }
        if ($amountCents <= 0) {
            throw new InvalidArgumentException('O valor do lançamento deve ser maior que zero.');
        }

        $entry->update([
            'type' => $type,
            'category' => trim($category),
            'description' => trim($description),
            'amount_cents' => $amountCents,
            'occurred_at' => $occurredAt,
            'notes' => filled($notes) ? trim($notes) : null,
        ]);

        return $entry->refresh();
    }
}

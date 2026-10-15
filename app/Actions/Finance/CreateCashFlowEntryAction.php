<?php

declare(strict_types=1);

namespace App\Actions\Finance;

use App\Domain\Finance\Enums\CashFlowType;
use App\Domain\Finance\Models\CashFlowEntry;
use Carbon\CarbonInterface;
use InvalidArgumentException;

final class CreateCashFlowEntryAction
{
    public function execute(
        CashFlowType $type,
        string $category,
        string $description,
        int $amountCents,
        CarbonInterface $occurredAt,
        ?string $notes = null,
        ?int $userId = null,
    ): CashFlowEntry {
        if ($amountCents <= 0) {
            throw new InvalidArgumentException('O valor do lançamento deve ser maior que zero.');
        }

        return CashFlowEntry::query()->create([
            'type' => $type,
            'category' => trim($category),
            'description' => trim($description),
            'amount_cents' => $amountCents,
            'occurred_at' => $occurredAt,
            'source' => 'manual',
            'notes' => filled($notes) ? trim($notes) : null,
            'user_id' => $userId,
        ]);
    }
}

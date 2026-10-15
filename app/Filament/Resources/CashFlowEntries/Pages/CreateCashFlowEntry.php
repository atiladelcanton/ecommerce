<?php

declare(strict_types=1);

namespace App\Filament\Resources\CashFlowEntries\Pages;

use App\Actions\Finance\CreateCashFlowEntryAction;
use App\Domain\Finance\Enums\CashFlowType;
use App\Filament\Resources\CashFlowEntries\CashFlowEntryResource;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

final class CreateCashFlowEntry extends CreateRecord
{
    protected static string $resource = CashFlowEntryResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateCashFlowEntryAction::class)->execute(
            CashFlowType::from($data['type']),
            $data['category'],
            $data['description'],
            (int) $data['amount_cents'],
            Carbon::parse($data['occurred_at']),
            $data['notes'] ?? null,
            auth()->id(),
        );
    }
}

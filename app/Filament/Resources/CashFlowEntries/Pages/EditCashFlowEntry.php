<?php

declare(strict_types=1);

namespace App\Filament\Resources\CashFlowEntries\Pages;

use App\Actions\Finance\DeleteCashFlowEntryAction;
use App\Actions\Finance\UpdateCashFlowEntryAction;
use App\Domain\Finance\Enums\CashFlowType;
use App\Domain\Finance\Models\CashFlowEntry;
use App\Filament\Resources\CashFlowEntries\CashFlowEntryResource;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

final class EditCashFlowEntry extends EditRecord
{
    protected static string $resource = CashFlowEntryResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var CashFlowEntry $record */
        return app(UpdateCashFlowEntryAction::class)->execute(
            $record,
            CashFlowType::from($data['type']),
            $data['category'],
            $data['description'],
            (int) $data['amount_cents'],
            Carbon::parse($data['occurred_at']),
            $data['notes'] ?? null,
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->using(function (CashFlowEntry $record): bool {
                app(DeleteCashFlowEntryAction::class)->execute($record);

                return true;
            }),
        ];
    }
}

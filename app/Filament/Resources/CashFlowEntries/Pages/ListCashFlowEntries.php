<?php

declare(strict_types=1);

namespace App\Filament\Resources\CashFlowEntries\Pages;

use App\Filament\Resources\CashFlowEntries\CashFlowEntryResource;
use App\Filament\Widgets\CashFlowMonthlyChart;
use App\Filament\Widgets\FinanceOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListCashFlowEntries extends ListRecords
{
    protected static string $resource = CashFlowEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Novo lançamento')->icon('heroicon-o-plus')];
    }

    protected function getHeaderWidgets(): array
    {
        return [FinanceOverview::class, CashFlowMonthlyChart::class];
    }
}

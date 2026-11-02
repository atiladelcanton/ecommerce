<?php

declare(strict_types=1);

namespace App\Filament\Resources\InfluencerCommissions\Pages;

use App\Filament\Resources\InfluencerCommissions\InfluencerCommissionResource;
use Filament\Resources\Pages\ListRecords;

final class ListInfluencerCommissions extends ListRecords
{
    protected static string $resource = InfluencerCommissionResource::class;
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\Influencers\Pages;

use App\Filament\Resources\Influencers\InfluencerResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateInfluencer extends CreateRecord
{
    protected static string $resource = InfluencerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['email'] = mb_strtolower(trim((string) $data['email']));
        $data['commission_bps'] = (int) round(((float) str_replace(',', '.', (string) $data['commission_percent'])) * 100);
        unset($data['commission_percent']);
        return $data;
    }
}

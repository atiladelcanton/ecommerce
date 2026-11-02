<?php

declare(strict_types=1);

namespace App\Filament\Resources\Influencers\Pages;

use App\Filament\Resources\Influencers\InfluencerResource;
use Filament\Resources\Pages\EditRecord;

final class EditInfluencer extends EditRecord
{
    protected static string $resource = InfluencerResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['password'] = null;
        $data['commission_percent'] = (float) ($data['commission_bps'] ?? 0) / 100;
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['email'] = mb_strtolower(trim((string) $data['email']));
        $data['commission_bps'] = (int) round(((float) str_replace(',', '.', (string) $data['commission_percent'])) * 100);
        unset($data['commission_percent']);
        if (! filled($data['pix_key'] ?? null)) {
            unset($data['pix_key']);
        }
        return $data;
    }
}

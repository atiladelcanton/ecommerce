<?php

declare(strict_types=1);

namespace App\Filament\Resources\Banners\Pages;

use App\Filament\Resources\Banners\BannerResource;
use App\Filament\Support\HandlesBannerForm;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

final class CreateBanner extends CreateRecord
{
    use HandlesBannerForm;

    protected static string $resource = BannerResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return $this->saveBannerForm($data);
    }

    protected function getRedirectUrl(): string
    {
        return self::getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\Banners\Pages;

use App\Actions\Catalog\DeleteBannerAction;
use App\Filament\Resources\Banners\BannerResource;
use App\Filament\Support\CatalogForm;
use App\Filament\Support\HandlesBannerForm;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

final class EditBanner extends EditRecord
{
    use HandlesBannerForm;

    protected static string $resource = BannerResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return $this->saveBannerForm($data, $record);
    }

    protected function getHeaderActions(): array
    {
        return [CatalogForm::delete(fn (Model $record) => app(DeleteBannerAction::class)->execute($record))];
    }
}

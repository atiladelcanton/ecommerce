<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Pages;

use App\Actions\Catalog\DeleteCategoryAction;
use App\Actions\Catalog\UpdateCategoryAction;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Support\CatalogForm;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

final class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return CatalogForm::submit(fn () => app(UpdateCategoryAction::class)->execute($record, $data));
    }

    protected function getHeaderActions(): array
    {
        return [CatalogForm::delete(fn (Model $record) => app(DeleteCategoryAction::class)->execute($record))];
    }
}

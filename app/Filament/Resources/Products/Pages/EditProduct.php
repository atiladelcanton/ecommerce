<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Actions\Catalog\DeleteProductAction;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Support\CatalogForm;
use App\Filament\Support\HandlesProductForm;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

final class EditProduct extends EditRecord
{
    use HandlesProductForm;

    protected static string $resource = ProductResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return $this->saveProductForm($data, $record);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['cost_price'] = $this->getRecord()->cost_price;

        return $data;
    }

    protected function afterSave(): void
    {
        $this->data['new_images'] = [];
        $this->pendingImagePaths = [];
        $this->dispatch('gallery-updated');
    }

    protected function getHeaderActions(): array
    {
        return [CatalogForm::delete(fn (Model $record) => app(DeleteProductAction::class)->execute($record))];
    }
}

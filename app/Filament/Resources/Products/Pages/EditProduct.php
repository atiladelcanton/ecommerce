<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Actions\Catalog\DeleteProductAction;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Support\CatalogForm;
use App\Filament\Support\HandlesProductAiAssistant;
use App\Filament\Support\HandlesProductForm;
use App\Support\Catalog\ProductDescription;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

final class EditProduct extends EditRecord
{
    use HandlesProductAiAssistant, HandlesProductForm;

    protected static string $resource = ProductResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return $this->saveProductForm($data, $record);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['variants'] = $this->getRecord()->variants->map(fn ($variant) => array_merge($variant->toArray(), ['attributes' => $variant->characteristics()]))->all();
        $data['cost_price'] = $this->getRecord()->cost_price;
        $data['description'] = ProductDescription::html($data['description'] ?? null);

        return $data;
    }

    protected function afterSave(): void
    {
        // Avoid re-hydrating the entire form after every save. Re-filling the whole
        // Filament form can cause browser number inputs to be morphed with stale DOM
        // values, which made unchanged package measurements appear to move between
        // fields after saving. The persisted product is already up to date here.
        //
        // We only need a re-fill when a new variation was created, so its database ID
        // is placed back into the hidden repeater field and a second save does not
        // create the same combination again. When that re-fill is necessary, preserve
        // every non-variant field exactly as the user had it in the form.
        $currentState = $this->data;
        $hasVariantWithoutId = collect($currentState['variants'] ?? [])->contains(
            static fn (mixed $variant): bool => is_array($variant) && blank($variant['id'] ?? null),
        );

        if ($hasVariantWithoutId) {
            $preservedState = $currentState;
            unset($preservedState['variants'], $preservedState['new_images']);

            $this->getRecord()->unsetRelation('variants');
            $this->fillForm();

            foreach ($preservedState as $field => $value) {
                $this->data[$field] = $value;
            }
        }

        $this->data['new_images'] = [];
        $this->pendingImagePaths = [];
        $this->dispatch('gallery-updated');
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->productAiAssistantAction(),
            CatalogForm::delete(fn (Model $record) => app(DeleteProductAction::class)->execute($record)),
        ];
    }
}

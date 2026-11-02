<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Actions\Catalog\AddProductImageAction;
use App\Actions\Catalog\CreateProductAction;
use App\Actions\Catalog\UpdateProductAction;
use App\Domain\Catalog\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

trait HandlesProductForm
{
    /** Paths accepted by the upload field, retained across validation retries. */
    #[Locked]
    public array $pendingImagePaths = [];

    protected function saveProductForm(array $data, ?Product $record = null): Product
    {
        // Only reached after Filament has validated and stored the uploaded files.
        $this->pendingImagePaths = $data['new_images'] ?? [];

        // The types are an editor-only field, but must correspond to the grid.
        // Otherwise a change to a tag could be silently ignored on Save.
        if (isset($this->data['variation_axes']) && ! ProductVariationMatrix::axesMatchRows(
            $this->data['variation_axes'], $data['variants'] ?? [],
        )) {
            throw ValidationException::withMessages([
                'data.variation_axes' => 'Você alterou as características ou seus valores. Clique em "Gerar / atualizar combinações" antes de salvar o produto.',
            ]);
        }

        $aliases = [];
        foreach (array_keys($this->data['variants'] ?? []) as $index => $key) {
            $aliases['variants.'.$index] = 'variants.'.$key;
        }

        return CatalogForm::submit(function () use ($data, $record): Product {
            $paths = $data['new_images'] ?? [];
            unset($data['new_images']);

            return DB::transaction(function () use ($data, $record, $paths): Product {
                $product = $record
                    ? app(UpdateProductAction::class)->execute($record, $data)
                    : app(CreateProductAction::class)->execute($data);
                foreach ($paths as $path) {
                    app(AddProductImageAction::class)->execute($product, ['path' => $path]);
                }

                return $product;
            });
        }, $aliases);
    }
}

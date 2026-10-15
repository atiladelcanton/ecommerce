<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Services\InventoryAvailability;
use Illuminate\Support\Facades\DB;

final class UpdateProductAction
{
    public function __construct(private readonly ValidateCatalogData $validation) {}

    public function execute(Product $model, array $data): Product
    {
        return DB::transaction(function () use ($model, $data): Product {
            $locked = Product::query()->lockForUpdate()->findOrFail($model->id);
            $variants = $data['variants'] ?? null;
            unset($data['variants']);
            $validated = $this->validation->product($data, $locked);
            if (array_key_exists('stock', $validated)) {
                app(InventoryAvailability::class)->assertStockChange($locked, $validated['stock'] === null ? null : (int) $validated['stock']);
            }
            if (isset($validated['availability']) && $validated['availability'] !== $locked->availability->value) {
                app(InventoryAvailability::class)->assertCanDelete($locked);
            }
            $locked->update($validated);
            if ($variants !== null) {
                app(SyncProductVariantsAction::class)->execute($locked, $variants);
            } else {
                foreach ($locked->variants()->get() as $variant) {
                    try {
                        $variant->pricing($locked);
                    } catch (\App\Domain\Catalog\Exceptions\InvalidProductPrice $exception) {
                        throw \Illuminate\Validation\ValidationException::withMessages(['price' => 'Revise as promoções das variações antes de alterar o preço base.']);
                    }
                }
            }

            return $locked->refresh();
        });
    }
}

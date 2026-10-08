<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class AddProductImageAction
{
    public function execute(Product $product, array $data): ProductImage
    {
        $data = Validator::make($data, [
            'path' => ['required', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['integer', 'between:-2147483648,2147483647'],
            'is_primary' => ['boolean'],
        ])->validate();

        return DB::transaction(function () use ($product, $data): ProductImage {
            $locked = Product::query()->lockForUpdate()->findOrFail($product->id);
            $data['is_primary'] = ($data['is_primary'] ?? false) || ! $locked->images()->exists();
            if ($data['is_primary']) {
                $locked->images()->update(['is_primary' => false]);
            }

            return $locked->images()->create($data);
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class UpdateProductImageOrderAction
{
    public function execute(ProductImage $image, int $sortOrder): void
    {
        DB::transaction(function () use ($image, $sortOrder): void {
            $product = Product::query()->lockForUpdate()->findOrFail($image->product_id);
            $locked = $product->images()->findOrFail($image->id);
            Validator::make(['sort_order' => $sortOrder], ['sort_order' => ['integer', 'between:-2147483648,2147483647']])->validate();
            $locked->update(['sort_order' => $sortOrder]);
        });
    }
}

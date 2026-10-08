<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImage;
use Illuminate\Support\Facades\DB;

final class SetPrimaryProductImageAction
{
    public function execute(ProductImage $image): void
    {
        DB::transaction(function () use ($image): void {
            $product = Product::query()->lockForUpdate()->findOrFail($image->product_id);
            $locked = $product->images()->findOrFail($image->id);
            $product->images()->whereKeyNot($locked->id)->update(['is_primary' => false]);
            $locked->update(['is_primary' => true]);
        });
    }
}

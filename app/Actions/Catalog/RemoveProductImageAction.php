<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImage;
use Illuminate\Support\Facades\DB;

final class RemoveProductImageAction
{
    public function execute(ProductImage $image): void
    {
        DB::transaction(function () use ($image): void {
            $product = Product::query()->lockForUpdate()->findOrFail($image->product_id);
            $locked = $product->images()->findOrFail($image->id);
            $primary = $locked->is_primary;
            $locked->delete();
            if ($primary) {
                $product->images()->first()?->update(['is_primary' => true]);
            }
        });
    }
}

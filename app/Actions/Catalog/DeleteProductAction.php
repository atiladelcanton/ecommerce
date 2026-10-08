<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Domain\Catalog\Models\Product;
use Illuminate\Support\Facades\DB;

final class DeleteProductAction
{
    public function execute(Product $model): void
    {
        DB::transaction(function () use ($model): void {
            $locked = Product::query()->lockForUpdate()->findOrFail($model->id);
            $locked->delete();
        });
    }
}

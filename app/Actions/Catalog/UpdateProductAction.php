<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Domain\Catalog\Models\Product;
use Illuminate\Support\Facades\DB;

final class UpdateProductAction
{
    public function __construct(private readonly ValidateCatalogData $validation) {}

    public function execute(Product $model, array $data): Product
    {
        return DB::transaction(function () use ($model, $data): Product {
            $locked = Product::query()->lockForUpdate()->findOrFail($model->id);
            $locked->update($this->validation->product($data, $locked));

            return $locked->refresh();
        });
    }
}

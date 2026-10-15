<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Domain\Catalog\Models\Product;
use Illuminate\Support\Facades\DB;

final class CreateProductAction
{
    public function __construct(private readonly ValidateCatalogData $validation) {}

    public function execute(array $data): Product
    {
        return DB::transaction(function () use ($data): Product {
            $variants = $data['variants'] ?? null;
            unset($data['variants']);
            $product = Product::query()->create($this->validation->product($data));
            if ($variants !== null) {
                app(SyncProductVariantsAction::class)->execute($product, $variants);
            }

            return $product->refresh();
        }, 3);
    }
}

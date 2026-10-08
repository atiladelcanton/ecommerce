<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Domain\Catalog\Models\Product;

final class CreateProductAction
{
    public function __construct(private readonly ValidateCatalogData $validation) {}

    public function execute(array $data): Product
    {
        return Product::query()->create($this->validation->product($data))->refresh();
    }
}

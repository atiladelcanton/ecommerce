<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Actions\Catalog\Storefront\PublicProducts;
use App\Domain\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class GetCartProductAction
{
    public function __construct(private readonly PublicProducts $products) {}

    public function query(): Builder
    {
        return $this->products->query()->addSelect(['products.is_active', 'products.stock', 'products.weight', 'products.height', 'products.width', 'products.length']);
    }

    public function execute(int $productId): Product
    {
        return $this->query()->findOrFail($productId);
    }
}

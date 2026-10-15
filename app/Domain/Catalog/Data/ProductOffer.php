<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Data;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Catalog\ValueObjects\ProductPricing;

final readonly class ProductOffer
{
    public function __construct(
        public Product $product,
        public ?ProductVariant $variant,
        public ProductPricing $pricing,
        public ?string $sku,
        public array $attributes,
        public array $measurements,
    ) {}
}

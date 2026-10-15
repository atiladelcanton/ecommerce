<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Domain\Catalog\Data\ProductOffer;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Catalog\ValueObjects\ProductPricing;

final class ResolveProductOfferAction
{
    public function execute(Product $product, ?ProductVariant $variant = null): ProductOffer
    {
        $price = $variant?->price ?? $product->price;
        $promotion = $variant?->promotional_price ?? ($variant?->price === null ? $product->promotional_price : null);
        $measurements = [];
        foreach (['weight', 'height', 'width', 'length'] as $field) {
            $measurements[$field] = $variant?->weight !== null ? $variant->{$field} : $product->{$field};
        }

        return new ProductOffer($product, $variant, $variant ? $variant->pricing($product) : new ProductPricing($price, $promotion), $variant?->sku ?: $product->sku, $variant?->characteristics() ?? [], $measurements);
    }
}

<?php

declare(strict_types=1);

namespace App\Support\Storefront;
use App\Domain\Catalog\Enums\ProductAvailability;
use App\Domain\Catalog\Models\Product;
use Illuminate\Support\Facades\Storage;
final class ProductMetadata
{
    public static function make(Product $product, string $url): array
    {
        $cents = $product->final_price;
        return array_filter([
            '@context' => 'https://schema.org', '@type' => 'Product', 'name' => $product->name,
            'url' => $url, 'sku' => $product->sku,
            'description' => strip_tags($product->short_description ?? ''),
            'image' => $product->images->map(fn ($image): string => Storage::disk('public')->url($image->path))->all(),
            'offers' => [
                '@type' => 'Offer', 'url' => $url, 'priceCurrency' => 'BRL',
                'price' => intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT),
                'availability' => 'https://schema.org/'.match ($product->availability) {
                    ProductAvailability::IMMEDIATE => 'InStock', ProductAvailability::ON_DEMAND => 'PreOrder', ProductAvailability::UNAVAILABLE => 'OutOfStock',
                },
            ],
        ], fn (mixed $value): bool => $value !== null && $value !== '' && $value !== []);
    }
}

<?php

declare(strict_types=1);

namespace App\Support\Storefront;

use App\Actions\Catalog\Storefront\GetProductPurchaseDataAction;
use App\Domain\Catalog\Enums\ProductAvailability;
use App\Domain\Catalog\Models\Product;
use Illuminate\Support\Facades\Storage;

final class ProductMetadata
{
    public static function make(Product $product, string $url): array
    {
        $cents = $product->final_price;
        $offerData = app(GetProductPurchaseDataAction::class)->execute($product);
        $prices = $offerData['offers']->map(fn ($offer) => $offer->pricing->finalPrice());
        $offer = [
            '@type' => 'Offer', 'url' => $url, 'priceCurrency' => 'BRL',
            'price' => intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT),
            'availability' => 'https://schema.org/'.match ($product->availability) {
                ProductAvailability::IMMEDIATE => 'InStock', ProductAvailability::ON_DEMAND => 'PreOrder', ProductAvailability::UNAVAILABLE => 'OutOfStock',
            },
        ];
        if ($prices->isNotEmpty()) {
            $decimal = fn (int $value): string => intdiv($value, 100).'.'.str_pad((string) ($value % 100), 2, '0', STR_PAD_LEFT);
            $offer = ['@type' => 'AggregateOffer', 'url' => $url, 'priceCurrency' => 'BRL', 'lowPrice' => $decimal($prices->min()), 'highPrice' => $decimal($prices->max()), 'offerCount' => $prices->count(), 'availability' => $offer['availability']];
        }

        return array_filter([
            '@context' => 'https://schema.org', '@type' => 'Product', 'name' => $product->name,
            'url' => $url, 'sku' => $product->sku,
            'description' => strip_tags($product->short_description ?? ''),
            'image' => $product->images->map(fn ($image): string => Storage::disk('public')->url($image->path))->all(),
            'offers' => $offer,
        ], fn (mixed $value): bool => $value !== null && $value !== '' && $value !== []);
    }
}

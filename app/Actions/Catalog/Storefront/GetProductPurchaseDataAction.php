<?php

declare(strict_types=1);

namespace App\Actions\Catalog\Storefront;

use App\Actions\Catalog\ResolveProductOfferAction;
use App\Domain\Catalog\Enums\ProductAvailability;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Catalog\Services\InventoryAvailability;

final class GetProductPurchaseDataAction
{
    public function execute(Product $product, array $selection = [], ?int $variantId = null): array
    {
        $variants = $product->variants->where('is_active', true);
        $names = [];
        foreach ($variants as $variant) {
            foreach ($variant->characteristics() as $name => $value) {
                $names[mb_strtolower((string) $name)] ??= $name;
            }
        }
        ksort($names);
        $names = array_values($names);
        $fields = [];
        foreach ($names as $index => $name) {
            $values = [];
            foreach ($variants as $variant) {
                $attributes = $this->canonicalAttributes($variant);
                $matches = true;
                for ($previous = 0; $previous < $index; $previous++) {
                    if (filled($selection[$previous] ?? null) && ($attributes[mb_strtolower($names[$previous])] ?? null) !== mb_strtolower((string) $selection[$previous])) {
                        $matches = false;
                    }
                }
                if ($matches) {
                    foreach ($variant->characteristics() as $key => $value) {
                        if (mb_strtolower((string) $key) === mb_strtolower((string) $name)) {
                            $values[$value] = $value;
                        }
                    }
                }
            }
            $fields[] = ['name' => $name, 'values' => array_values($values)];
        }
        $selected = $variantId ? $variants->firstWhere('id', $variantId) : null;
        if ($selection !== []) {
            $selected = $variants->first(function (ProductVariant $variant) use ($selection, $names): bool {
                $attributes = $this->canonicalAttributes($variant);
                foreach ($names as $index => $name) {
                    if (! filled($selection[$index] ?? null) || ($attributes[mb_strtolower((string) $name)] ?? null) !== mb_strtolower((string) $selection[$index])) {
                        return false;
                    }
                }

                return true;
            });
        }
        $inventory = app(InventoryAvailability::class);
        $canPurchase = $product->availability !== ProductAvailability::UNAVAILABLE && ($product->variants->isEmpty() || $selected !== null);
        $offer = app(ResolveProductOfferAction::class)->execute($product, $selected);
        $available = ($pool = $inventory->pool($product, $selected)) ? $inventory->available($pool) : null;
        $offers = $variants->filter(function ($variant) use ($product, $inventory): bool {
            $pool = $inventory->pool($product, $variant);

            return ! $pool || $inventory->available($pool) > 0;
        })->map(fn ($variant) => app(ResolveProductOfferAction::class)->execute($product, $variant));
        if (! $selected && $offers->isNotEmpty()) {
            $offer = $offers->sortBy(fn ($offer) => $offer->pricing->finalPrice())->first();
        }

        return ['fields' => $fields, 'selectedVariant' => $selected, 'offer' => $offer, 'canPurchase' => $canPurchase && ($available === null || $available > 0), 'available' => $available, 'offers' => $offers];
    }

    private function canonicalAttributes(ProductVariant $variant): array
    {
        $attributes = [];
        foreach ($variant->characteristics() as $key => $value) {
            $attributes[mb_strtolower((string) $key)] = mb_strtolower($value);
        }

        return $attributes;
    }
}

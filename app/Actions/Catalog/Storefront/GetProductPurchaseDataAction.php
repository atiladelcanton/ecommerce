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
        $inventory = app(InventoryAvailability::class);
        $resolveOffer = app(ResolveProductOfferAction::class);

        // The same stock rules used by the cart determine which choices can be clicked.
        $availableById = [];
        $availableByPool = [];
        foreach ($variants as $variant) {
            $pool = $inventory->pool($product, $variant);
            $poolKey = $pool ? ($pool instanceof ProductVariant ? 'variant:' : 'product:').$pool->id : null;
            $remaining = $poolKey !== null
                ? ($availableByPool[$poolKey] ??= $inventory->available($pool))
                : null;
            $availableById[$variant->id] = $product->availability !== ProductAvailability::UNAVAILABLE
                && ($remaining === null || $remaining > 0);
        }

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
                    if (filled($selection[$previous] ?? null)
                        && ($attributes[mb_strtolower((string) $names[$previous])] ?? null) !== mb_strtolower((string) $selection[$previous])) {
                        $matches = false;
                        break;
                    }
                }
                if (! $matches) {
                    continue;
                }

                foreach ($variant->characteristics() as $key => $value) {
                    if (mb_strtolower((string) $key) !== mb_strtolower((string) $name)) {
                        continue;
                    }

                    $normalized = mb_strtolower((string) $value);
                    $values[$normalized] ??= ['value' => (string) $value, 'available' => false];
                    $values[$normalized]['available'] = $values[$normalized]['available'] || $availableById[$variant->id];
                }
            }
            $fields[] = ['name' => (string) $name, 'values' => array_values($values)];
        }

        $selected = $variantId ? $variants->firstWhere('id', $variantId) : null;
        if ($selection !== []) {
            $selected = $variants->first(function (ProductVariant $variant) use ($selection, $names): bool {
                $attributes = $this->canonicalAttributes($variant);
                foreach ($names as $index => $name) {
                    if (! filled($selection[$index] ?? null)
                        || ($attributes[mb_strtolower((string) $name)] ?? null) !== mb_strtolower((string) $selection[$index])) {
                        return false;
                    }
                }

                return true;
            });
        }

        $offer = $resolveOffer->execute($product, $selected);
        $pool = $inventory->pool($product, $selected);
        $selectedPoolKey = $pool ? ($pool instanceof ProductVariant ? 'variant:' : 'product:').$pool->id : null;
        $available = $selectedPoolKey !== null
            ? ($availableByPool[$selectedPoolKey] ??= $inventory->available($pool))
            : null;
        $offers = $variants
            ->filter(fn (ProductVariant $variant): bool => $availableById[$variant->id])
            ->map(fn (ProductVariant $variant) => $resolveOffer->execute($product, $variant));

        if (! $selected && $offers->isNotEmpty()) {
            $offer = $offers->sortBy(fn ($offer) => $offer->pricing->finalPrice())->first();
        }

        // With 3+ characteristics, picking the complete combination is much faster
        // and prevents the customer from assembling impossible selections.
        $combinations = $variants->map(function (ProductVariant $variant) use ($availableById, $resolveOffer, $product): array {
            $variantOffer = $resolveOffer->execute($product, $variant);
            $attributes = [];
            foreach ($variant->characteristics() as $name => $value) {
                $attributes[] = ['name' => (string) $name, 'value' => (string) $value];
            }

            return [
                'id' => $variant->id,
                'attributes' => $attributes,
                'price' => $variantOffer->pricing->finalPrice(),
                'available' => $availableById[$variant->id],
            ];
        })->sortBy(fn (array $choice): int => $choice['available'] ? 0 : 1)->values()->all();

        $canPurchase = $product->availability !== ProductAvailability::UNAVAILABLE
            && ($product->variants->isEmpty() || $selected !== null)
            && ($available === null || $available > 0);

        return [
            'fields' => $fields,
            'selectionMode' => count($fields) >= 3 ? 'combinations' : 'attributes',
            'combinations' => $combinations,
            'selectedVariant' => $selected,
            'offer' => $offer,
            'canPurchase' => $canPurchase,
            'available' => $available,
            'offers' => $offers,
        ];
    }

    private function canonicalAttributes(ProductVariant $variant): array
    {
        $attributes = [];
        foreach ($variant->characteristics() as $key => $value) {
            $attributes[mb_strtolower((string) $key)] = mb_strtolower((string) $value);
        }

        return $attributes;
    }
}

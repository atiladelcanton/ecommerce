<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Queries;

/** Shared SQL for catalog filters; monetary expressions remain integer cents. */
final class ProductOfferQuery
{
    public static function availableVariant(): string
    {
        // Match the application's persisted timestamp timezone, including a frozen test clock.
        $now = now()->format('Y-m-d H:i:s');

        return "pv.product_id = products.id AND pv.is_active = 1 AND (products.availability != 'immediate' OR COALESCE(pv.stock, products.stock) IS NULL OR COALESCE(pv.stock, products.stock) > COALESCE((SELECT SUM(ir.quantity) FROM inventory_reservations ir WHERE ir.product_id = products.id AND ((pv.stock IS NOT NULL AND ir.pool_type = 'variant' AND ir.product_variant_id = pv.id) OR (pv.stock IS NULL AND ir.pool_type = 'product')) AND ir.consumed_at IS NULL AND ir.released_at IS NULL AND ir.expires_at > '".$now."'), 0))";
    }

    public static function effectivePrice(): string
    {
        $price = 'CASE WHEN pv.price IS NOT NULL THEN COALESCE(pv.promotional_price, pv.price) ELSE COALESCE(pv.promotional_price, products.promotional_price, products.price) END';

        return 'COALESCE((SELECT MIN('.$price.') FROM product_variants pv WHERE '.self::availableVariant().'), products.promotional_price, products.price)';
    }
}

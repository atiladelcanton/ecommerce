<?php

declare(strict_types=1);

namespace App\Actions\Catalog\Storefront;

use App\Domain\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PublicProducts
{
    // SQL ordering/filtering uses persisted prices validated by the Catalog Actions.
    // No promotion validation is repeated here; display always uses ProductPricing.
    public const EFFECTIVE_PRICE = 'COALESCE(products.promotional_price, products.price)';

    public function query(bool $gallery = false): Builder
    {
        return Product::query()->active()->select([
            'id', 'category_id', 'name', 'slug', 'sku', 'short_description', 'description',
            'youtube_url', 'availability', 'price', 'promotional_price', 'whatsapp_quote_enabled', 'created_at',
        ])->with(['category:id,name,slug,is_active', 'images' => function (HasMany $query) use ($gallery): void {
            $query->select(['id', 'product_id', 'path', 'alt_text', 'is_primary', 'sort_order']);
            if (! $gallery) { $query->limit(1); }
        }]);
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Catalog\Storefront;

use App\Domain\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PublicProducts
{
    public function query(bool $gallery = false): Builder
    {
        return Product::query()->active()->select([
            'id', 'category_id', 'name', 'slug', 'sku', 'short_description', 'description',
            'youtube_url', 'availability', 'stock', 'price', 'promotional_price', 'whatsapp_quote_enabled', 'created_at',
            'is_active', 'weight', 'height', 'width', 'length',
        ])->withSum(['reservations as reserved_stock' => fn ($q) => $q->whereNull('consumed_at')->whereNull('released_at')->where(fn ($q) => $q->where('expires_at', '>', now())->orWhere('payment_hold', true))], 'quantity')->with(['variants' => fn ($q) => $q->withSum(['reservations as reserved_stock' => fn ($r) => $r->whereNull('consumed_at')->whereNull('released_at')->where(fn ($q) => $q->where('expires_at', '>', now())->orWhere('payment_hold', true))], 'quantity'), 'category:id,name,slug,is_active', 'images' => function (HasMany $query) use ($gallery): void {
            $query->select(['id', 'product_id', 'path', 'alt_text', 'is_primary', 'sort_order']);
            if (! $gallery) {
                $query->limit(1);
            }
        }]);
    }
}

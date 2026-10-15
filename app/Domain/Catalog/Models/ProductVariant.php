<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\ValueObjects\ProductPricing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'name', 'value', 'sku', 'stock', 'is_active', 'sort_order', 'attributes', 'combination_key', 'price', 'promotional_price', 'weight', 'height', 'width', 'length'];

    protected function casts(): array
    {
        return ['attributes' => 'array', 'price' => 'integer', 'promotional_price' => 'integer', 'weight' => 'decimal:3', 'height' => 'decimal:2', 'width' => 'decimal:2', 'length' => 'decimal:2', 'stock' => 'integer', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function pricing(Product $product): ProductPricing
    {
        return new ProductPricing($this->price ?? $product->price, $this->promotional_price ?? ($this->price === null ? $product->promotional_price : null));
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(InventoryReservation::class)->where('pool_type', 'variant');
    }

    public function characteristics(): array
    {
        return $this->getAttribute('attributes') ?: [$this->name => $this->value];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

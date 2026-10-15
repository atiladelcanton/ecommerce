<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Enums\ProductAvailability;
use App\Domain\Catalog\ValueObjects\ProductPricing;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'youtube_url',
        'availability',
        'cost_price',
        'price',
        'promotional_price',
        'stock',
        'weight',
        'height',
        'width',
        'length',
        'is_active',
        'is_featured',
        'whatsapp_quote_enabled',
    ];

    protected $hidden = ['cost_price'];

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }

    protected function casts(): array
    {
        return [
            'availability' => ProductAvailability::class,
            'cost_price' => 'integer',
            'price' => 'integer',
            'promotional_price' => 'integer',
            'stock' => 'integer',
            'weight' => 'decimal:3',
            'height' => 'decimal:2',
            'width' => 'decimal:2',
            'length' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'whatsapp_quote_enabled' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderByDesc('is_primary')->orderBy('sort_order')->orderBy('id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOnPromotion(Builder $query): Builder
    {
        $available = \App\Domain\Catalog\Queries\ProductOfferQuery::availableVariant();

        return $query->whereRaw("((NOT EXISTS (SELECT 1 FROM product_variants pv WHERE pv.product_id = products.id) AND products.promotional_price > 0 AND products.promotional_price < products.price) OR EXISTS (SELECT 1 FROM product_variants pv WHERE ".$available." AND (pv.promotional_price > 0 OR (pv.price IS NULL AND products.promotional_price > 0))))");
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(InventoryReservation::class)->where('pool_type', 'product');
    }

    public function pricing(): ProductPricing
    {
        if ($this->relationLoaded('variants')) {
            $offers = $this->variants->filter(function (ProductVariant $variant): bool {
                $stock = $variant->stock ?? $this->stock;
                $reserved = $variant->stock !== null ? (int) $variant->getAttribute('reserved_stock') : (int) $this->getAttribute('reserved_stock');

                return $variant->is_active && ($this->availability !== ProductAvailability::IMMEDIATE || $stock === null || $stock > $reserved);
            })->map(fn (ProductVariant $variant): ProductPricing => $variant->pricing($this));
            if ($offers->isNotEmpty()) {
                return $offers->sortBy(fn (ProductPricing $pricing): int => $pricing->finalPrice())->first();
            }
        }

        return new ProductPricing($this->price, $this->promotional_price);
    }

    protected function regularPrice(): Attribute
    {
        return Attribute::get(fn (): int => $this->pricing()->regularPrice);
    }

    protected function finalPrice(): Attribute
    {
        return Attribute::get(fn (): int => $this->pricing()->finalPrice());
    }

    protected function hasPromotion(): Attribute
    {
        return Attribute::get(fn (): bool => $this->pricing()->hasPromotion());
    }

    protected function discountPercentage(): Attribute
    {
        return Attribute::get(fn (): int => $this->pricing()->discountPercentage());
    }
}

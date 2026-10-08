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
        return $query->where('promotional_price', '>', 0)->whereColumn('promotional_price', '<', 'price');
    }

    public function pricing(): ProductPricing
    {
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

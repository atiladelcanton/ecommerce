<?php

declare(strict_types=1);

namespace App\Domain\Coupon\Models;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Coupon extends Model
{
    protected $fillable = [
        'code', 'description', 'discount_type', 'value', 'scope',
        'minimum_subtotal_cents', 'maximum_discount_cents', 'usage_limit',
        'allow_sale_items', 'is_active', 'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'minimum_subtotal_cents' => 'integer',
            'maximum_discount_cents' => 'integer',
            'usage_limit' => 'integer',
            'allow_sale_items' => 'boolean',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'coupon_products');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'coupon_categories');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(CouponClaim::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }
}

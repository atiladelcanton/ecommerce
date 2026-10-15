<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ShippingCarrier extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean', 'is_available' => 'boolean', 'is_active' => 'boolean', 'metadata' => 'array', 'last_synced_at' => 'datetime'];
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(ShippingIntegration::class, 'shipping_integration_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(ShippingService::class);
    }

    public function agencies(): BelongsToMany
    {
        return $this->belongsToMany(ShippingAgency::class, 'shipping_agency_carrier');
    }

    public function defaultAgency(): BelongsTo
    {
        return $this->belongsTo(ShippingAgency::class, 'default_shipping_agency_id');
    }
}

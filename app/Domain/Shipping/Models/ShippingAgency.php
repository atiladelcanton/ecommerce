<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class ShippingAgency extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean', 'is_available' => 'boolean', 'is_active' => 'boolean', 'metadata' => 'array', 'last_synced_at' => 'datetime'];
    }

    public function carriers(): BelongsToMany
    {
        return $this->belongsToMany(ShippingCarrier::class, 'shipping_agency_carrier');
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(ShippingCarrier::class, 'shipping_carrier_id');
    }
}

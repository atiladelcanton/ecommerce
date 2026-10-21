<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Models;

use App\Domain\Order\Models\Order;
use App\Domain\Shipping\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Shipment extends Model
{
    protected $fillable = [
        'order_id', 'shipping_integration_id', 'shipping_agency_id', 'environment', 'operation_key', 'fulfillment_key', 'attempt', 'packages', 'operation_phase', 'operation_started_at',
        'provider',
        'external_id',
        'carrier_external_id',
        'carrier_name',
        'service_external_id',
        'service_name',
        'status',
        'customer_price_cents',
        'provider_price_cents',
        'tracking_code',
        'tracking_url',
        'label_url',
        'label_format',
        'quote_snapshot',
        'metadata',
        'generated_at',
        'posted_at',
        'delivered_at',
        'cancelled_at',
        'monitor_checked_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ShipmentStatus::class,
            'packages' => 'array', 'attempt' => 'integer', 'operation_started_at' => 'datetime',
            'customer_price_cents' => 'integer',
            'provider_price_cents' => 'integer',
            'quote_snapshot' => 'array',
            'metadata' => 'array',
            'generated_at' => 'datetime',
            'posted_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'monitor_checked_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ShipmentEvent::class)->orderBy('id');
    }
}

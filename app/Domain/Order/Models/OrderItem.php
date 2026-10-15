<?php

declare(strict_types=1);

namespace App\Domain\Order\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'product_sku',
        'variant_name',
        'variant_value',
        'regular_unit_price',
        'unit_price',
        'variant_attributes_snapshot', 'shipping_measurements_snapshot',
        'quantity',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'regular_unit_price' => 'integer',
            'unit_price' => 'integer',
            'variant_attributes_snapshot' => 'array', 'shipping_measurements_snapshot' => 'array',
            'quantity' => 'integer',
            'total' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

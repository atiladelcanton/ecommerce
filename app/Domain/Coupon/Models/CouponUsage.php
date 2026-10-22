<?php

declare(strict_types=1);

namespace App\Domain\Coupon\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CouponUsage extends Model
{
    protected $fillable = ['coupon_id', 'order_id', 'document_hash', 'status', 'discount_cents', 'consumed_at', 'released_at'];

    protected function casts(): array
    {
        return ['discount_cents' => 'integer', 'consumed_at' => 'datetime', 'released_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Order\Models\Order::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}

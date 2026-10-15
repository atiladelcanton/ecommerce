<?php

declare(strict_types=1);

namespace App\Domain\Order\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderShippingQuote extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['price_cents' => 'integer', 'revision' => 'integer', 'estimated_days' => 'integer', 'expires_at' => 'immutable_datetime', 'accepted_at' => 'immutable_datetime', 'superseded_at' => 'immutable_datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

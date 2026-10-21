<?php

declare(strict_types=1);

namespace App\Domain\Payment\Models;

use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'gateway',
        'external_id',
        'status',
        'amount_cents',
        'is_demo',
        'payment_integration_id', 'environment', 'idempotency_key', 'checkout_url',
        'provider_status', 'status_detail', 'expires_at', 'last_checked_at', 'needs_review', 'review_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'amount_cents' => 'integer',
            'is_demo' => 'boolean',
            'expires_at' => 'datetime', 'last_checked_at' => 'datetime', 'needs_review' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Finance\Models;

use App\Domain\Finance\Enums\CashFlowType;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CashFlowEntry extends Model
{
    protected $fillable = [
        'type',
        'category',
        'description',
        'amount_cents',
        'occurred_at',
        'order_id',
        'payment_id',
        'source',
        'notes',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => CashFlowType::class,
            'amount_cents' => 'integer',
            'occurred_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isAutomatic(): bool
    {
        return $this->source !== 'manual';
    }
}

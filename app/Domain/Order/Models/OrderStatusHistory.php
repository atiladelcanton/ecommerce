<?php

declare(strict_types=1);

namespace App\Domain\Order\Models;

use App\Domain\Order\Enums\OrderStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderStatusHistory extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'from_status',
        'to_status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'from_status' => OrderStatus::class,
            'to_status' => OrderStatus::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

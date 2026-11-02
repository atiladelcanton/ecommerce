<?php

declare(strict_types=1);

namespace App\Domain\Influencer\Models;

use App\Domain\Coupon\Models\Coupon;
use App\Domain\Order\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class InfluencerCommission extends Model
{
    protected $fillable = [
        'influencer_id', 'coupon_id', 'order_id', 'base_cents', 'commission_bps', 'amount_cents',
        'status', 'approved_at', 'paid_at', 'reversed_at', 'reviewed_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'base_cents' => 'integer', 'amount_cents' => 'integer', 'commission_bps' => 'integer',
            'approved_at' => 'datetime', 'paid_at' => 'datetime', 'reversed_at' => 'datetime',
        ];
    }

    public function influencer(): BelongsTo
    {
        return $this->belongsTo(Influencer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public static function statuses(): array
    {
        return [
            'pending' => 'Pedido pendente', 'forecast' => 'Prevista', 'approved' => 'Aprovada',
            'paid' => 'Paga', 'cancelled' => 'Cancelada', 'review' => 'Em revisão', 'reversed' => 'Revertida',
        ];
    }
}

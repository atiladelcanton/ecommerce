<?php

declare(strict_types=1);

namespace App\Domain\Influencer\Services;

use App\Domain\Influencer\Models\InfluencerCommission;
use App\Domain\Order\Models\Order;

/** Called inside the existing coupon/order transaction. Idempotent per order. */
final class SyncInfluencerCommission
{
    public function execute(Order $order, string $event): void
    {
        if ($order->coupon_id === null) {
            return;
        }

        $coupon = $order->coupon()->with('influencer')->first();
        if (! $coupon?->influencer_id) {
            return;
        }

        $commission = InfluencerCommission::query()->where('order_id', $order->id)->lockForUpdate()->first();
        if ($commission === null) {
            // Existing coupon ownership is snapshotted at reservation to avoid retroactive reassignment.
            $bps = (int) $coupon->influencer->commission_bps;
            $base = max(0, (int) $order->subtotal_cents - (int) $order->discount_cents);
            $commission = InfluencerCommission::query()->create([
                'order_id' => $order->id, 'coupon_id' => $coupon->id,
                'influencer_id' => $coupon->influencer_id,
                'base_cents' => $base, 'commission_bps' => $bps,
                'amount_cents' => intdiv($base * $bps + 5000, 10000), 'status' => 'pending',
            ]);
        }

        if ($event === 'reserved' && $commission->status === 'cancelled') {
            $commission->update(['status' => 'pending']);
        } elseif ($event === 'consumed' && $commission->status === 'pending') {
            $commission->update(['status' => 'forecast']);
        } elseif ($event === 'released' && $commission->status === 'pending') {
            $commission->update(['status' => 'cancelled']);
        } elseif (in_array($event, ['cancelled', 'refunded'], true) && in_array($commission->status, ['forecast', 'approved'], true)) {
            // Cancelled order does not prove a refund. Pause for human review.
            $commission->update(['status' => 'review', 'notes' => 'Cancelamento ou reembolso após pagamento: confirmar situação financeira antes de reverter comissão.']);
        }
    }
}

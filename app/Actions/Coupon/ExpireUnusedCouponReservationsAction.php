<?php

declare(strict_types=1);

namespace App\Actions\Coupon;

use App\Domain\Coupon\Models\CouponUsage;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;

/** Reclaims abandoned, unpaid coupon claims after 24h; never touches active remote charges. */
final class ExpireUnusedCouponReservationsAction
{
    public function execute(int $limit = 100): int
    {
        $count = 0;
        $usages = CouponUsage::query()->where('status', 'reserved')
            ->where('created_at', '<=', now()->subHours(24))
            ->whereHas('order', fn ($query) => $query->where('payment_status', '!=', PaymentStatus::APPROVED->value)
                ->whereDoesntHave('payments', fn ($payments) => $payments->where('status', PaymentStatus::PENDING->value)))
            ->limit($limit)->pluck('order_id');
        foreach ($usages as $id) {
            DB::transaction(function () use ($id, &$count): void {
                $order = Order::query()->lockForUpdate()->find($id);
                if (! $order || $order->payment_status === PaymentStatus::APPROVED || $order->payments()->where('status', PaymentStatus::PENDING->value)->exists()) {
                    return;
                }
                $usage = CouponUsage::query()->where('order_id', $order->id)->first();
                if ($usage?->status === 'reserved' && $usage->created_at->lte(now()->subHours(24))) {
                    app(ManageCouponUsageAction::class)->release($order);
                    $count++;
                }
            });
        }
        return $count;
    }
}

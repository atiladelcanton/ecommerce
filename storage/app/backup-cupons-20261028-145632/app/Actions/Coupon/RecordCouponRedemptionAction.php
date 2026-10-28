<?php

declare(strict_types=1);

namespace App\Actions\Coupon;

use App\Domain\Coupon\Models\CouponRedemption;
use App\Domain\Order\Models\Order;
use Illuminate\Validation\ValidationException;

final class RecordCouponRedemptionAction
{
    // Caller owns the payment transaction and the order row lock.
    public function execute(Order $order): void
    {
        if ($order->coupon_id === null) {
            return;
        }
        if (CouponRedemption::query()->where('order_id', $order->id)->exists()) {
            return;
        }
        $offer = app(ValidateCouponAction::class)->execute((string) $order->coupon_code, $order->subtotal_cents, lock: true);
        if ($offer['coupon']->id !== $order->coupon_id || $order->discount_cents <= 0) {
            throw ValidationException::withMessages(['coupon' => 'O cupom deste pedido não está mais disponível.']);
        }
        CouponRedemption::query()->create([
            'coupon_id' => $offer['coupon']->id,
            'order_id' => $order->id,
            'discount_cents' => $order->discount_cents,
        ]);
        $offer['coupon']->increment('used_count');
    }
}

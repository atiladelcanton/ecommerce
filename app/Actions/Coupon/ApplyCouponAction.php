<?php

declare(strict_types=1);

namespace App\Actions\Coupon;

use App\Actions\Cart\GetCartAction;
use App\Actions\Order\GetCheckoutTokenAction;
use App\Support\CheckoutCoupon;
use Illuminate\Validation\ValidationException;

/** Shares the same checkout-token session state as the Livewire checkout. */
final class ApplyCouponAction
{
    public function apply(string $code): void
    {
        $cart = app(GetCartAction::class)->execute();
        if ($cart['items'] === []) {
            throw ValidationException::withMessages(['coupon' => 'Adicione produtos antes de aplicar um cupom.']);
        }
        $offer = app(QuoteCouponAction::class)->execute($code, array_values($cart['items']), $cart['subtotal']);
        app(CheckoutCoupon::class)->set(app(GetCheckoutTokenAction::class)->execute(), $offer['coupon']->code);
    }

    public function remove(): void
    {
        app(CheckoutCoupon::class)->forget(app(GetCheckoutTokenAction::class)->execute());
    }

    public function current(): ?string
    {
        return app(CheckoutCoupon::class)->get(app(GetCheckoutTokenAction::class)->execute());
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Actions\Cart\GetCartAction;
use App\Actions\Shipping\QuoteShippingAction;
use App\Actions\Coupon\QuoteCouponAction;
use App\Domain\Order\OrderTotals;
use App\Domain\Settings\Models\StoreSetting;
use App\Domain\Shipping\Data\ShippingOption;
use Illuminate\Validation\ValidationException;

final class GetCheckoutSummaryAction
{
    public function __construct(private readonly GetCartAction $cart, private readonly QuoteShippingAction $shipping) {}

    public function execute(string $postalCode = '', string $selected = 'standard', ?string $couponCode = null): array
    {
        $cart = $this->cart->execute();
        $options = [];
        $shipping = null;
        $shippingError = null;
        if ($cart['items'] !== [] && ! ($cart['stockError'] ?? null)) {
            try {
                $options = $this->shipping->execute($cart, preg_replace('/\D/', '', $postalCode));
                $shipping = collect($options)->first(fn (ShippingOption $option): bool => $option->id === $selected);
            } catch (ValidationException $exception) {
                $shippingError = $exception->validator->errors()->first();
            }
        }

        $coupon = null;
        $discount = 0;
        $couponError = null;
        if ($couponCode !== null) {
            try {
                $offer = app(QuoteCouponAction::class)->execute($couponCode, array_values($cart['items']), $cart['subtotal']);
                $coupon = $offer['coupon'];
                $discount = $offer['discount'];
            } catch (ValidationException $exception) {
                $couponError = $exception->validator->errors()->first();
            }
        }
        $totals = null;
        $totalError = $cart['stockError'] ?? null;
        try {
            $totals = new OrderTotals($cart['subtotal'], $shipping?->price ?? 0, $discount);
        } catch (ValidationException $exception) {
            $totalError = $exception->validator->errors()->first();
        }

        return [
            'cart' => $cart,
            'canRequestShippingQuote' => strlen(preg_replace('/\D/', '', $postalCode)) === 8 && $cart['items'] !== [] && ! $totalError && ! collect($options)->contains(fn (ShippingOption $option) => $option->fulfillmentMode !== 'pickup'),
            'shippingContact' => StoreSetting::query()->find(1)?->whatsapp,
            'shippingOptions' => $options,
            'selectedShipping' => $shipping,
            'totals' => $totals,
            'coupon' => $coupon,
            'couponError' => $couponError,
            'discount' => $discount,
            'totalError' => $totalError,
            'shippingError' => $shippingError,
        ];
    }
}

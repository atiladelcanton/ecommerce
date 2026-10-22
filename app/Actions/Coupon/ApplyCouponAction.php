<?php

declare(strict_types=1);

namespace App\Actions\Coupon;

use App\Actions\Cart\GetCartAction;
use Illuminate\Contracts\Session\Session;
use Illuminate\Validation\ValidationException;

final class ApplyCouponAction
{
    public const SESSION_KEY = 'store.coupon_code';

    public function __construct(private readonly Session $session) {}

    public function apply(string $code): void
    {
        $cart = app(GetCartAction::class)->execute();
        if ($cart['items'] === []) {
            throw ValidationException::withMessages(['coupon' => 'Adicione produtos antes de aplicar um cupom.']);
        }
        $code = QuoteCouponAction::normalize($code);
        app(QuoteCouponAction::class)->execute($code, array_values($cart['items']), $cart['subtotal']);
        $this->session->put(self::SESSION_KEY, $code);
    }

    public function remove(): void
    {
        $this->session->forget(self::SESSION_KEY);
    }

    public function current(): ?string
    {
        $code = $this->session->get(self::SESSION_KEY);
        return is_string($code) && $code !== '' ? $code : null;
    }
}

<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Contracts\Session\Session;

final class CheckoutCoupon
{
    private const KEY = 'store.checkout.coupon.';

    public function __construct(private readonly Session $session) {}

    public function get(string $token): ?string
    {
        $value = $this->session->get(self::KEY.$token);

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function set(string $token, string $code): void
    {
        $this->session->put(self::KEY.$token, strtoupper(trim($code)));
    }

    public function forget(string $token): void
    {
        $this->session->forget(self::KEY.$token);
    }
}

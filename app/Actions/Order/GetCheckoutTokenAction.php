<?php

declare(strict_types=1);

namespace App\Actions\Order;

use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Str;

final class GetCheckoutTokenAction
{
    public const SESSION_KEY = 'store.checkout.token';

    public function __construct(private readonly Session $session) {}

    public function execute(): string
    {
        $token = $this->session->get(self::SESSION_KEY);
        if (! is_string($token) || ! Str::isUuid($token)) {
            $token = (string) Str::uuid();
            $this->session->put(self::SESSION_KEY, $token);
        }

        return $token;
    }
}

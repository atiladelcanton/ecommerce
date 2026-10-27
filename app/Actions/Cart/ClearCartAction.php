<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Infrastructure\Cart\SessionCart;

final class ClearCartAction
{
    public function __construct(private readonly SessionCart $cart) {}

    public function execute(): void
    {
        $this->cart->clear();
    }
}

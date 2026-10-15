<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Infrastructure\Cart\SessionCart;

final class RemoveCartItemAction
{
    public function __construct(private readonly SessionCart $cart) {}

    public function execute(string $key): void
    {
        $items = $this->cart->items();
        unset($items[$key]);
        $this->cart->replace($items);
    }
}

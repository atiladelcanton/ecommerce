<?php

declare(strict_types=1);

namespace App\Infrastructure\Cart;

use App\Domain\Cart\CartItem;
use Illuminate\Contracts\Session\Session;

final class SessionCart
{
    public const SESSION_KEY = 'store.cart.items';

    public function __construct(private readonly Session $session) {}

    /** @return array<string, CartItem> */
    public function items(): array
    {
        $items = [];
        foreach ($this->session->get(self::SESSION_KEY, []) as $data) {
            $item = new CartItem($data['product_id'], $data['product_variant_id'], $data['quantity']);
            $items[$item->key()] = $item;
        }

        return $items;
    }

    /** @param array<string, CartItem> $items */
    public function replace(array $items): void
    {
        $this->session->put(self::SESSION_KEY, array_map(fn (CartItem $item): array => $item->toArray(), $items));
    }

    public function clear(): void
    {
        $this->session->forget(self::SESSION_KEY);
    }
}

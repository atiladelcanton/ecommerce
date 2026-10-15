<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Domain\Cart\CartItem;
use App\Infrastructure\Cart\SessionCart;

final class AddCartItemAction
{
    public function __construct(
        private readonly SessionCart $cart,
        private readonly GetCartProductAction $products,
        private readonly ValidateCartProduct $validateProduct,
        private readonly GetCartAction $getCart,
    ) {}

    public function execute(int $productId, ?int $variantId = null, int $quantity = 1): void
    {
        $product = $this->products->execute($productId);
        $this->validateProduct->execute($product, $variantId);
        $item = new CartItem($productId, $variantId, $quantity);
        $items = $this->cart->items();
        $item = new CartItem($productId, $variantId, ($items[$item->key()]->quantity ?? 0) + $quantity);
        $items[$item->key()] = $item;
        $summary = $this->getCart->summarize($items, validateStock: true);
        $this->cart->replace(array_map(fn (array $line): CartItem => $line['item'], $summary['items']));
    }
}

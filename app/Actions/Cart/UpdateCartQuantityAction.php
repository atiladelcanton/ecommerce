<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Domain\Cart\CartItem;
use App\Infrastructure\Cart\SessionCart;
use Illuminate\Validation\ValidationException;

final class UpdateCartQuantityAction
{
    public function __construct(
        private readonly SessionCart $cart,
        private readonly GetCartProductAction $products,
        private readonly ValidateCartProduct $validateProduct,
        private readonly GetCartAction $getCart,
    ) {}

    public function execute(string $key, int $quantity): void
    {
        $items = $this->cart->items();
        $current = $items[$key] ?? null;
        if (! $current) {
            throw ValidationException::withMessages(['quantity' => 'Este item não está mais no carrinho.']);
        }
        $product = $this->products->execute($current->productId);
        $this->validateProduct->execute($product, $current->productVariantId);
        $item = new CartItem($current->productId, $current->productVariantId, $quantity);
        $items[$key] = $item;
        $summary = $this->getCart->summarize($items, validateStock: true);
        $this->cart->replace(array_map(fn (array $line): CartItem => $line['item'], $summary['items']));
    }

    public function adjust(string $key, int $delta): void
    {
        if (! in_array($delta, [-1, 1], true)) {
            throw ValidationException::withMessages(['quantity' => 'Alteração de quantidade inválida.']);
        }
        $current = $this->cart->items()[$key] ?? null;
        if (! $current) {
            throw ValidationException::withMessages(['quantity' => 'Este item não está mais no carrinho.']);
        }
        $this->execute($key, $current->quantity + $delta);
    }
}

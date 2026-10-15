<?php

declare(strict_types=1);

namespace App\Domain\Cart;

use Illuminate\Validation\ValidationException;

final readonly class CartItem
{
    public const MAX_QUANTITY = 999;

    public function __construct(
        public int $productId,
        public ?int $productVariantId,
        public int $quantity,
    ) {
        if ($quantity < 1 || $quantity > self::MAX_QUANTITY) {
            throw ValidationException::withMessages(['quantity' => 'Informe uma quantidade entre 1 e 999.']);
        }
    }

    public function key(): string
    {
        return $this->productId.':'.($this->productVariantId ?? 'none');
    }

    public function subtotal(int $unitPrice): int
    {
        if ($unitPrice > intdiv(PHP_INT_MAX, $this->quantity)) {
            throw ValidationException::withMessages(['quantity' => 'O valor total excede o limite permitido. Reduza a quantidade.']);
        }

        return $unitPrice * $this->quantity;
    }

    /** @return array{product_id: int, product_variant_id: ?int, quantity: int} */
    public function toArray(): array
    {
        return ['product_id' => $this->productId, 'product_variant_id' => $this->productVariantId, 'quantity' => $this->quantity];
    }
}

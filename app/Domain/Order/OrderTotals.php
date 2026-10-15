<?php

declare(strict_types=1);

namespace App\Domain\Order;

use Illuminate\Validation\ValidationException;

final readonly class OrderTotals
{
    public int $total;

    public function __construct(public int $subtotal, public int $shipping, public int $discount = 0)
    {
        if ($subtotal < 0 || $shipping < 0 || $discount < 0 || $subtotal > PHP_INT_MAX - $shipping) {
            throw ValidationException::withMessages(['checkout' => 'O valor do pedido excede o limite permitido. Revise seu carrinho.']);
        }
        $gross = $subtotal + $shipping;
        if ($discount > $gross) {
            throw ValidationException::withMessages(['checkout' => 'O desconto não pode exceder o valor do pedido.']);
        }
        $this->total = $gross - $discount;
    }
}

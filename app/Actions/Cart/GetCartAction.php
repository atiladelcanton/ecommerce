<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Actions\Catalog\ResolveProductOfferAction;
use App\Domain\Cart\CartItem;
use App\Domain\Catalog\Services\InventoryAvailability;
use App\Infrastructure\Cart\SessionCart;
use Illuminate\Validation\ValidationException;

final class GetCartAction
{
    public function __construct(
        private readonly SessionCart $cart,
        private readonly GetCartProductAction $products,
        private readonly ValidateCartProduct $validateProduct,
    ) {}

    public function execute(): array
    {
        $stored = $this->cart->items();
        $result = $this->summarize($stored);
        if (count($result['items']) !== count($stored)) {
            $this->cart->replace(array_map(fn (array $line): CartItem => $line['item'], $result['items']));
        }

        return $result;
    }

    /** @param array<string, CartItem> $stored */
    public function summarize(array $stored, bool $validateStock = false): array
    {
        $products = $stored === [] ? collect() : $this->products->query()
            ->whereKey(array_map(fn (CartItem $item): int => $item->productId, $stored))->get()->keyBy('id');
        $items = [];
        $subtotal = 0;
        $count = 0;

        foreach ($stored as $key => $item) {
            $product = $products->get($item->productId);
            try {
                if (! $product) {
                    continue;
                }
                $variant = $this->validateProduct->execute($product, $item->productVariantId);
            } catch (ValidationException) {
                continue;
            }

            $offer = app(ResolveProductOfferAction::class)->execute($product, $variant);
            $unitPrice = $offer->pricing->finalPrice();
            $lineSubtotal = $item->subtotal($unitPrice);
            if ($subtotal > PHP_INT_MAX - $lineSubtotal) {
                throw ValidationException::withMessages(['quantity' => 'O valor total excede o limite permitido. Reduza a quantidade.']);
            }
            $subtotal += $lineSubtotal;
            $count += $item->quantity;
            $items[$key] = ['item' => $item, 'product' => $product, 'variant' => $variant, 'unitPrice' => $unitPrice, 'subtotal' => $lineSubtotal, 'offer' => $offer];
        }

        $pools = [];
        $stockError = null;
        foreach ($items as $line) {
            $inventory = app(InventoryAvailability::class);
            $pool = $inventory->pool($line['product'], $line['variant']);
            if ($pool) {
                $poolKey = $pool::class.':'.$pool->id;
                $pools[$poolKey] = ($pools[$poolKey] ?? 0) + $line['item']->quantity;
                try {
                    $inventory->assertQuantity($line['product'], $line['variant'], $pools[$poolKey]);
                } catch (ValidationException $exception) {
                    if ($validateStock) {
                        throw $exception;
                    }
                    $stockError = $exception->getMessage();
                }
            }
        }

        return array_merge(['items' => $items, 'subtotal' => $subtotal, 'count' => $count], $stockError !== null ? ['stockError' => $stockError] : []);
    }
}

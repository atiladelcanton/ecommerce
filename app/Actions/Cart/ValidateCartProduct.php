<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Domain\Catalog\Enums\ProductAvailability;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use Illuminate\Validation\ValidationException;

final class ValidateCartProduct
{
    public function execute(Product $product, ?int $variantId): ?ProductVariant
    {
        if (! $product->is_active || $product->availability === ProductAvailability::UNAVAILABLE) {
            throw ValidationException::withMessages(['product' => 'Este produto não está disponível para compra.']);
        }

        $label = $product->variants->isNotEmpty() && $product->variants->every(fn ($variant): bool => array_keys($variant->characteristics()) === ['Cor']) ? 'cor' : 'variação';
        if ($variantId === null) {
            if ($product->variants->isNotEmpty()) {
                throw ValidationException::withMessages(['productVariantId' => 'Selecione uma '.$label.' antes de adicionar ao carrinho.']);
            }

            return null;
        }

        $variant = $product->variants->firstWhere('id', $variantId);
        if (! $variant || ! $variant->is_active) {
            throw ValidationException::withMessages(['productVariantId' => 'Selecione uma '.$label.' disponível deste produto.']);
        }

        return $variant;
    }
}

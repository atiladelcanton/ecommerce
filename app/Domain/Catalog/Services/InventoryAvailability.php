<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Services;

use App\Domain\Catalog\Enums\ProductAvailability;
use App\Domain\Catalog\Models\InventoryReservation;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

final class InventoryAvailability
{
    public function pool(Product $product, ?ProductVariant $variant): ?Model
    {
        if ($product->availability !== ProductAvailability::IMMEDIATE) {
            return null;
        }

        return $variant?->stock !== null ? $variant : ($product->stock !== null ? $product : null);
    }

    public function available(Model $pool, ?int $excludingItemId = null): int
    {
        $reserved = InventoryReservation::query()->where('product_id', $pool instanceof Product ? $pool->id : $pool->product_id)
            ->where('pool_type', $pool instanceof Product ? 'product' : 'variant')
            ->when($pool instanceof ProductVariant, fn ($q) => $q->where('product_variant_id', $pool->id))
            ->whereNull('consumed_at')->whereNull('released_at')->where(fn ($q) => $q->where('expires_at', '>', now())->orWhere('payment_hold', true))
            ->when($excludingItemId !== null, fn ($q) => $q->where('order_item_id', '!=', $excludingItemId))->sum('quantity');

        return max(0, (int) $pool->stock - (int) $reserved);
    }

    public function assertQuantity(Product $product, ?ProductVariant $variant, int $quantity): void
    {
        $pool = $this->pool($product, $variant);
        if ($pool && $quantity > $this->available($pool)) {
            throw ValidationException::withMessages(['quantity' => 'Quantidade indisponível para '.$product->name.'. Revise o carrinho; há unidades reservadas ou o estoque é insuficiente.']);
        }
    }

    public function assertCanDelete(Product $product): void
    {
        if (InventoryReservation::query()->where('product_id', $product->id)->whereNull('consumed_at')->whereNull('released_at')->where(fn ($q) => $q->where('expires_at', '>', now())->orWhere('payment_hold', true))->exists()) {
            throw ValidationException::withMessages(['product' => 'Este produto possui unidades reservadas. Cancele os pedidos ou aguarde o vencimento da reserva antes de excluir.']);
        }
    }

    public function assertStockChange(Model $pool, ?int $stock): void
    {
        $reserved = (int) $pool->stock - $this->available($pool);
        if (($stock === null && $reserved > 0) || ($stock !== null && $stock < $reserved)) {
            throw ValidationException::withMessages(['stock' => 'O estoque não pode ser menor que a quantidade reservada em pedidos.']);
        }
    }
}

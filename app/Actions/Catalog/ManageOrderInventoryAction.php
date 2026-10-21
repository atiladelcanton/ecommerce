<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Domain\Catalog\Enums\ProductAvailability;
use App\Domain\Catalog\Models\InventoryReservation;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Catalog\Services\InventoryAvailability;
use App\Domain\Order\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ManageOrderInventoryAction
{
    public function __construct(private readonly InventoryAvailability $availability) {}

    public function reserve(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            $items = $order->items()->orderBy('product_id')->orderBy('product_variant_id')->get();
            $products = Product::query()->whereIn('id', $items->pluck('product_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $variants = ProductVariant::query()->whereIn('id', $items->pluck('product_variant_id')->filter())->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            foreach ($items as $item) {
                $existing = InventoryReservation::query()->where('order_item_id', $item->id)->lockForUpdate()->first();
                if ($existing && ($existing->consumed_at || (! $existing->released_at && ($existing->payment_hold || $existing->expires_at->isFuture())))) {
                    continue;
                }
                $product = $products->get($item->product_id);
                $variant = $variants->get($item->product_variant_id);
                if (! $product || ! $product->is_active || $product->availability === ProductAvailability::UNAVAILABLE || ($item->product_variant_id && (! $variant || ! $variant->is_active))) {
                    throw ValidationException::withMessages(['cart' => 'Um dos itens não está mais disponível. Revise seu carrinho.']);
                }
                $this->availability->assertQuantity($product, $variant, $item->quantity);
                $pool = $this->availability->pool($product, $variant);
                if (! $pool) {
                    continue;
                }
                $reservationData = [
                    'order_item_id' => $item->id, 'product_id' => $product->id,
                    'product_variant_id' => $pool instanceof ProductVariant ? $pool->id : null,
                    'pool_type' => $pool instanceof ProductVariant ? 'variant' : 'product',
                    'quantity' => $item->quantity, 'expires_at' => now()->addHours(24), 'released_at' => null, 'payment_hold' => false,
                ];
                if ($existing) {
                    $existing->update($reservationData);
                } else {
                    InventoryReservation::query()->create($reservationData);
                }
            }
        }, 3);
    }

    public function consume(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            Order::query()->lockForUpdate()->findOrFail($order->id);
            $rows = $this->reservations($order);
            // Always lock parents before variants, including in payments and cancellations.
            $products = Product::query()->whereIn('id', $rows->pluck('product_id')->filter())->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $variants = ProductVariant::query()->whereIn('id', $rows->pluck('product_variant_id')->filter())->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            foreach ($rows as $row) {
                if ($row->consumed_at) {
                    continue;
                }
                $pool = $row->pool_type === 'variant' ? $variants->get($row->product_variant_id) : $products->get($row->product_id);
                if (! $pool || $row->released_at || $this->availability->available($pool, $row->order_item_id) < $row->quantity) {
                    throw ValidationException::withMessages(['stock' => 'A reserva expirou ou o estoque não está mais disponível. Entre em contato com a loja.']);
                }
                $pool->decrement('stock', $row->quantity);
                $row->update(['consumed_at' => now(), 'payment_hold' => false]);
            }
        }, 3);
    }

    public function cancel(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            Order::query()->lockForUpdate()->findOrFail($order->id);
            $rows = $this->reservations($order);
            $products = Product::query()->whereIn('id', $rows->pluck('product_id')->filter())->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $variants = ProductVariant::query()->whereIn('id', $rows->pluck('product_variant_id')->filter())->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            foreach ($rows as $row) {
                if ($row->consumed_at && ! $row->restored_at) {
                    $pool = $row->pool_type === 'variant' ? $variants->get($row->product_variant_id) : $products->get($row->product_id);
                    $pool?->increment('stock', $row->quantity);
                    $row->restored_at = now();
                }
                $row->payment_hold = false;
                $row->released_at ??= now();
                $row->save();
            }
        }, 3);
    }

    /** While a remote charge is pending, do not release stock at local timeout. */
    public function holdForPayment(Order $order): void
    {
        $this->reserve($order);
        InventoryReservation::query()->whereIn('order_item_id', $order->items()->select('id'))
            ->whereNull('consumed_at')->whereNull('released_at')->update(['payment_hold' => true]);
    }

    public function releasePaymentHold(Order $order): void
    {
        InventoryReservation::query()->whereIn('order_item_id', $order->items()->select('id'))
            ->whereNull('consumed_at')->update(['payment_hold' => false, 'released_at' => now()]);
    }

    public function expire(): int
    {
        return InventoryReservation::query()->where('payment_hold', false)->whereNull('consumed_at')->whereNull('released_at')->where('expires_at', '<=', now())->update(['released_at' => now()]);
    }

    private function reservations(Order $order): Collection
    {
        return InventoryReservation::query()->whereIn('order_item_id', $order->items()->select('id'))->orderBy('id')->lockForUpdate()->get();
    }
}

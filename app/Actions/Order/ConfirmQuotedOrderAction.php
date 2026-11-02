<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Actions\Catalog\ManageOrderInventoryAction;
use App\Domain\Catalog\Enums\ProductAvailability;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Shipping\Enums\ShippingPricingStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ConfirmQuotedOrderAction
{
    public function execute(string $number, int $revision, string $signedUrl): Order
    {
        $authorized = app(GetQuotedOrderAction::class)->execute($number, $revision, $signedUrl);

        $confirmed = DB::transaction(function () use ($authorized, $number, $revision, $signedUrl): Order {
            $order = Order::query()->lockForUpdate()->findOrFail($authorized->id);
            // Recheck revision and expiry after acquiring the order lock.
            app(GetQuotedOrderAction::class)->execute($number, $revision, $signedUrl);
            if ($order->status === OrderStatus::CANCELLED) {
                throw ValidationException::withMessages(['payment' => 'Este pedido foi cancelado. Entre em contato com a loja.']);
            }
            if ($order->payment_status === PaymentStatus::APPROVED) {
                return $order;
            }
            if ($order->status !== OrderStatus::RECEIVED || ! in_array($order->shipping_pricing_status, [ShippingPricingStatus::QUOTED, ShippingPricingStatus::CONFIRMED], true)) {
                throw ValidationException::withMessages(['payment' => 'Este pedido não está disponível para confirmação.']);
            }
            if ($order->shipping_pricing_status === ShippingPricingStatus::CONFIRMED) {
                return $order; // A second click must resume, not create a second payment.
            }
            if ($order->payments()->where('status', PaymentStatus::PENDING->value)->exists()) {
                throw ValidationException::withMessages(['payment' => 'Existe uma cobrança pendente. Aguarde a confirmação antes de alterar o frete.']);
            }
            $items = $order->items()->orderBy('product_id')->get();
            $products = Product::query()->whereIn('id', $items->pluck('product_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $variants = ProductVariant::query()->whereIn('id', $items->pluck('product_variant_id')->filter())->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            foreach ($items as $item) {
                $product = $products->get($item->product_id);
                $variant = $variants->get($item->product_variant_id);
                if (! $product || ! $product->is_active || $product->availability === ProductAvailability::UNAVAILABLE || ($item->product_variant_id === null && $product->variants()->where('is_active', true)->exists()) || ($item->variant_value !== null && (! $variant || ! $variant->is_active || $variant->characteristics() != $item->variant_attributes_snapshot))) {
                    throw ValidationException::withMessages(['stock' => 'Um dos itens não está mais disponível nessa configuração. Entre em contato com a loja.']);
                }
            }
            app(ManageOrderInventoryAction::class)->reserve($order);
            $order->update(['shipping_pricing_status' => ShippingPricingStatus::CONFIRMED]);
            $order->latestShippingQuote()->firstOrFail()->update(['accepted_at' => now()]);

            $order->statusHistories()->create(['from_status' => $order->status, 'to_status' => $order->status, 'description' => 'Cliente aceitou a cotação de frete (revisão '.$revision.').']);
            return $order;
        }, 3);

        // Network IO must run after committing acceptance and the stock reservation.
        // A rejected/uncertain checkout cannot erase the accepted quote.
        return app(PayOrderAction::class)->execute($confirmed);
    }
}

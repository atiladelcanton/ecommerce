<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Models\OrderShippingQuote;
use App\Domain\Order\OrderTotals;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Shipping\Data\ShippingOption;
use App\Domain\Shipping\Enums\ShippingPricingStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class PublishOrderShippingQuoteAction
{
    public function execute(Order $order, array $input, int $userId): OrderShippingQuote
    {
        $data = Validator::make($input, [
            'carrier_name' => ['required', 'string', 'max:255'], 'service_name' => ['required', 'string', 'max:255'],
            'price_cents' => ['required', 'integer', 'min:0'], 'estimated_days' => ['nullable', 'integer', 'between:0,365'],
            'instructions' => ['nullable', 'string', 'max:2000'], 'expires_at' => ['required', 'date', 'after:now'],
        ])->validate();
        if (! is_int($data['price_cents'])) {
            throw ValidationException::withMessages(['price_cents' => 'Informe o frete em centavos inteiros.']);
        }

        return DB::transaction(function () use ($order, $data, $userId): OrderShippingQuote {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($order->status !== OrderStatus::RECEIVED || $order->payment_status === PaymentStatus::APPROVED || $order->shipping_pricing_status === null) {
                throw ValidationException::withMessages(['shipping' => 'Somente pedidos aguardando conclusão podem receber uma cotação manual.']);
            }
            $totals = new OrderTotals($order->subtotal_cents, $data['price_cents'], $order->discount_cents);
            $last = $order->shippingQuotes()->first();
            $order->shippingQuotes()->whereNull('superseded_at')->whereNull('accepted_at')->update(['superseded_at' => now()]);
            $quote = $order->shippingQuotes()->create(array_merge($data, ['revision' => ($last?->revision ?? 0) + 1, 'user_id' => $userId]));
            $option = new ShippingOption(id: 'manual:'.$quote->id, name: $quote->service_name, price: $quote->price_cents,
                description: $quote->estimated_days !== null ? $quote->estimated_days.' dias úteis' : 'Prazo combinado com a SABA',
                provider: 'manual', carrierName: $quote->carrier_name, estimatedDays: $quote->estimated_days,
                fulfillmentMode: 'manual', metadata: ['quote_id' => $quote->id, 'revision' => $quote->revision, 'instructions' => $quote->instructions]);
            $order->update([
                'shipping_pricing_status' => ShippingPricingStatus::QUOTED, 'shipping_cents' => $quote->price_cents, 'total_cents' => $totals->total,
                'shipping_option_id' => $option->id, 'shipping_provider' => 'manual', 'shipping_name' => $option->name,
                'shipping_description' => $option->description, 'carrier' => $option->carrierName, 'shipping_quote_snapshot' => $option->snapshot(),
            ]);
            $order->statusHistories()->create(['user_id' => $userId, 'from_status' => $order->status, 'to_status' => $order->status, 'description' => 'Cotação de frete publicada — revisão '.$quote->revision.'.']);

            return $quote;
        }, 3);
    }
}

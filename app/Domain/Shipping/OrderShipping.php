<?php

declare(strict_types=1);

namespace App\Domain\Shipping;

use App\Domain\Order\Models\Order;
use App\Domain\Shipping\Contracts\ShipmentProvider;
use App\Domain\Shipping\Models\Shipment;
use Illuminate\Support\Collection;

final class OrderShipping
{
    public static function mode(Order $order): string
    {
        $snapshot = $order->shipping_quote_snapshot ?? [];
        if (isset($snapshot['fulfillment_mode'])) {
            return $snapshot['fulfillment_mode'];
        }
        // Compatibility with orders predating independent fulfillment providers.
        if ($order->shipping_option_id === 'pickup' || str_ends_with((string) $order->shipping_option_id, ':pickup') || $order->shipping_provider === 'local_pickup') {
            return 'pickup';
        }

        $class = config('shipping.providers.'.$order->shipping_provider);
        if (is_string($class) && is_a($class, ShipmentProvider::class, true) && filled($snapshot['metadata']['packages'] ?? [])) {
            return 'automatic';
        }

        return 'manual';
    }

    public static function snapshot(Order $order): array
    {
        $snapshot = $order->shipping_quote_snapshot ?? [];
        $snapshot += [
            'provider' => $order->shipping_provider, 'integration_id' => $order->shipping_integration_id,
            'environment' => $order->shipping_environment, 'name' => $order->shipping_name,
            'service_external_id' => $order->shipping_service_id, 'carrier_name' => $order->carrier,
            'packages' => $snapshot['metadata']['packages'] ?? [], 'fulfillment_mode' => self::mode($order),
        ];
        if (! isset($snapshot['metadata']['products'])) {
            $snapshot['metadata']['products'] = $order->items->map(fn ($item): array => [
                'id' => (string) $item->product_id, 'name' => $item->product_name.($item->variant_value ? ' - '.$item->variant_value : ''),
                'quantity' => $item->quantity, 'unit_price_cents' => $item->unit_price,
            ])->all();
        }

        return $snapshot;
    }

    public static function currentShipments(Order $order): Collection
    {
        return $order->shipments()->orderByDesc('attempt')->orderByDesc('id')->get()->unique(fn (Shipment $shipment) => $shipment->fulfillment_key ?? 'legacy-'.$shipment->id)->values();
    }
}

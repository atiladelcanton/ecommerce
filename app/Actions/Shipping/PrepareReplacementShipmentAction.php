<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Actions\Order\TransitionOrderStatusAction;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Domain\Shipping\Models\Shipment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PrepareReplacementShipmentAction
{
    public function execute(Shipment $shipment, int $userId): Shipment
    {
        return Cache::lock('shipping-label-order:'.$shipment->order_id, 600)->block(3, fn () => DB::transaction(function () use ($shipment, $userId): Shipment {
            $order = Order::query()->lockForUpdate()->findOrFail($shipment->order_id);
            $current = $order->shipments()->where('fulfillment_key', $shipment->fulfillment_key)->orderByDesc('attempt')->lockForUpdate()->firstOrFail();
            if ($current->id !== $shipment->id) {
                return $current;
            }
            if ($current->status !== ShipmentStatus::CANCELLED || ! in_array($order->status, [OrderStatus::PROCESSING, OrderStatus::READY_TO_SHIP], true)) {
                throw ValidationException::withMessages(['shipping' => 'Somente uma etiqueta cancelada de pedido ainda não enviado pode ser substituída.']);
            }
            if ($order->status === OrderStatus::READY_TO_SHIP) {
                app(TransitionOrderStatusAction::class)->execute($order, OrderStatus::PROCESSING, $userId, 'Preparação reaberta para substituir etiqueta cancelada.');
            }
            $attributes = $current->only(['order_id', 'provider', 'shipping_integration_id', 'environment', 'shipping_agency_id', 'carrier_external_id', 'carrier_name', 'service_external_id', 'service_name', 'customer_price_cents', 'packages', 'quote_snapshot', 'label_format', 'fulfillment_key']);

            return Shipment::query()->create(array_merge($attributes, ['attempt' => $current->attempt + 1, 'operation_key' => (string) Str::uuid(), 'status' => ShipmentStatus::PENDING, 'metadata' => ['replaces_shipment_id' => $current->id, 'prepared_by' => $userId]]));
        }));
    }
}

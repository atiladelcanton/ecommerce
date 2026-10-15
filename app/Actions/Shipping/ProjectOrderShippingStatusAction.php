<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Actions\Order\TransitionOrderStatusAction;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Domain\Shipping\Models\Shipment;
use App\Domain\Shipping\OrderShipping;

final class ProjectOrderShippingStatusAction
{
    public function __construct(private readonly TransitionOrderStatusAction $transition) {}

    public function execute(Order $order): void
    {
        $order->refresh();
        $shipments = OrderShipping::currentShipments($order);
        if ($shipments->isEmpty() || $shipments->contains(fn (Shipment $s) => $s->status === ShipmentStatus::CANCELLED)) {
            return;
        }
        if ($order->status === OrderStatus::PROCESSING && $shipments->every(fn (Shipment $s) => $s->generated_at || $s->posted_at || $s->delivered_at)) {
            $order = $this->transition->execute($order, OrderStatus::READY_TO_SHIP, description: 'Todas as etiquetas estão prontas para envio.', fromProvider: true);
        }
        if (in_array($order->status, [OrderStatus::PROCESSING, OrderStatus::READY_TO_SHIP], true) && $shipments->every(fn (Shipment $s) => $s->posted_at || $s->delivered_at)) {
            $order = $this->transition->execute($order, OrderStatus::SHIPPED, description: 'Postagem de todos os volumes confirmada pela transportadora.', fromProvider: true);
        }
        if ($order->status === OrderStatus::SHIPPED && $shipments->every(fn (Shipment $s) => $s->delivered_at !== null)) {
            $this->transition->execute($order, OrderStatus::DELIVERED, description: 'Entrega de todos os volumes confirmada pela transportadora.', fromProvider: true);
        }
    }
}

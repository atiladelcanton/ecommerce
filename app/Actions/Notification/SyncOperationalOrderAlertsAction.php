<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Shipping\Enums\ShipmentDocumentType;
use App\Domain\Shipping\OrderShipping;

/** Opt-in installation/backfill for currently actionable orders only. */
final class SyncOperationalOrderAlertsAction
{
    public function __construct(private readonly NotifyOrderMilestoneAction $notify) {}

    public function execute(int $limit = 100): int
    {
        $count = 0;
        $orders = Order::query()
            ->whereIn('status', [OrderStatus::PAID->value, OrderStatus::PROCESSING->value, OrderStatus::READY_TO_SHIP->value])
            ->where('payment_status', PaymentStatus::APPROVED->value)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderByDesc('id')
            ->limit(max(1, min(300, $limit)))
            ->get();

        foreach ($orders as $order) {
            if ($order->status === OrderStatus::READY_TO_SHIP) {
                $shipments = OrderShipping::currentShipments($order);
                if ($shipments->isNotEmpty() && $shipments->every(fn ($shipment): bool =>
                    $shipment->generated_at !== null || $shipment->posted_at !== null || $shipment->delivered_at !== null)) {
                    $this->notify->execute($order, OrderStatus::READY_TO_SHIP);
                    $count++;
                }

                continue;
            }

            if (OrderShipping::mode($order) === 'automatic'
                && $order->shipping_document_type === ShipmentDocumentType::INVOICE
                && blank($order->invoice_key)) {
                $this->notify->execute($order, OrderStatus::PAID);
                $count++;
            }
        }

        return $count;
    }
}

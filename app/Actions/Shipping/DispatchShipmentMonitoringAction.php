<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Domain\Shipping\Models\Shipment;
use App\Jobs\Shipping\CheckShipmentStatusJob;

/** Select due shipments; the queue worker performs the HTTP reads. */
final class DispatchShipmentMonitoringAction
{
    public function execute(int $limit = 100): int
    {
        $due = Shipment::query()
            ->where('provider', 'melhor_envio')
            ->whereIn('status', [
                ShipmentStatus::PENDING->value,
                ShipmentStatus::CART_CREATED->value,
                ShipmentStatus::PURCHASED->value,
                ShipmentStatus::LABEL_GENERATED->value,
                ShipmentStatus::POSTED->value,
                ShipmentStatus::PAUSED->value,
                ShipmentStatus::SUSPENDED->value,
                ShipmentStatus::UNDELIVERED->value,
            ])
            ->where(function ($query): void {
                $query->whereNotNull('external_id')
                    ->orWhere(function ($inner): void {
                        $inner->whereNotNull('operation_phase')->whereNotNull('operation_key');
                    });
            })
            ->whereHas('order', function ($query): void {
                $query->whereIn('status', [
                    OrderStatus::PAID->value,
                    OrderStatus::PROCESSING->value,
                    OrderStatus::READY_TO_SHIP->value,
                    OrderStatus::SHIPPED->value,
                ]);
            })
            ->where(function ($query): void {
                $query->whereNull('monitor_checked_at')
                    ->orWhere('monitor_checked_at', '<=', now()->subMinutes(2));
            })
            ->orderBy('monitor_checked_at')
            ->orderBy('id')
            ->limit(max(1, min(300, $limit)))
            ->pluck('id');

        foreach ($due as $id) {
            CheckShipmentStatusJob::dispatch((int) $id);
        }

        return $due->count();
    }
}

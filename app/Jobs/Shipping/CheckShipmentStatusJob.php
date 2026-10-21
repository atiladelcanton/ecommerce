<?php

declare(strict_types=1);

namespace App\Jobs\Shipping;

use App\Actions\Shipping\ReconcileShipmentAction;
use App\Domain\Shipping\Contracts\ShipmentOperationLookupProvider;
use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Domain\Shipping\Models\Shipment;
use App\Infrastructure\Shipping\ShippingProviderRegistry;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Only consults remote state; NEVER creates a cart, buys freight or regenerates a label. */
final class CheckShipmentStatusJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $timeout = 90;

    public int $tries = 2;

    public int $uniqueFor = 240;

    public function __construct(public readonly int $shipmentId) {}

    public function uniqueId(): string
    {
        return 'shipping-status:'.$this->shipmentId;
    }

    public function handle(ShippingProviderRegistry $registry, ReconcileShipmentAction $reconcile): void
    {
        $shipment = Shipment::query()->find($this->shipmentId);
        if (! $shipment || $shipment->provider !== 'melhor_envio'
            || in_array($shipment->status, [ShipmentStatus::CANCELLED, ShipmentStatus::DELIVERED], true)) {
            return;
        }

        $shipment->update(['monitor_checked_at' => now()]);

        try {
            $externalId = $shipment->external_id;
            if (! $externalId) {
                // Uncertain initial request: read-only lookup by operation tag;
                // a missing match must never trigger a new purchase.
                if (! $shipment->operation_key || ! $shipment->operation_phase) {
                    return;
                }
                $provider = $registry->get($shipment->provider);
                if (! $provider instanceof ShipmentOperationLookupProvider) {
                    return;
                }
                $match = $provider->findByOperation((string) $shipment->operation_key, (string) $shipment->service_external_id);
                $externalId = $match?->externalId;
                if (! $externalId) {
                    return;
                }
            }

            $before = $shipment->status;
            $after = $reconcile->execute($shipment, (string) $externalId);
            if ($after->status !== $before) {
                Log::info('Automatic shipment status updated.', [
                    'shipment_id' => $shipment->id,
                    'order_id' => $shipment->order_id,
                    'from_status' => $before->value,
                    'to_status' => $after->status->value,
                ]);
            }
        } catch (Throwable $exception) {
            // A failed inspection never triggers new financial operations.
            // Keep candidate active for the next scheduled check.
            $root = str_replace('\\', '/', base_path()).'/';
            $source = str_replace('\\', '/', $exception->getFile());
            Log::warning('Automatic shipment status check failed.', [
                'shipment_id' => $shipment->id,
                'order_id' => $shipment->order_id,
                'exception_type' => $exception::class,
                'source' => str_starts_with($source, $root) ? substr($source, strlen($root)) : '(external)',
                'line' => $exception->getLine(),
            ]);
        }
    }
}

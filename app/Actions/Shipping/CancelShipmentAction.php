<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Domain\Shipping\Contracts\ShipmentLabelProvider;
use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Domain\Shipping\Exceptions\ShippingProviderFailed;
use App\Domain\Shipping\Models\Shipment;
use App\Infrastructure\Shipping\ShippingProviderRegistry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

final class CancelShipmentAction
{
    public function __construct(private readonly ShippingProviderRegistry $providers, private readonly AssertShipmentConnectionAction $connection) {}

    public function execute(Shipment $shipment, string $reason, ?int $userId = null): Shipment
    {
        if (trim($reason) === '') {
            throw ValidationException::withMessages(['reason' => 'Informe o motivo do cancelamento.']);
        }

        return Cache::lock('shipping-label-order:'.$shipment->order_id, 600)->block(3, function () use ($shipment, $reason, $userId): Shipment {
            $shipment->refresh();
            if ($shipment->status === ShipmentStatus::CANCELLED) {
                return $shipment;
            }
            $this->connection->execute($shipment);
            $provider = $this->providers->get($shipment->provider);
            if (! $provider instanceof ShipmentLabelProvider || ! $shipment->external_id) {
                throw ValidationException::withMessages(['shipping' => 'Este envio não oferece cancelamento automático.']);
            }
            try {
                if (! $provider->canCancel($shipment->external_id)) {
                    throw ValidationException::withMessages(['shipping' => 'O provedor informou que esta etiqueta não pode mais ser cancelada.']);
                }
                $provider->cancel($shipment->external_id, trim($reason));
            } catch (ShippingProviderFailed $exception) {
                throw ValidationException::withMessages(['shipping' => $exception->getMessage()]);
            }
            // Cancellation is logistics only. Commercial order status is preserved.
            $shipment->update(['status' => ShipmentStatus::CANCELLED, 'cancelled_at' => now(), 'operation_phase' => null,
                'metadata' => array_merge($shipment->metadata ?? [], ['cancel_reason' => trim($reason), 'cancelled_by' => $userId])]);

            return $shipment->refresh();
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Domain\Shipping\Contracts\ShipmentTrackingProvider;
use App\Domain\Shipping\Exceptions\ShippingProviderFailed;
use App\Domain\Shipping\Models\Shipment;
use App\Infrastructure\Shipping\ShippingProviderRegistry;
use Illuminate\Validation\ValidationException;

final class RefreshShipmentTrackingAction
{
    public function __construct(private readonly ShippingProviderRegistry $providers, private readonly AssertShipmentConnectionAction $connection, private readonly ProcessShippingWebhookAction $events) {}

    public function execute(Shipment $shipment): Shipment
    {
        $this->connection->execute($shipment);
        $provider = $this->providers->get($shipment->provider);
        if (! $provider instanceof ShipmentTrackingProvider || ! $shipment->external_id) {
            throw ValidationException::withMessages(['shipping' => 'Este envio não oferece rastreamento automático.']);
        }
        try {
            $result = $provider->track($shipment->external_id);
        } catch (ShippingProviderFailed $exception) {
            throw ValidationException::withMessages(['shipping' => $exception->getMessage()]);
        }
        $payload = ['event' => 'order.'.$result->status, 'data' => ['id' => $shipment->external_id, 'tracking' => $result->trackingCode, 'tracking_url' => $result->trackingUrl]];
        $key = 'sync:'.hash('sha256', json_encode([$shipment->provider, $shipment->external_id, $result->status, $result->trackingCode, $result->trackingUrl], JSON_THROW_ON_ERROR));
        $this->events->execute($shipment->provider, $payload, $key);

        return $shipment->refresh();
    }
}

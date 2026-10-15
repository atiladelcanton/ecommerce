<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Domain\Shipping\Models\Shipment;
use App\Domain\Shipping\Models\ShippingIntegration;
use Illuminate\Validation\ValidationException;

final class AssertShipmentConnectionAction
{
    public function execute(Shipment $shipment): void
    {
        $integration = $shipment->shipping_integration_id ? ShippingIntegration::query()->find($shipment->shipping_integration_id) : ShippingIntegration::query()->where('provider', $shipment->provider)->first();
        if (! $integration || $integration->provider !== $shipment->provider || ! $integration->access_token || ($shipment->environment && $integration->environment !== $shipment->environment)) {
            throw ValidationException::withMessages(['shipping' => 'Conecte a integração no mesmo ambiente deste envio.']);
        }
    }
}

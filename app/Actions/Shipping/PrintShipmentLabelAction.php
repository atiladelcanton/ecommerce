<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Domain\Shipping\Contracts\ShipmentLabelProvider;
use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Domain\Shipping\Exceptions\ShippingProviderFailed;
use App\Domain\Shipping\Models\Shipment;
use App\Infrastructure\Shipping\ShippingProviderRegistry;
use Illuminate\Validation\ValidationException;

final class PrintShipmentLabelAction
{
    public function __construct(private readonly ShippingProviderRegistry $providers) {}

    public function execute(Shipment $shipment, string $format = 'pdf'): string
    {
        app(AssertShipmentConnectionAction::class)->execute($shipment);
        $provider = $this->providers->get($shipment->provider);

        if (! $provider instanceof ShipmentLabelProvider
            || ! $shipment->external_id
            || ! in_array($shipment->status, [
                ShipmentStatus::LABEL_GENERATED,
                ShipmentStatus::POSTED,
                ShipmentStatus::DELIVERED,
                ShipmentStatus::PAUSED,
                ShipmentStatus::SUSPENDED,
                ShipmentStatus::UNDELIVERED,
            ], true)) {
            throw ValidationException::withMessages(['shipping' => 'Esta etiqueta ainda não está disponível para impressão.']);
        }

        try {
            $url = $provider->printLabel($shipment->external_id, $format);
        } catch (ShippingProviderFailed $exception) {
            throw ValidationException::withMessages(['shipping' => $exception->getMessage()]);
        }

        $shipment->forceFill(['label_url' => $url, 'label_format' => $format])->save();

        return $url;
    }
}

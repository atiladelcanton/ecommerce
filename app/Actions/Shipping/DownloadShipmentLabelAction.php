<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Domain\Shipping\Contracts\ShipmentLabelProvider;
use App\Domain\Shipping\Data\ShipmentLabelFile;
use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Domain\Shipping\Exceptions\ShippingProviderFailed;
use App\Domain\Shipping\Models\Shipment;
use App\Infrastructure\Shipping\ShippingProviderRegistry;
use Illuminate\Validation\ValidationException;

final class DownloadShipmentLabelAction
{
    public function __construct(private readonly ShippingProviderRegistry $providers, private readonly AssertShipmentConnectionAction $connection) {}

    public function execute(Shipment $shipment, string $format): ShipmentLabelFile
    {
        $this->connection->execute($shipment);
        $provider = $this->providers->get($shipment->provider);
        if (! $provider instanceof ShipmentLabelProvider || ! $shipment->external_id || ! in_array($shipment->status, [ShipmentStatus::LABEL_GENERATED, ShipmentStatus::POSTED, ShipmentStatus::DELIVERED, ShipmentStatus::PAUSED, ShipmentStatus::SUSPENDED, ShipmentStatus::UNDELIVERED], true)) {
            throw ValidationException::withMessages(['shipping' => 'Esta etiqueta ainda não está disponível para impressão.']);
        }
        try {
            $file = $provider->downloadLabel($shipment->external_id, $format);
        } catch (ShippingProviderFailed $exception) {
            throw ValidationException::withMessages(['shipping' => $exception->getMessage()]);
        }
        $shipment->update(['label_format' => $format]);

        return $file;
    }
}

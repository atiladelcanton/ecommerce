<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Domain\Shipping\Contracts\ShipmentProvider;
use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Domain\Shipping\Models\Shipment;
use App\Infrastructure\Shipping\ShippingProviderRegistry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReconcileShipmentAction
{
    public function __construct(private readonly ShippingProviderRegistry $providers, private readonly AssertShipmentConnectionAction $connection, private readonly ProcessShippingWebhookAction $events) {}

    public function execute(Shipment $shipment, string $externalId): Shipment
    {
        return Cache::lock('shipping-label-order:'.$shipment->order_id, 600)->block(3, function () use ($shipment, $externalId): Shipment {
            $this->connection->execute($shipment);
            $provider = $this->providers->get($shipment->provider);
            if (! $provider instanceof ShipmentProvider || ! preg_match('/^[a-zA-Z0-9-]{1,100}$/D', $externalId)) {
                throw ValidationException::withMessages(['external_id' => 'Informe um identificador externo válido.']);
            }
            $result = $provider->inspect($externalId);
            if (! collect($result->metadata['tags'] ?? [])->contains(fn ($tag) => ($tag['tag'] ?? '') === 'saba-operation:'.$shipment->operation_key)
                || (string) ($result->metadata['service'] ?? '') !== $shipment->service_external_id) {
                throw ValidationException::withMessages(['external_id' => 'O envio externo não corresponde a esta operação e serviço.']);
            }
            DB::transaction(function () use ($shipment, $externalId, $result): void {
                $locked = Shipment::query()->lockForUpdate()->findOrFail($shipment->id);
                if ($locked->external_id && $locked->external_id !== $externalId) {
                    throw ValidationException::withMessages(['external_id' => 'Este envio já possui outro identificador.']);
                }
                $locked->external_id = $externalId;
                if ($result->status === 'pending' && $locked->status === ShipmentStatus::PENDING) {
                    $locked->status = ShipmentStatus::CART_CREATED;
                    $locked->operation_phase = null;
                }
                $locked->save();
            });
            app(RefreshShipmentTrackingAction::class)->execute($shipment->refresh());

            return $shipment->refresh();
        });
    }
}

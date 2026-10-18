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

/**
 * Low-level verification of a known provider shipment.
 *
 * This class deliberately keeps the technical name "reconcile" internally,
 * but the admin UI exposes it as "Verificar envio".
 */
final class ReconcileShipmentAction
{
    public function __construct(
        private readonly ShippingProviderRegistry $providers,
        private readonly AssertShipmentConnectionAction $connection,
        private readonly ProcessShippingWebhookAction $events,
    ) {}

    public function execute(Shipment $shipment, string $externalId): Shipment
    {
        return Cache::lock('shipping-label-order:'.$shipment->order_id, 600)->block(3, function () use ($shipment, $externalId): Shipment {
            $this->connection->execute($shipment);
            $provider = $this->providers->get($shipment->provider);

            if (! $provider instanceof ShipmentProvider || ! preg_match('/^[a-zA-Z0-9-]{1,100}$/D', $externalId)) {
                throw ValidationException::withMessages(['external_id' => 'Não foi possível validar o identificador deste envio.']);
            }

            $result = $provider->inspect($externalId);
            if (! $this->matchesOperation($shipment, $result->metadata)) {
                throw ValidationException::withMessages([
                    'shipping' => 'O envio encontrado no provedor não corresponde com segurança a este pedido. Nenhuma alteração foi feita.',
                ]);
            }

            DB::transaction(function () use ($shipment, $externalId, $result): void {
                $locked = Shipment::query()->lockForUpdate()->findOrFail($shipment->id);

                if ($locked->external_id && $locked->external_id !== $externalId) {
                    throw ValidationException::withMessages([
                        'shipping' => 'Este pedido já está vinculado a outro envio no provedor.',
                    ]);
                }

                $locked->external_id = $externalId;

                // pending/created proves that the cart exists. This safely
                // resolves an uncertain cart creation, but intentionally does
                // NOT clear an uncertain purchase: payment can still be in
                // flight at the provider.
                if (in_array($result->status, ['pending', 'created'], true)
                    && $locked->status === ShipmentStatus::PENDING) {
                    $locked->status = ShipmentStatus::CART_CREATED;
                    if ($locked->operation_phase === 'cart_in_progress') {
                        $locked->operation_phase = null;
                    }
                }

                if ($result->trackingCode) {
                    $locked->tracking_code = $result->trackingCode;
                }
                if ($result->trackingUrl) {
                    $locked->tracking_url = $result->trackingUrl;
                }

                $locked->save();
            });

            // Reuse the webhook projector for definitive provider states. It
            // centralizes status mapping and clears an uncertain purchase only
            // after Melhor Envio confirms payment (released) or a later state.
            if (! in_array($result->status, ['pending', 'created', 'unknown'], true)) {
                $payload = [
                    'event' => 'order.'.$this->eventStatus($result->status),
                    'data' => [
                        'id' => $externalId,
                        'tracking' => $result->trackingCode,
                        'tracking_url' => $result->trackingUrl,
                    ],
                ];
                $key = 'verify:'.hash('sha256', json_encode([
                    $shipment->provider,
                    $externalId,
                    $result->status,
                    $result->trackingCode,
                    $result->trackingUrl,
                ], JSON_THROW_ON_ERROR));
                $this->events->execute($shipment->provider, $payload, $key);
            }

            return $shipment->refresh();
        });
    }

    /** @param array<string, mixed> $metadata */
    private function matchesOperation(Shipment $shipment, array $metadata): bool
    {
        $expectedTag = 'saba-operation:'.$shipment->operation_key;
        $tags = $metadata['tags'] ?? [];
        $tagMatches = collect(is_array($tags) ? $tags : [])->contains(function (mixed $tag) use ($expectedTag): bool {
            if (is_string($tag)) {
                return $tag === $expectedTag;
            }

            return is_array($tag) && (string) ($tag['tag'] ?? '') === $expectedTag;
        });

        return $tagMatches && (string) ($metadata['service'] ?? '') === (string) $shipment->service_external_id;
    }

    private function eventStatus(string $status): string
    {
        return match ($status) {
            'canceled' => 'cancelled',
            default => $status,
        };
    }
}

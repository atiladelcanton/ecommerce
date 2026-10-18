<?php

declare(strict_types=1);

namespace App\Infrastructure\Shipping;

use App\Domain\Shipping\Contracts\ShippingCapabilitiesProvider;
use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Domain\Shipping\Data\ShippingCapabilities;
use App\Domain\Shipping\Data\ShippingOption;
use App\Domain\Shipping\Data\ShippingQuoteRequest;
use App\Domain\Shipping\Models\ShippingIntegration;

final class LocalPickupProvider implements ShippingCapabilitiesProvider, ShippingProvider
{
    public function capabilities(): ShippingCapabilities
    {
        return new ShippingCapabilities(pickup: true, requiresPostalCodes: false);
    }

    public function key(): string
    {
        return 'local_pickup';
    }

    public function quote(ShippingQuoteRequest $request): array
    {
        $integration = ShippingIntegration::forProvider($this->key());
        if (! $integration->is_enabled) {
            return [];
        }
        $config = $integration->configuration ?? [];
        $label = trim((string) ($config['label'] ?? 'Retirada na loja')) ?: 'Retirada na loja';
        $description = isset($config['ready_days']) ? 'Disponível para retirada em até '.(int) $config['ready_days'].' dia(s) úteis.' : trim((string) ($config['description'] ?? 'Retire seu pedido diretamente na loja.'));

        return [new ShippingOption(
            id: $this->key().':pickup',
            name: $label,
            price: 0,
            description: $description,
            provider: $this->key(),
            serviceExternalId: 'pickup',
            integrationId: $integration->id,
            fulfillmentMode: 'pickup',
            estimatedDays: isset($config['ready_days']) ? (int) $config['ready_days'] : null,
            metadata: ['address' => $config['address'] ?? '', 'instructions' => $config['instructions'] ?? ''],
            carrierName: 'Retirada',
        )];
    }
}

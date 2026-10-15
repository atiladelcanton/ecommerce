<?php

declare(strict_types=1);

namespace App\Infrastructure\Shipping;

use App\Domain\Shipping\Contracts\ShippingCapabilitiesProvider;
use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Domain\Shipping\Data\ShippingCapabilities;
use App\Domain\Shipping\Data\ShippingOption;
use App\Domain\Shipping\Data\ShippingQuoteRequest;

final class FakeShippingProvider implements ShippingCapabilitiesProvider, ShippingProvider
{
    public function capabilities(): ShippingCapabilities
    {
        return new ShippingCapabilities(requiresPostalCodes: false);
    }

    public function key(): string
    {
        return 'fake';
    }

    public function quote(ShippingQuoteRequest $request): array
    {
        return [
            new ShippingOption(
                id: 'standard',
                name: 'Entrega padrão',
                price: 4990,
                description: 'Prazo demonstrativo: 5 a 8 dias úteis',
                provider: $this->key(),
                serviceExternalId: 'standard',
                carrierName: 'Entrega de demonstração',
                estimatedDays: 8,
            ),
            new ShippingOption(
                id: 'pickup',
                name: 'Retirada / combinar com a loja',
                price: 0,
                description: 'Combine a retirada diretamente com a loja.',
                provider: $this->key(),
                serviceExternalId: 'pickup',
                fulfillmentMode: 'pickup',
                carrierName: 'Retirada',
            ),
        ];
    }
}

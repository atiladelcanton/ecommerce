<?php

declare(strict_types=1);

namespace App\Infrastructure\Shipping;

use App\Domain\Shipping\Contracts\ShippingCapabilitiesProvider;
use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Domain\Shipping\Data\ShippingCapabilities;
use App\Domain\Shipping\Data\ShippingQuoteRequest;

/** Order-scoped negotiated freight is published by an admin, never as a public quote. */
final class ManualShippingProvider implements ShippingCapabilitiesProvider, ShippingProvider
{
    public function key(): string
    {
        return 'manual';
    }

    public function capabilities(): ShippingCapabilities
    {
        return new ShippingCapabilities;
    }

    public function quote(ShippingQuoteRequest $request): array
    {
        return [];
    }
}

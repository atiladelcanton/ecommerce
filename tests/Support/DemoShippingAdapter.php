<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Domain\Shipping\Contracts\ShippingCapabilitiesProvider;
use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Domain\Shipping\Data\ShippingCapabilities;
use App\Domain\Shipping\Data\ShippingQuoteRequest;

final class DemoShippingAdapter implements ShippingCapabilitiesProvider, ShippingProvider
{
    public function key(): string
    {
        return 'fake';
    }

    public function capabilities(): ShippingCapabilities
    {
        return new ShippingCapabilities(requiresPostalCodes: false);
    }

    public function quote(ShippingQuoteRequest $request): array
    {
        return app(ShippingProvider::class)->quote($request);
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Contracts;

use App\Domain\Shipping\Data\ShippingCapabilities;

interface ShippingCapabilitiesProvider
{
    public function capabilities(): ShippingCapabilities;
}

<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Data;

final readonly class ShippingCapabilities
{
    public function __construct(
        public bool $pickup = false,
        public bool $labels = false,
        public bool $automaticTracking = false,
        public bool $requiresPostalCodes = true,
    ) {}
}

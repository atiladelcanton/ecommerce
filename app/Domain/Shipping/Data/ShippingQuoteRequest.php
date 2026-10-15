<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Data;

final readonly class ShippingQuoteRequest
{
    public string $postalCode;

    /** @param list<ShippingItem> $items */
    public function __construct(
        public string $destinationPostalCode,
        public array $items,
        public string $originPostalCode = '',
    ) {
        // Backwards-compatible alias kept for existing Actions/tests.
        $this->postalCode = $destinationPostalCode;
    }
}

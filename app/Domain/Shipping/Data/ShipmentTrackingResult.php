<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Data;

final readonly class ShipmentTrackingResult
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public string $status,
        public ?string $trackingCode = null,
        public ?string $trackingUrl = null,
        public array $metadata = [],
    ) {}
}

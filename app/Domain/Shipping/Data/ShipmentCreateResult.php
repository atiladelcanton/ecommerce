<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Data;

final readonly class ShipmentCreateResult
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public string $externalId,
        public ?int $providerPriceCents = null,
        public ?string $trackingCode = null,
        public ?string $trackingUrl = null,
        public array $metadata = [],
    ) {}
}

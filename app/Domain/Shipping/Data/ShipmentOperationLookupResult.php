<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Data;

final readonly class ShipmentOperationLookupResult
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public string $externalId,
        public string $status,
        public array $metadata = [],
    ) {}
}

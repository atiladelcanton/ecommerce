<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Data;

use InvalidArgumentException;

final readonly class ShippingOption
{
    public int $priceCents;

    public string $serviceName;

    /** @param array<string, mixed> $metadata */
    public function __construct(
        public string $id,
        public string $name,
        public int $price,
        public string $description,
        public string $provider = 'fake',
        public ?string $serviceExternalId = null,
        public ?string $carrierExternalId = null,
        public ?string $carrierName = null,
        public ?int $estimatedDays = null,
        public array $metadata = [],
        public ?int $integrationId = null,
        public ?array $estimatedRange = null,
        public array $packages = [],
        public string $fulfillmentMode = 'manual',
        public ?string $environment = null,
    ) {
        $this->priceCents = $price;
        $this->serviceName = $name;
        if ($price < 0) {
            throw new InvalidArgumentException('O valor do frete não pode ser negativo.');
        }
    }

    public static function fromSnapshot(array $row): self
    {
        return new self(
            id: $row['id'], name: $row['name'], price: $row['price'], description: $row['description'],
            provider: $row['provider'], serviceExternalId: $row['service_external_id'] ?? null,
            carrierExternalId: $row['carrier_external_id'] ?? null, carrierName: $row['carrier_name'] ?? null,
            estimatedDays: $row['estimated_days'] ?? null, metadata: $row['metadata'] ?? [],
            integrationId: $row['integration_id'] ?? null, estimatedRange: $row['estimated_range'] ?? null,
            packages: $row['packages'] ?? $row['metadata']['packages'] ?? [],
            fulfillmentMode: $row['fulfillment_mode'] ?? 'manual', environment: $row['environment'] ?? null,
        );
    }

    /** @return array<string, mixed> */
    public function snapshot(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'description' => $this->description,
            'provider' => $this->provider,
            'service_external_id' => $this->serviceExternalId,
            'carrier_external_id' => $this->carrierExternalId,
            'carrier_name' => $this->carrierName,
            'estimated_days' => $this->estimatedDays,
            'metadata' => $this->metadata,
            'integration_id' => $this->integrationId, 'estimated_range' => $this->estimatedRange,
            'packages' => $this->packages, 'fulfillment_mode' => $this->fulfillmentMode, 'environment' => $this->environment,
            'service_name' => $this->name, 'price_cents' => $this->price,
        ];
    }
}

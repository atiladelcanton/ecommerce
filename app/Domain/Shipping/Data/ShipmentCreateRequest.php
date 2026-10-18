<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Data;

use App\Domain\Shipping\Enums\ShipmentDocumentType;

final readonly class ShipmentCreateRequest
{
    /**
     * @param  array<string, mixed>  $sender
     * @param  array<string, mixed>  $recipient
     * @param  list<array<string, mixed>>  $products
     * @param  list<array<string, mixed>>  $volumes
     * @param  array<string, mixed>  $quoteSnapshot
     */
    public function __construct(
        public string $orderNumber,
        public string $serviceExternalId,
        public array $sender,
        public array $recipient,
        public array $products,
        public array $volumes,
        public int $insuranceValueCents,
        public ShipmentDocumentType $documentType,
        public ?string $invoiceKey,
        public ?string $dceKey,
        public array $quoteSnapshot,
        public ?string $orderUrl = null,
        public ?string $agencyExternalId = null,
        public ?string $operationKey = null,
    ) {}
}

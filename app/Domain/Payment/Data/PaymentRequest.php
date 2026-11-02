<?php

declare(strict_types=1);

namespace App\Domain\Payment\Data;

/** Immutable hosted checkout request built exclusively from saved order snapshots. */
final readonly class PaymentRequest
{
    public function __construct(
        public string $orderNumber,
        public int $amount,
        public string $idempotencyKey,
        public ?string $buyerEmail = null,
        public array $items = [],
        public ?string $returnUrl = null,
        public int $expirationHours = 72,
        public bool $allowOffline = false,
        public ?int $expirationMinutes = null,
        public ?string $buyerName = null,
        public ?string $buyerDocument = null,
        public ?string $buyerPhone = null,
        public ?array $buyerAddress = null,
    ) {}
}

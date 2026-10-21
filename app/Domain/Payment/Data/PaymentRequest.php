<?php

declare(strict_types=1);

namespace App\Domain\Payment\Data;

final readonly class PaymentRequest
{
    /**
     * @param list<array{title:string,quantity:int,unit_price:string}> $items
     * @param array{street:string,number:string,postal_code:string,neighborhood:string,complement?:?string}|null $buyerAddress
     */
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

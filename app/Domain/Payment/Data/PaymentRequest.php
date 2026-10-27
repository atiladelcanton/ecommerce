<?php

declare(strict_types=1);

namespace App\Domain\Payment\Data;

final readonly class PaymentRequest
{
    public function __construct(
        public string $orderNumber,
        public int $amount,
        public string $idempotencyKey,
    ) {}
}

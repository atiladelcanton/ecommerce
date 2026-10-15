<?php

declare(strict_types=1);

namespace App\Domain\Payment\Data;

use App\Domain\Payment\Enums\PaymentStatus;

final readonly class PaymentResult
{
    public function __construct(
        public string $gateway,
        public string $externalId,
        public PaymentStatus $status,
        public bool $isDemo,
    ) {}
}

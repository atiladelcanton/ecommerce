<?php

declare(strict_types=1);

namespace App\Domain\Payment\Contracts;

interface InspectablePaymentGateway extends PaymentGateway
{
    /** @return array<string,mixed> Validated external resource returned by the provider */
    public function inspect(string $externalId): array;
}

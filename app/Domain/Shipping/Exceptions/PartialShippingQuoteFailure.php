<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Exceptions;

use App\Domain\Shipping\Data\ShippingOption;
use RuntimeException;

final class PartialShippingQuoteFailure extends RuntimeException
{
    /** @param list<ShippingOption> $options */
    public function __construct(public readonly array $options)
    {
        parent::__construct('Alguns serviços de entrega estão temporariamente indisponíveis.');
    }
}

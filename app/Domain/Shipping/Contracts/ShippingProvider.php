<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Contracts;

use App\Domain\Shipping\Data\ShippingOption;
use App\Domain\Shipping\Data\ShippingQuoteRequest;

interface ShippingProvider
{
    public function key(): string;

    /** @return list<ShippingOption> */
    public function quote(ShippingQuoteRequest $request): array;
}

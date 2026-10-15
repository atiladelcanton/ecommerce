<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Contracts;

use App\Domain\Shipping\Data\ShipmentTrackingResult;

interface ShipmentTrackingProvider
{
    public function track(string $externalId): ShipmentTrackingResult;
}

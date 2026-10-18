<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Contracts;

use App\Domain\Shipping\Data\ShipmentOperationLookupResult;

interface ShipmentOperationLookupProvider
{
    public function findByOperation(string $operationKey, string $serviceExternalId): ?ShipmentOperationLookupResult;
}

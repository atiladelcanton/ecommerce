<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Contracts;

use App\Domain\Shipping\Data\ShipmentCreateRequest;
use App\Domain\Shipping\Data\ShipmentCreateResult;
use App\Domain\Shipping\Data\ShipmentTrackingResult;

interface ShipmentProvider
{
    public function createCart(ShipmentCreateRequest $request): ShipmentCreateResult;

    public function inspect(string $externalId): ShipmentTrackingResult;

    public function purchase(string $externalId): void;
}

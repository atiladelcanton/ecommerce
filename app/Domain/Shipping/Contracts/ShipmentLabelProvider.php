<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Contracts;

use App\Domain\Shipping\Data\ShipmentLabelFile;

interface ShipmentLabelProvider
{
    public function generateLabel(string $externalId): void;

    public function downloadLabel(string $externalId, string $format): ShipmentLabelFile;

    public function printLabel(string $externalId, string $format = 'pdf'): string;

    public function canCancel(string $externalId): bool;

    public function cancel(string $externalId, string $reason): void;
}

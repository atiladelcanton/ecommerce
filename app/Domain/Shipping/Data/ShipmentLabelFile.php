<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Data;

final readonly class ShipmentLabelFile
{
    public function __construct(public string $contents, public string $contentType, public string $filename) {}
}

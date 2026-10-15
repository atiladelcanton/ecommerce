<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Data;

final readonly class ShippingItem
{
    public function __construct(
        public int $productId,
        public int $quantity,
        public ?string $weight,
        public ?string $height,
        public ?string $width,
        public ?string $length,
        public int $unitPriceCents = 0,
        public ?string $name = null,
        public ?string $sku = null,
        public ?string $lineId = null,
    ) {}
}

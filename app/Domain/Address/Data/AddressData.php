<?php

declare(strict_types=1);

namespace App\Domain\Address\Data;

final readonly class AddressData
{
    public function __construct(
        public string $postalCode,
        public string $street,
        public string $neighborhood,
        public string $city,
        public string $state,
        public ?string $complement = null,
        public ?string $ibgeCode = null,
    ) {}
}

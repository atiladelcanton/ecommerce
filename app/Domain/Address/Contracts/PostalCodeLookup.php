<?php

declare(strict_types=1);

namespace App\Domain\Address\Contracts;

use App\Domain\Address\Data\AddressData;
use App\Domain\Address\Exceptions\PostalCodeLookupFailed;

interface PostalCodeLookup
{
    /** @throws PostalCodeLookupFailed */
    public function find(string $postalCode): ?AddressData;
}

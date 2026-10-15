<?php

declare(strict_types=1);

namespace App\Actions\Address;

use App\Domain\Address\Contracts\PostalCodeLookup;
use App\Domain\Address\Data\AddressData;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

final class LookupPostalCodeAction
{
    public function __construct(private readonly PostalCodeLookup $lookup) {}

    public function execute(string $postalCode): ?AddressData
    {
        $postalCode = preg_replace('/\D/', '', $postalCode);
        if (strlen($postalCode) !== 8) {
            throw ValidationException::withMessages(['postal_code' => 'Informe um CEP com 8 dígitos.']);
        }

        // Wrap null so valid-but-unknown CEPs are cached too; temporary failures are never cached.
        $result = Cache::remember(
            'address:'.config('address.provider').':'.$postalCode,
            (int) config('address.cache_ttl', 86400),
            fn (): array => ['address' => $this->lookup->find($postalCode)],
        );

        return $result['address'];
    }
}

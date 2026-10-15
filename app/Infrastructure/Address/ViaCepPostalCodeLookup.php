<?php

declare(strict_types=1);

namespace App\Infrastructure\Address;

use App\Domain\Address\Contracts\PostalCodeLookup;
use App\Domain\Address\Data\AddressData;
use App\Domain\Address\Exceptions\PostalCodeLookupFailed;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final class ViaCepPostalCodeLookup implements PostalCodeLookup
{
    public function find(string $postalCode): ?AddressData
    {
        $postalCode = preg_replace('/\D/', '', $postalCode);
        if (strlen($postalCode) !== 8) {
            return null;
        }

        try {
            $response = Http::acceptJson()
                ->connectTimeout((int) config('address.providers.viacep.connect_timeout', 3))
                ->timeout((int) config('address.providers.viacep.timeout', 4))
                ->get(rtrim(config('address.providers.viacep.url'), '/').'/'.$postalCode.'/json/');
        } catch (ConnectionException $exception) {
            throw new PostalCodeLookupFailed('Postal code service unavailable.', previous: $exception);
        }

        if (! $response->successful()) {
            throw new PostalCodeLookupFailed('Postal code service returned an unsuccessful response.');
        }
        $data = $response->json();
        if (! is_array($data)) {
            throw new PostalCodeLookupFailed('Postal code service returned an invalid response.');
        }
        if (in_array($data['erro'] ?? false, [true, 'true'], true)) {
            return null;
        }
        foreach (['cep', 'logradouro', 'bairro', 'localidade', 'uf'] as $field) {
            if (! is_string($data[$field] ?? null) || mb_strlen($data[$field]) > 255) {
                throw new PostalCodeLookupFailed('Postal code service returned an invalid address.');
            }
        }
        if (preg_replace('/\D/', '', $data['cep']) !== $postalCode || trim($data['localidade']) === '' || ! preg_match('/\A[A-Z]{2}\z/', $data['uf'])) {
            throw new PostalCodeLookupFailed('Postal code service returned an inconsistent address.');
        }

        return new AddressData(
            postalCode: $postalCode,
            street: trim($data['logradouro']),
            neighborhood: trim($data['bairro']),
            city: trim($data['localidade']),
            state: $data['uf'],
            complement: $this->optionalString($data['complemento'] ?? null),
            ibgeCode: $this->optionalString($data['ibge'] ?? null),
        );
    }

    private function optionalString(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' && mb_strlen($value) <= 255 ? trim($value) : null;
    }
}

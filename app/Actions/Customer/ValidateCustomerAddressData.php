<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Actions\Order\ValidateCheckoutData;
use App\Support\BrazilianData;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class ValidateCustomerAddressData
{
    public function execute(array $input): array
    {
        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $input[$key] = trim($value);
            }
        }
        $input['postal_code'] = BrazilianData::digits($input['postal_code'] ?? '');
        $input['state'] = strtoupper((string) ($input['state'] ?? ''));
        $input['complement'] = ($input['complement'] ?? '') === '' ? null : $input['complement'];
        $input['label'] = ($input['label'] ?? '') === '' ? null : $input['label'];
        $input['is_default'] = (bool) ($input['is_default'] ?? false);

        return Validator::make($input, [
            'label' => ['nullable', 'string', 'max:60'],
            'postal_code' => ['required', 'regex:/\A\d{8}\z/'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:30'],
            'complement' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', Rule::in(ValidateCheckoutData::STATES)],
            'is_default' => ['boolean'],
        ], [
            'postal_code.regex' => 'Informe um CEP válido.',
            'required' => 'Informe :attribute.',
            'state.in' => 'Selecione um estado válido.',
        ], [
            'postal_code' => 'o CEP', 'street' => 'a rua', 'number' => 'o número',
            'neighborhood' => 'o bairro', 'city' => 'a cidade', 'state' => 'o estado',
        ])->validate();
    }
}

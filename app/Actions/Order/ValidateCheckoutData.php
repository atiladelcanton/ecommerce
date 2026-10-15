<?php

declare(strict_types=1);

namespace App\Actions\Order;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class ValidateCheckoutData
{
    public const STATES = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];

    public function execute(array $data): array
    {
        foreach ($data as $field => $value) {
            if (is_string($value)) {
                $data[$field] = trim($value);
            }
        }
        foreach (['phone', 'document', 'postal_code'] as $field) {
            if (is_string($data[$field] ?? null)) {
                $data[$field] = preg_replace('~[.\/()\s+\-]~u', '', $data[$field]);
            }
        }
        if (is_string($data['state'] ?? null)) {
            $data['state'] = strtoupper($data['state']);
        }
        if (($data['complement'] ?? null) === '') {
            $data['complement'] = null;
        }

        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'regex:/\A[0-9]{10,13}\z/'],
            'document' => ['required', 'string', 'regex:/\A(?:[0-9]{11}|[0-9]{14})\z/'],
            'postal_code' => ['required', 'string', 'regex:/\A[0-9]{8}\z/'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:30'],
            'complement' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', Rule::in(self::STATES)],
            'shipping_option' => ['required', 'string', 'max:255'],
        ], [
            'required' => 'Informe :attribute.',
            'string' => 'Informe um valor válido para :attribute.',
            'email.email' => 'Informe um e-mail válido.',
            'phone.regex' => 'Informe um telefone válido com DDD.',
            'document.regex' => 'Informe um CPF com 11 dígitos ou CNPJ com 14 dígitos.',
            'postal_code.regex' => 'Informe um CEP com 8 dígitos.',
            'state.in' => 'Selecione um estado válido.',
            'max' => 'O campo :attribute deve ter no máximo :max caracteres.',
        ], [
            'name' => 'o nome', 'email' => 'o e-mail', 'phone' => 'o telefone', 'document' => 'o CPF/CNPJ',
            'postal_code' => 'o CEP', 'street' => 'a rua', 'number' => 'o número', 'complement' => 'o complemento',
            'neighborhood' => 'o bairro', 'city' => 'a cidade', 'state' => 'o estado', 'shipping_option' => 'a forma de entrega',
        ])->validate();
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Domain\Customer\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class RegisterCustomerAction
{
    public function execute(array $input): Customer
    {
        $data = $this->validate($input);

        return Customer::query()->create([
            'name' => $data['name'],
            'email' => mb_strtolower($data['email']),
            'phone' => $data['phone'],
            'document' => $data['document'],
            'birth_date' => $data['birth_date'] ?? null,
            'password' => Hash::make($data['password']),
        ]);
    }

    private function validate(array $input): array
    {
        foreach (['name', 'email', 'phone', 'document'] as $field) {
            if (is_string($input[$field] ?? null)) {
                $input[$field] = trim($input[$field]);
            }
        }
        if (is_string($input['email'] ?? null)) {
            $input['email'] = mb_strtolower($input['email']);
        }
        foreach (['phone', 'document'] as $field) {
            if (is_string($input[$field] ?? null)) {
                $input[$field] = preg_replace('~\D~u', '', $input[$field]);
            }
        }

        return Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('customers', 'email')],
            'phone' => ['required', 'string', 'regex:/\A[0-9]{10,13}\z/'],
            'document' => ['required', 'string', 'regex:/\A(?:[0-9]{11}|[0-9]{14})\z/', Rule::unique('customers', 'document')],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'required' => 'Informe :attribute.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Já existe uma conta com este e-mail.',
            'document.unique' => 'Já existe uma conta com este CPF/CNPJ.',
            'document.regex' => 'Informe um CPF com 11 dígitos ou CNPJ com 14 dígitos.',
            'phone.regex' => 'Informe um telefone válido com DDD.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'birth_date.before_or_equal' => 'A data de nascimento não pode estar no futuro.',
        ], [
            'name' => 'seu nome', 'email' => 'seu e-mail', 'phone' => 'seu telefone',
            'document' => 'seu CPF/CNPJ', 'password' => 'uma senha',
        ])->validate();
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Domain\Customer\Models\Customer;
use App\Support\BrazilianData;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class UpdateCustomerProfileAction
{
    public function execute(Customer $customer, array $input): Customer
    {
        $input['name'] = trim((string) ($input['name'] ?? ''));
        $input['email'] = mb_strtolower(trim((string) ($input['email'] ?? '')));
        $input['phone'] = BrazilianData::digits($input['phone'] ?? '');
        $data = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customer->id)],
            'phone' => ['required', 'regex:/\A\d{10,13}\z/'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
        ], [
            'email.unique' => 'Este e-mail já está em uso.',
            'phone.regex' => 'Informe um telefone válido com DDD.',
            'birth_date.before_or_equal' => 'A data de nascimento não pode estar no futuro.',
            'required' => 'Informe :attribute.',
        ], ['name' => 'o nome', 'email' => 'o e-mail', 'phone' => 'o telefone'])->validate();
        $customer->update($data);

        return $customer->refresh();
    }
}

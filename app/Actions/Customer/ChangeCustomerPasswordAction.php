<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Domain\Customer\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class ChangeCustomerPasswordAction
{
    public function execute(Customer $customer, array $input): void
    {
        $data = Validator::make($input, [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'required' => 'Preencha este campo.',
            'password.min' => 'A nova senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação da nova senha não confere.',
        ])->validate();
        if (! Hash::check($data['current_password'], $customer->password)) {
            throw ValidationException::withMessages(['current_password' => 'A senha atual está incorreta.']);
        }
        $customer->update(['password' => Hash::make($data['password'])]);
    }
}

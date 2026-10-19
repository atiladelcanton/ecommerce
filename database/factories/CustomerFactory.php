<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '11999990000',
            'document' => (string) fake()->unique()->numerify('###########'),
            'birth_date' => null,
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }
}

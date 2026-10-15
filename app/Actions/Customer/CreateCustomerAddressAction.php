<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Models\CustomerAddress;
use Illuminate\Support\Facades\DB;

final class CreateCustomerAddressAction
{
    public function __construct(private readonly ValidateCustomerAddressData $validate) {}

    public function execute(Customer $customer, array $input): CustomerAddress
    {
        $data = $this->validate->execute($input);
        return DB::transaction(function () use ($customer, $data): CustomerAddress {
            $hasAny = $customer->addresses()->lockForUpdate()->exists();
            $makeDefault = ! $hasAny || $data['is_default'];
            if ($makeDefault) $customer->addresses()->update(['is_default' => false]);
            $data['is_default'] = $makeDefault;
            return $customer->addresses()->create($data);
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Domain\Customer\Models\CustomerAddress;
use Illuminate\Support\Facades\DB;

final class UpdateCustomerAddressAction
{
    public function __construct(private readonly ValidateCustomerAddressData $validate) {}

    public function execute(CustomerAddress $address, array $input): CustomerAddress
    {
        $data = $this->validate->execute($input);

        return DB::transaction(function () use ($address, $data): CustomerAddress {
            $address->customer->addresses()->lockForUpdate()->get();
            if ($data['is_default']) {
                $address->customer->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            } elseif ($address->is_default) {
                // A customer with at least one address must always keep one default address.
                $data['is_default'] = true;
            }
            $address->update($data);

            return $address->refresh();
        });
    }
}

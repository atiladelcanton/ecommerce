<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Domain\Customer\Models\CustomerAddress;
use Illuminate\Support\Facades\DB;

final class DeleteCustomerAddressAction
{
    public function execute(CustomerAddress $address): void
    {
        DB::transaction(function () use ($address): void {
            $customer = $address->customer;
            $wasDefault = $address->is_default;
            $customer->addresses()->lockForUpdate()->get();
            $address->delete();
            if ($wasDefault) {
                $customer->addresses()->orderBy('id')->first()?->update(['is_default' => true]);
            }
        });
    }
}

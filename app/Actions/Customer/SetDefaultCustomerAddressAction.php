<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Domain\Customer\Models\CustomerAddress;
use Illuminate\Support\Facades\DB;

final class SetDefaultCustomerAddressAction
{
    public function execute(CustomerAddress $address): void
    {
        DB::transaction(function () use ($address): void {
            $address->customer->addresses()->lockForUpdate()->get();
            $address->customer->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });
    }
}

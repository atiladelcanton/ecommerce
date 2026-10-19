<?php

declare(strict_types=1);

namespace App\Filament\Resources\Customers\Pages;

use App\Domain\Payment\Enums\PaymentStatus;
use App\Filament\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\ViewRecord;

final class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected string $view = 'filament.resources.customers.pages.view-customer';

    protected function getViewData(): array
    {
        $customer = $this->record;
        $customer->loadMissing('addresses');

        return [
            'ordersCount' => $customer->orders()->count(),
            'paidOrdersCount' => $customer->orders()->where('payment_status', PaymentStatus::APPROVED->value)->count(),
            'paidTotalCents' => (int) $customer->orders()->where('payment_status', PaymentStatus::APPROVED->value)->sum('total_cents'),
            'lastOrderAt' => $customer->orders()->max('created_at'),
            'recentOrders' => $customer->orders()->withCount('items')->limit(20)->get(),
        ];
    }
}

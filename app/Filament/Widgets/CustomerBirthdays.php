<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Customer\Models\Customer;
use Filament\Widgets\Widget;

final class CustomerBirthdays extends Widget
{
    protected string $view = 'filament.widgets.customer-birthdays';

    protected int|string|array $columnSpan = [
        'md' => 12,
        'xl' => 6,
    ];

    protected function getViewData(): array
    {
        $customers = Customer::query()
            ->whereNotNull('birth_date')
            ->whereMonth('birth_date', now()->month)
            ->get()
            ->sortBy(fn (Customer $customer): int => (int) $customer->birth_date?->format('d'))
            ->values();

        return [
            'customers' => $customers->take(8),
            'totalBirthdays' => $customers->count(),
            'monthLabel' => now()->locale('pt_BR')->translatedFormat('F'),
        ];
    }
}

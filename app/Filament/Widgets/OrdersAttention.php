<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use Filament\Widgets\Widget;

final class OrdersAttention extends Widget
{
    protected string $view = 'filament.widgets.orders-attention';

    protected int|string|array $columnSpan = [
        'md' => 12,
        'xl' => 8,
    ];

    protected function getViewData(): array
    {
        return [
            'orders' => Order::query()
                ->whereIn('status', [OrderStatus::PAID->value, OrderStatus::PROCESSING->value, OrderStatus::READY_FOR_SHIPPING->value])
                ->withCount('items')
                ->latest()
                ->limit(6)
                ->get(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Order\Models\OrderItem;
use App\Domain\Payment\Enums\PaymentStatus;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

final class BestSellingProducts extends Widget
{
    protected string $view = 'filament.widgets.best-selling-products';

    protected int|string|array $columnSpan = [
        'md' => 12,
        'xl' => 4,
    ];

    protected function getViewData(): array
    {
        $range = [now()->startOfMonth(), now()->endOfMonth()];

        /** @var Collection<int, object{product_name:string,total_quantity:int,revenue_cents:int}> $products */
        $products = OrderItem::query()
            ->selectRaw('product_name, SUM(quantity) as total_quantity, SUM(total) as revenue_cents')
            ->whereHas('order', function ($query) use ($range): void {
                $query
                    ->where('payment_status', PaymentStatus::APPROVED->value)
                    ->whereBetween('created_at', $range);
            })
            ->groupBy('product_name')
            ->orderByDesc('total_quantity')
            ->orderByDesc('revenue_cents')
            ->limit(5)
            ->get();

        return [
            'products' => $products,
            'monthLabel' => now()->locale('pt_BR')->translatedFormat('F'),
        ];
    }
}

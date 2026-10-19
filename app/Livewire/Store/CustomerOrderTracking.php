<?php

declare(strict_types=1);

namespace App\Livewire\Store;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Shipping\OrderShipping;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class CustomerOrderTracking extends Component
{
    #[Locked]
    public string $number;

    public function mount(string $number): void
    {
        $this->number = $number;
        $this->loadOrder();
    }

    public function render(): View
    {
        $order = $this->loadOrder();

        return view('livewire.store.customer-order-tracking', [
            'order' => $order,
            'steps' => $this->steps($order->status),
        ]);
    }

    private function loadOrder(): Order
    {
        $customerId = Auth::guard('customer')->id();
        abort_unless($customerId, 403);

        return Order::query()
            ->with(['items', 'address', 'payments', 'statusHistories', 'shipments.events', 'latestShippingQuote'])
            ->where('customer_id', $customerId)
            ->where('number', $this->number)
            ->firstOrFail();
    }

    private function isPickupOrder(): bool
    {
        $customerId = Auth::guard('customer')->id();
        if (! $customerId) {
            return false;
        }

        $order = Order::query()
            ->where('customer_id', $customerId)
            ->where('number', $this->number)
            ->first(['shipping_provider', 'shipping_option_id', 'shipping_quote_snapshot']);

        if (! $order) {
            return false;
        }

        return OrderShipping::mode($order) === 'pickup';
    }

    private function stepDescription(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::PENDING => 'Recebemos seu pedido',
            OrderStatus::PAID => 'Pagamento confirmado',
            OrderStatus::PROCESSING => 'Preparando seus itens',
            OrderStatus::READY_FOR_SHIPPING => 'Embalado e etiquetado',
            OrderStatus::READY_FOR_PICKUP => 'Disponível para retirada',
            OrderStatus::SHIPPED => 'Pedido a caminho',
            OrderStatus::DELIVERED => 'Entrega concluída',
            OrderStatus::CANCELLED => 'Pedido cancelado',
        };
    }

    private function steps(OrderStatus $current): array
    {
        $sequence = $this->isPickupOrder()
            ? [OrderStatus::PENDING, OrderStatus::PAID, OrderStatus::PROCESSING, OrderStatus::READY_FOR_PICKUP, OrderStatus::DELIVERED]
            : [OrderStatus::PENDING, OrderStatus::PAID, OrderStatus::PROCESSING, OrderStatus::READY_FOR_SHIPPING, OrderStatus::SHIPPED, OrderStatus::DELIVERED];
        $currentIndex = array_search($current, $sequence, true);

        return array_map(function (OrderStatus $status, int $index) use ($current, $currentIndex): array {
            return [
                'status' => $status,
                'label' => $status->label(),
                'complete' => $current !== OrderStatus::CANCELLED && $currentIndex !== false && $index <= $currentIndex,
                'current' => $status === $current,
                'description' => $this->stepDescription($status),
            ];
        }, $sequence, array_keys($sequence));
    }
}

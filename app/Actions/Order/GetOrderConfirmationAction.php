<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Domain\Order\Models\Order;
use Illuminate\Contracts\Session\Session;

final class GetOrderConfirmationAction
{
    public function __construct(private readonly Session $session) {}

    public function execute(string $number): Order
    {
        return Order::query()->with(['items', 'address', 'payments', 'statusHistories'])
            ->where('number', $number)->where('checkout_session_hash', hash('sha256', $this->session->getId()))->firstOrFail();
    }
}

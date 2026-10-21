<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payment;

use App\Domain\Order\Models\Order;
use Illuminate\Http\Response;

final class PaymentStatusController
{
    public function __invoke(string $number): Response
    {
        $order = Order::query()->with('payments')->where('number', $number)->firstOrFail();
        $authorized = auth('customer')->id() !== null && auth('customer')->id() === $order->customer_id;
        $authorized = $authorized || session('payment.return.orders.'.$order->id) === hash('sha256', $order->checkout_token);
        $authorized = $authorized || hash_equals($order->checkout_session_hash, hash('sha256', session()->getId()));
        abort_unless($authorized, 404);
        return response()->view('store.payment-status', ['order' => $order])->header('Cache-Control', 'private, no-store');
    }
}

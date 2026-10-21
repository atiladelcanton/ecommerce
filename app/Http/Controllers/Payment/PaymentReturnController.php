<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payment;

use App\Domain\Order\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PaymentReturnController
{
    public function __invoke(string $number, Request $request): RedirectResponse
    {
        $order = Order::query()->where('number', $number)->firstOrFail();
        $key = (string) $request->query('key', '');
        $expected = hash_hmac('sha256', 'payment-return:'.$order->id, $order->checkout_token);
        abort_unless($key !== '' && hash_equals($expected, $key), 404);
        session()->put('payment.return.orders.'.$order->id, hash('sha256', $order->checkout_token));
        $response = redirect()->route('store.order.payment-status', ['number' => $order->number]);
        $response->headers->set('Cache-Control', 'private, no-store');
        return $response;
    }
}

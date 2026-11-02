<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Actions\Order\GetQuotedOrderAction;
use App\Domain\Order\Models\Order;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ShippingQuoteCheckoutController
{
    public function __invoke(Request $request, string $number, int $revision, GetQuotedOrderAction $action): Response
    {
        $customerId = Order::query()->where('number', $number)->value('customer_id');
        if ($customerId && ! auth('customer')->check()) {
            return redirect()->guest(route('customer.login'));
        }
        $order = $action->execute($number, $revision, $request->fullUrl());
        // A valid private quotation link also grants this browser access to payment
        // status and retry; it does not grant access to any other order.
        session()->put('payment.return.orders.'.$order->id, hash('sha256', $order->checkout_token));

        return response()->view('store.shipping-quote-checkout', ['number' => $number, 'revision' => $revision, 'signedUrl' => $request->fullUrl()])
            ->header('Cache-Control', 'private, no-store')->header('Referrer-Policy', 'no-referrer')->header('X-Robots-Tag', 'noindex, nofollow');
    }
}

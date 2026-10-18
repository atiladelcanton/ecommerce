<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Domain\Order\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

final class GetQuotedOrderAction
{
    public function execute(string $number, int $revision, string $signedUrl): Order
    {
        $request = Request::create($signedUrl);
        $expected = route('store.order.shipping-quote', ['number' => $number, 'revision' => $revision]);
        abort_unless(URL::hasValidSignature($request) && $request->getPathInfo() === parse_url($expected, PHP_URL_PATH), 403);
        $order = Order::query()->with(['items', 'address', 'latestShippingQuote', 'payments'])->where('number', $number)->firstOrFail();
        abort_unless($order->customer_id === null || auth('customer')->id() === $order->customer_id, 403);
        $quote = $order->latestShippingQuote;
        abort_unless($quote && $quote->revision === $revision && ! $quote->superseded_at && $quote->expires_at->isFuture(), 410, 'Esta cotação foi substituída ou venceu. Solicite um novo link à loja.');

        return $order;
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Domain\Order\Models\OrderShippingQuote;
use Illuminate\Support\Facades\URL;

final class GetOrderShippingQuoteLinkAction
{
    public function execute(OrderShippingQuote $quote): string
    {
        return URL::temporarySignedRoute('store.order.shipping-quote', $quote->expires_at, ['number' => $quote->order->number, 'revision' => $quote->revision]);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Actions\Order\GetOrderConfirmationAction;
use App\Actions\Order\GetOrderShippingContactAction;
use Illuminate\Http\Response;

final class OrderConfirmationController
{
    public function __invoke(string $number, GetOrderConfirmationAction $action): Response
    {
        $order = $action->execute($number);

        return response()->view('store.order-confirmation', ['order' => $order, 'shippingContactUrl' => app(GetOrderShippingContactAction::class)->execute($order)])
            ->header('Cache-Control', 'private, no-store');
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Domain\Order\Models\Order;
use App\Domain\Settings\Models\StoreSetting;
use App\Support\Storefront\Links;

final class GetOrderShippingContactAction
{
    public function execute(Order $order): ?string
    {
        $message = "Olá! Solicitei a cotação de entrega do pedido {$order->number}.\n".$order->items->map(fn ($item): string => $item->quantity.' × '.$item->product_name.($item->variant_value ? ' — '.$item->variant_value : ''))->implode("\n")."\nCEP: ".$order->address?->postal_code;

        return Links::whatsapp(StoreSetting::query()->find(1)?->whatsapp, $message);
    }
}

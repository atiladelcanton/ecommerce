<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Domain\Order\Models\OrderShippingQuote;
use App\Support\Storefront\Links;

final class GetCustomerQuoteContactLinkAction
{
    public function execute(OrderShippingQuote $quote): ?string
    {
        $phone = preg_replace('/\D/', '', $quote->order->customer_phone);
        if (in_array(strlen($phone), [10, 11], true)) {
            $phone = '55'.$phone;
        }
        $link = app(GetOrderShippingQuoteLinkAction::class)->execute($quote);

        return Links::whatsapp($phone, 'Olá! Sua cotação de entrega está disponível. Revise o pedido e conclua a compra: '.$link);
    }
}

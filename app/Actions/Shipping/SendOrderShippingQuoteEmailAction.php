<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Shipping\Enums\ShippingPricingStatus;
use App\Support\BrazilianMoney;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/** Explicit operator action; email is never treated as proof of delivery/acceptance. */
final class SendOrderShippingQuoteEmailAction
{
    public function execute(Order $order, int $userId): void
    {
        $order->refresh();
        $quote = $order->latestShippingQuote;
        if ($order->status !== OrderStatus::RECEIVED || $order->shipping_pricing_status !== ShippingPricingStatus::QUOTED
            || ! $quote || $quote->accepted_at || $quote->superseded_at || $quote->expires_at->isPast()) {
            throw ValidationException::withMessages(['shipping' => 'Publique uma cotação válida e ainda não aceita antes de enviar o e-mail.']);
        }
        if (! filter_var($order->customer_email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages(['shipping' => 'O e-mail deste cliente não é válido.']);
        }
        $link = app(GetOrderShippingQuoteLinkAction::class)->execute($quote);
        $body = "Olá, {$order->customer_name}!\n\n".
            "A cotação da entrega do pedido {$order->number} está pronta.\n\n".
            "Transportadora: {$quote->carrier_name}\n".
            "Serviço: {$quote->service_name}\n".
            "Frete: ".BrazilianMoney::display($quote->price_cents)."\n".
            "Total do pedido: {$order->formattedTotal()}\n".
            "Prazo estimado: ".($quote->estimated_days === null ? 'A combinar' : $quote->estimated_days.' dias úteis')."\n".
            "Válida até: {$quote->expires_at->format('d/m/Y H:i')}\n\n".
            "Para revisar, aceitar e pagar de forma segura, acesse:\n{$link}\n\n".
            "O pagamento só será considerado confirmado após a aprovação pelo provedor.\n";
        Mail::raw($body, function ($message) use ($order): void {
            $message->to($order->customer_email)->subject('Cotação da entrega do pedido '.$order->number);
        });
        $order->statusHistories()->create([
            'user_id' => $userId,
            'from_status' => $order->status,
            'to_status' => $order->status,
            'description' => 'Cotação enviada por e-mail ao endereço cadastrado (entrega não garantida).',
        ]);
    }
}

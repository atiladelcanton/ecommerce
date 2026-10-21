<?php

declare(strict_types=1);

namespace App\Livewire\Store;

use App\Actions\Order\ConfirmQuotedOrderAction;
use App\Actions\Order\GetQuotedOrderAction;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class ShippingQuoteCheckout extends Component
{
    #[Locked]
    public string $number;

    #[Locked]
    public int $revision;

    #[Locked]
    public string $signedUrl;

    public function confirmPayment(ConfirmQuotedOrderAction $action): void
    {
        $this->resetValidation();
        $order = $action->execute($this->number, $this->revision, $this->signedUrl);
        $payment = $order->payments()->latest('id')->first();
        if ($payment && $payment->gateway === 'asaas' && ! $payment->is_demo && $payment->checkout_url && \App\Infrastructure\Payments\AsaasGateway::isCheckoutUrl($payment->checkout_url, $payment->environment)) {
            $this->redirect($payment->checkout_url);
        }
    }

    public function render(): View
    {
        return view('livewire.store.shipping-quote-checkout', ['order' => app(GetQuotedOrderAction::class)->execute($this->number, $this->revision, $this->signedUrl)]);
    }
}

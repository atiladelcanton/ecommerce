<?php

declare(strict_types=1);

namespace App\Livewire\Store;

use App\Actions\Order\ConfirmQuotedOrderAction;
use App\Actions\Order\GetQuotedOrderAction;
use Illuminate\Contracts\View\View;
use App\Infrastructure\Payments\AsaasGateway;
use App\Domain\Payment\Enums\PaymentStatus;
use Illuminate\Validation\ValidationException;
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
        if ($order->payment_status === PaymentStatus::APPROVED) {
            $this->redirectRoute('store.order.payment-status', ['number' => $order->number]);
            return;
        }
        $payment = $order->payments()->latest('id')->first();
        if ($payment?->gateway === 'asaas' && AsaasGateway::isCheckoutUrl($payment->checkout_url, $payment->environment)) {
            $this->redirect($payment->checkout_url);
            return;
        }
        if ($payment?->needs_review) {
            throw ValidationException::withMessages(['payment' => 'A cobrança está em análise pela loja. Não tente criar outra cobrança.']);
        }
        $this->redirectRoute('store.order.payment-status', ['number' => $order->number]);
    }

    public function render(): View
    {
        return view('livewire.store.shipping-quote-checkout', ['order' => app(GetQuotedOrderAction::class)->execute($this->number, $this->revision, $this->signedUrl)]);
    }
}

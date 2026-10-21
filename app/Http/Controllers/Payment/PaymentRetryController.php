<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payment;

use App\Actions\Order\PayOrderAction;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Infrastructure\Payments\AsaasGateway;
use Illuminate\Http\RedirectResponse;

final class PaymentRetryController
{
    public function __invoke(string $number, PayOrderAction $action): RedirectResponse
    {
        $order = Order::query()->where('number', $number)->firstOrFail();
        $authorized = auth('customer')->id() !== null && auth('customer')->id() === $order->customer_id;
        $authorized = $authorized || session('payment.return.orders.'.$order->id) === hash('sha256', $order->checkout_token);
        $authorized = $authorized || hash_equals($order->checkout_session_hash, hash('sha256', session()->getId()));
        abort_unless($authorized, 404);
        if ($order->payment_status === PaymentStatus::APPROVED) {
            return redirect()->route('store.order.payment-status', ['number' => $order->number]);
        }
        $action->execute($order);
        $payment = $order->payments()->latest('id')->first();
        if ($payment && $payment->gateway === 'asaas' && $payment->checkout_url && AsaasGateway::isCheckoutUrl($payment->checkout_url, $payment->environment)) {
            return redirect()->away($payment->checkout_url);
        }
        return redirect()->route('store.order.payment-status', ['number' => $order->number])
            ->with('payment_notice', 'Cobrança em preparação. A reserva continua ativa; atualize o pagamento mais tarde.');
    }
}

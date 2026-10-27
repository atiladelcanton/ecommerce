<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Actions\Catalog\ManageOrderInventoryAction;
use App\Actions\Payment\HandleApprovedPaymentAction;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\Data\PaymentRequest;
use App\Domain\Payment\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PayOrderAction
{
    public function execute(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($order->payment_status === PaymentStatus::APPROVED) {
                return $order;
            }
            if ($order->status !== OrderStatus::RECEIVED || $order->total_cents === null || $order->shipping_cents === null) {
                throw ValidationException::withMessages(['payment' => 'Aguarde a definição do frete antes de concluir o pagamento.']);
            }
            // Consume within the same transaction, before calling the configured demo gateway.
            // Failure rolls everything back. Replayed approval consumes each reservation once.
            app(ManageOrderInventoryAction::class)->consume($order);
            $payment = app(PaymentGateway::class)->createPayment(new PaymentRequest($order->number, $order->total_cents, $order->checkout_token));
            if ($payment->status !== PaymentStatus::APPROVED) {
                throw ValidationException::withMessages(['payment' => 'O pagamento de demonstração não foi aprovado. Tente novamente.']);
            }
            $model = $order->payments()->create([
                'gateway' => $payment->gateway, 'external_id' => $payment->externalId, 'status' => $payment->status,
                'amount_cents' => $order->total_cents, 'is_demo' => $payment->isDemo,
            ]);

            return app(HandleApprovedPaymentAction::class)->execute($order, $model);
        }, 3);
    }
}

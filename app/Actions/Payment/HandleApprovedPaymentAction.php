<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Actions\Catalog\ManageOrderInventoryAction;
use App\Actions\Coupon\ManageCouponUsageAction;
use App\Actions\Order\TransitionOrderStatusAction;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class HandleApprovedPaymentAction
{
    public function __construct(
        private readonly TransitionOrderStatusAction $transition,
    ) {}

    public function execute(Order $order, Payment $payment): Order
    {
        return DB::transaction(function () use ($order, $payment): Order {
            /** @var Order $lockedOrder */
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            /** @var Payment $lockedPayment */
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($lockedPayment->order_id !== $lockedOrder->id) {
                throw ValidationException::withMessages(['payment' => 'O pagamento não pertence a este pedido.']);
            }
            if ($lockedOrder->total_cents === null || $lockedOrder->shipping_cents === null) {
                throw ValidationException::withMessages(['payment' => 'Defina o frete antes de aprovar o pagamento.']);
            }
            if ($lockedOrder->status === OrderStatus::CANCELLED) {
                throw ValidationException::withMessages(['payment' => 'Pedido cancelado com cobrança recebida. Revisão financeira necessária.']);
            }
            app(ManageOrderInventoryAction::class)->consume($lockedOrder);
            app(ManageCouponUsageAction::class)->consume($lockedOrder);
            if ($lockedPayment->status !== PaymentStatus::APPROVED) {
                $lockedPayment->update(['status' => PaymentStatus::APPROVED]);
            }
            if ($lockedOrder->payment_status !== PaymentStatus::APPROVED) {
                $lockedOrder->update(['payment_status' => PaymentStatus::APPROVED]);
            }

            if ($lockedOrder->status === OrderStatus::PENDING) {
                $lockedOrder = $this->transition->execute(
                    $lockedOrder,
                    OrderStatus::PAID,
                    description: 'Pagamento aprovado automaticamente.',
                );
            }

            return $lockedOrder->refresh();
        }, 3);
    }
}

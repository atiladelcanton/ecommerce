<?php

declare(strict_types=1);

namespace App\Actions\Finance;

use App\Domain\Finance\Enums\CashFlowType;
use App\Domain\Finance\Models\CashFlowEntry;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;

final class RecordApprovedPaymentIncomeAction
{
    public function execute(Payment $payment): ?CashFlowEntry
    {
        $payment->loadMissing('order');
        if ($payment->status !== PaymentStatus::APPROVED || ! $payment->order) {
            return null;
        }

        return CashFlowEntry::query()->firstOrCreate(
            ['payment_id' => $payment->id],
            [
                'type' => CashFlowType::INCOME,
                'category' => 'Venda',
                'description' => 'Pedido '.$payment->order->number,
                'amount_cents' => $payment->amount_cents,
                'occurred_at' => $payment->updated_at ?? now(),
                'order_id' => $payment->order_id,
                'source' => 'payment',
            ],
        );
    }
}

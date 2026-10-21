<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;
use Illuminate\Support\Facades\Log;

final class ReconcilePendingPaymentsAction
{
    public function execute(int $limit = 50): int
    {
        $count = 0;
        Payment::query()->whereIn('gateway', ['mercado_pago', 'asaas'])->where('status', PaymentStatus::PENDING->value)
            ->where(function ($query): void {
                $query->whereNull('last_checked_at')->orWhere('last_checked_at', '<', now()->subMinutes(5));
            })->orderBy('last_checked_at')->orderBy('id')->limit($limit)->get()->each(function (Payment $payment) use (&$count): void {
                try {
                    if ($payment->gateway === 'asaas') {
                        // Asaas Checkout has no documented GET checkout/status and no safe
                        // replay-idempotency guarantee for POST /checkouts. Await signed webhook.
                        if (str_starts_with($payment->external_id, 'initiating-') && ! $payment->needs_review) {
                            $payment->update(['needs_review' => true, 'review_reason' => 'Criação Asaas inconclusiva: verificar no Asaas antes de tentar novamente.']);
                        }
                        if (! str_starts_with($payment->external_id, 'initiating-') && $payment->expires_at?->addMinutes(20)->isPast() && ! $payment->needs_review) {
                            // The local timer is NOT proof of financial expiration.
                            $payment->update(['needs_review' => true, 'review_reason' => 'Checkout passou do prazo esperado sem webhook: verificar no Asaas. Reserva mantida.']);
                        }
                        $payment->update(['last_checked_at' => now()]);
                    } elseif (! str_starts_with($payment->external_id, 'ORD')) {
                        // An uncertain create is repeated only with its ORIGINAL idempotency key.
                        app(\App\Actions\Order\PayOrderAction::class)->resumePending($payment);
                    } else {
                        app(ReconcileHostedPaymentAction::class)->execute($payment);
                    }
                    $count++;
                } catch (\Throwable $exception) {
                    Log::warning('Falha ao conciliar pagamento remoto.', ['payment_id' => $payment->id, 'exception' => $exception::class]);
                }
            });
        return $count;
    }
}

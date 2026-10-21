<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Actions\Catalog\ManageOrderInventoryAction;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;
use App\Domain\Payment\Models\PaymentEvent;
use App\Infrastructure\Payments\AsaasGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ProcessAsaasCheckoutEventAction
{
    public function execute(PaymentEvent $event): void
    {
        if ($event->gateway !== 'asaas' || $event->processed_at) {
            return;
        }
        $payload = $event->payload ?? [];
        $eventName = $event->action;
        $checkout = $payload['checkout'] ?? [];
        if (! is_array($checkout) || ($checkout['id'] ?? null) !== $event->external_id) {
            throw new \RuntimeException('Identidade do evento Asaas inválida.');
        }
        $expectedStatus = match ($eventName) {
            'CHECKOUT_CREATED' => 'ACTIVE',
            'CHECKOUT_PAID' => 'PAID',
            'CHECKOUT_CANCELED' => 'CANCELED',
            'CHECKOUT_EXPIRED' => 'EXPIRED',
            default => null,
        };
        if ($expectedStatus === null || ($checkout['status'] ?? null) !== $expectedStatus) {
            throw new \RuntimeException('Status/evento Asaas incompatíveis.');
        }
        $payment = Payment::query()->where('gateway', 'asaas')->where('environment', $event->environment)
            ->where('external_id', $event->external_id)->first();
        if (! $payment) {
            // A provider notification may arrive before the create-checkout HTTP response
            // has been saved. Recover only the unique in-flight attempt of that order.
            $reference = $checkout['externalReference'] ?? null;
            if (is_string($reference) && $reference !== '') {
                $payment = Payment::query()->where('gateway', 'asaas')->where('environment', $event->environment)
                    ->where('status', PaymentStatus::PENDING->value)->where('external_id', 'like', 'initiating-%')
                    ->whereHas('order', fn ($query) => $query->where('number', $reference))->sole();
            }
            if (! $payment) {
                throw new \RuntimeException('Checkout Asaas ainda não vinculado ao pedido.');
            }
        }
        DB::transaction(function () use ($event, $payment, $checkout, $expectedStatus): void {
            $order = Order::query()->lockForUpdate()->findOrFail($payment->order_id);
            $locked = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $event = PaymentEvent::query()->lockForUpdate()->findOrFail($event->id);
            if ($event->processed_at) {
                return;
            }
            if ($locked->payment_integration_id === null || $locked->environment !== $event->environment
                || $locked->gateway !== 'asaas') {
                throw new \RuntimeException('Ambiente/integração do pagamento diverge do evento.');
            }
            $reference = $checkout['externalReference'] ?? null;
            if ($reference !== null && $reference !== $order->number) {
                $this->flag($locked, 'Referência externa divergente no evento Asaas.');
                $event->update(['payment_id' => $locked->id, 'processed_at' => now()]);
                return;
            }
            // Checkout event items are authoritative only after validating their
            // integer-cent total against the immutable order amount.
            $items = $checkout['items'] ?? null;
            if (! is_array($items) || $items === [] || $this->sumItems($items) !== $locked->amount_cents) {
                $this->flag($locked, 'Valor ou itens divergentes no evento Asaas. Conferência financeira necessária.');
                $event->update(['payment_id' => $locked->id, 'processed_at' => now()]);
                return;
            }
            if (str_starts_with($locked->external_id, 'initiating-')) {
                $locked->external_id = $event->external_id;
                // The webhook may precede the create-checkout response. Never
                // synthesize a public URL; the API response supplies its link.
            } elseif ($locked->external_id !== $event->external_id) {
                throw new \RuntimeException('Identidade do checkout alterada inesperadamente.');
            }
            $locked->provider_status = $expectedStatus;
            $locked->status_detail = mb_substr((string) $event->action, 0, 128);
            $locked->last_checked_at = now();
            $locked->save();
            if ($locked->needs_review && $locked->review_reason === 'Criação Asaas inconclusiva: verificar no Asaas antes de tentar novamente.') {
                $locked->update(['needs_review' => false, 'review_reason' => null]);
            }
            if ($expectedStatus === 'PAID') {
                if (! in_array($locked->status, [PaymentStatus::APPROVED, PaymentStatus::REFUNDED], true)) {
                    $anotherApproved = $order->payments()->whereKeyNot($locked->id)
                        ->where('status', PaymentStatus::APPROVED->value)->exists();
                    if ($anotherApproved) {
                        $locked->update(['status' => PaymentStatus::APPROVED]);
                        $this->flag($locked, 'Pagamento adicional aprovado em pedido já pago: verifique reembolso.');
                    } else {
                        try {
                            DB::transaction(fn () => app(HandleApprovedPaymentAction::class)->execute($order, $locked));
                        } catch (ValidationException $exception) {
                            $locked->update(['status' => PaymentStatus::APPROVED]);
                            $order->update(['payment_status' => PaymentStatus::APPROVED]);
                            $this->flag($locked, 'Pagamento recebido, mas pedido/estoque exige atendimento manual.');
                        }
                    }
                }
            } elseif (in_array($expectedStatus, ['CANCELED', 'EXPIRED'], true)) {
                if (! in_array($locked->status, [PaymentStatus::APPROVED, PaymentStatus::REFUNDED], true)) {
                    $locked->update(['status' => PaymentStatus::CANCELLED, 'checkout_url' => null]);
                    $anotherPending = $order->payments()->whereKeyNot($locked->id)
                        ->where('status', PaymentStatus::PENDING->value)->exists();
                    if (! $anotherPending && $order->payment_status !== PaymentStatus::APPROVED) {
                        $order->update(['payment_status' => PaymentStatus::CANCELLED]);
                        app(ManageOrderInventoryAction::class)->releasePaymentHold($order);
                    }
                }
            }
            $event->update(['payment_id' => $locked->id, 'processed_at' => now()]);
        }, 3);
    }

    private function flag(Payment $payment, string $reason): void
    {
        $payment->update(['needs_review' => true, 'review_reason' => $reason]);
    }

    private function sumItems(array $items): ?int
    {
        $sum = 0;
        foreach ($items as $item) {
            if (! is_array($item) || ! is_int($item['quantity'] ?? null) || $item['quantity'] < 1
                || ! is_numeric($item['value'] ?? null)) {
                return null;
            }
            $raw = is_string($item['value']) ? $item['value'] : json_encode($item['value'], JSON_PRESERVE_ZERO_FRACTION);
            $cents = AsaasGateway::cents($raw);
            if ($cents === null || $cents <= 0 || $cents > intdiv(PHP_INT_MAX - $sum, $item['quantity'])) {
                return null;
            }
            $sum += $cents * $item['quantity'];
        }
        return $sum;
    }
}

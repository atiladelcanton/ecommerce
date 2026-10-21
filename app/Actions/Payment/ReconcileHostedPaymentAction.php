<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Actions\Catalog\ManageOrderInventoryAction;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;
use App\Domain\Payment\Models\PaymentIntegration;
use App\Infrastructure\Payments\MercadoPagoGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReconcileHostedPaymentAction
{
    public function execute(Payment $payment): void
    {
        if ($payment->gateway !== 'mercado_pago' || ! str_starts_with($payment->external_id, 'ORD')) {
            return; // Unknown remote identity: never release an uncertain charge.
        }
        $integration = PaymentIntegration::query()->findOrFail($payment->payment_integration_id);
        if ($integration->environment !== $payment->environment) {
            throw new \RuntimeException('O ambiente histórico do pagamento não corresponde à integração.');
        }
        $remote = (new MercadoPagoGateway($integration))->inspect($payment->external_id);
        $status = (string) ($remote['status'] ?? '');
        $detail = (string) ($remote['status_detail'] ?? '');
        $amount = MercadoPagoGateway::cents((string) ($remote['total_amount'] ?? ''));
        if (($remote['external_reference'] ?? null) !== $payment->order->number || $amount !== $payment->amount_cents
            || (! empty($remote['currency']) && $remote['currency'] !== 'BRL')) {
            DB::transaction(function () use ($payment): void {
                $order = Order::query()->lockForUpdate()->findOrFail($payment->order_id);
                Payment::query()->whereKey($payment->id)->update(['needs_review' => true, 'review_reason' => 'Referência, moeda ou valor divergente no Mercado Pago.', 'last_checked_at' => now()]);
            });
            throw new \RuntimeException('Divergência de identidade/valor na conciliação do Mercado Pago.');
        }
        if (! in_array($status, ['created', 'processing', 'action_required', 'processed', 'failed', 'canceled', 'refunded'], true)) {
            throw new \RuntimeException('Estado remoto desconhecido: não liberar estoque.');
        }
        DB::transaction(function () use ($payment, $status, $detail): void {
            $order = Order::query()->lockForUpdate()->findOrFail($payment->order_id);
            $locked = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $locked->update(['provider_status' => $status, 'status_detail' => $detail, 'last_checked_at' => now()]);
            if ($status === 'processed' && $detail === 'accredited') {
                if ($locked->status === PaymentStatus::REFUNDED) {
                    return; // Older/out-of-order provider snapshot must not undo a refund.
                }
                if ($locked->status === PaymentStatus::APPROVED && ! $locked->needs_review) {
                    return;
                }
                // A delayed approval of an earlier attempt must not silently approve a second charge.
                $alreadyPaid = $order->payments()->whereKeyNot($locked->id)
                    ->where('status', PaymentStatus::APPROVED->value)->exists();
                if ($alreadyPaid) {
                    $locked->update(['status' => PaymentStatus::APPROVED, 'needs_review' => true,
                        'review_reason' => 'Cobrança adicional aprovada para pedido já pago: verificar reembolso.']);
                    return;
                }
                try {
                    DB::transaction(function () use ($order, $locked): void {
                        app(HandleApprovedPaymentAction::class)->execute($order, $locked);
                    });
                    $locked->update(['needs_review' => false, 'review_reason' => null]);
                } catch (ValidationException $exception) {
                    // An actual paid transaction must remain visible even if stock was consumed elsewhere.
                    $locked->update(['status' => PaymentStatus::APPROVED, 'needs_review' => true,
                        'review_reason' => 'Pagamento recebido, mas o estoque/pedido exige tratamento manual.']);
                    $order->update(['payment_status' => PaymentStatus::APPROVED]);
                }
                return;
            }
            if ($status === 'refunded' || ($status === 'processed' && $detail === 'partially_refunded')) {
                $locked->update(['status' => $status === 'refunded' ? PaymentStatus::REFUNDED : PaymentStatus::APPROVED,
                    'needs_review' => true, 'review_reason' => 'Reembolso informado pelo gateway. Verifique o pedido e o estoque.']);
                if ($status === 'refunded') {
                    $order->update(['payment_status' => PaymentStatus::REFUNDED]);
                }
                // Refund is not an automatic return of goods to physical inventory.
                return;
            }
            if (in_array($status, ['failed', 'canceled'], true)) {
                // Never regress an approved payment because of a late or inconsistent notification.
                if (in_array($locked->status, [PaymentStatus::APPROVED, PaymentStatus::REFUNDED], true)) {
                    return;
                }
                $newStatus = $status === 'failed' ? PaymentStatus::FAILED : PaymentStatus::CANCELLED;
                $locked->update(['status' => $newStatus, 'checkout_url' => null]);
                if ($order->payment_status !== PaymentStatus::APPROVED) {
                    $otherActive = $order->payments()->whereKeyNot($locked->id)->where('status', PaymentStatus::PENDING->value)->exists();
                    if (! $otherActive) {
                        $order->update(['payment_status' => $newStatus]);
                        app(ManageOrderInventoryAction::class)->releasePaymentHold($order);
                    }
                }
            }
            // created/processing/action_required remain pending regardless of the local deadline.
        }, 3);
    }
}

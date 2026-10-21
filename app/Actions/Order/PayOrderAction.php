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
use App\Domain\Payment\Models\Payment;
use App\Infrastructure\Payments\PaymentGatewayRegistry;
use App\Infrastructure\Payments\MercadoPagoGateway;
use App\Infrastructure\Payments\AsaasGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PayOrderAction
{
    public function execute(Order $order): Order
    {
        $registry = app(PaymentGatewayRegistry::class);
        $integration = $registry->active();
        if ($integration !== null) {
            return $this->hosted($order, $registry, $integration);
        }
        if (! $registry->fakeAllowed()) {
            throw ValidationException::withMessages(['payment' => 'Nenhum meio de pagamento real está ativo. Configure Meios de Pagamento.']);
        }
        return DB::transaction(function () use ($order): Order {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($order->payment_status === PaymentStatus::APPROVED) {
                return $order;
            }
            $this->assertPayable($order);
            app(ManageOrderInventoryAction::class)->consume($order);
            $result = app(PaymentGateway::class)->createPayment(new PaymentRequest($order->number, $order->total_cents, $order->checkout_token));
            if ($result->status !== PaymentStatus::APPROVED) {
                throw ValidationException::withMessages(['payment' => 'O pagamento de demonstração não foi aprovado.']);
            }
            $payment = $order->payments()->firstOrCreate(['external_id' => $result->externalId], [
                'gateway' => $result->gateway, 'status' => $result->status, 'amount_cents' => $order->total_cents, 'is_demo' => true,
            ]);
            return app(HandleApprovedPaymentAction::class)->execute($order, $payment);
        }, 3);
    }

    /** Retry a remote creation with the ORIGINAL idempotency key and historical credentials. */
    public function resumePending(Payment $payment): void
    {
        if ($payment->gateway !== 'mercado_pago' || $payment->status !== PaymentStatus::PENDING || ! str_starts_with($payment->external_id, 'initiating-')) {
            return;
        }
        $integration = \App\Domain\Payment\Models\PaymentIntegration::query()->findOrFail($payment->payment_integration_id);
        $this->hosted($payment->order, app(PaymentGatewayRegistry::class), $integration);
    }

    private function hosted(Order $order, PaymentGatewayRegistry $registry, \App\Domain\Payment\Models\PaymentIntegration $integration): Order
    {
        $createdNow = false;
        $payment = DB::transaction(function () use ($order, $integration, &$createdNow): ?Payment {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($order->payment_status === PaymentStatus::APPROVED) {
                return null;
            }
            $this->assertPayable($order);
            $pending = $order->payments()->whereIn('gateway', ['mercado_pago', 'asaas'])->where('status', PaymentStatus::PENDING->value)->orderByDesc('id')->lockForUpdate()->first();
            if ($pending) {
                return $pending;
            }
            // Reserve under the order lock BEFORE any external operation.
            app(ManageOrderInventoryAction::class)->holdForPayment($order);
            $idempotencyKey = (string) Str::uuid();
            $createdNow = true;
            return $order->payments()->create([
                'gateway' => $integration->provider, 'environment' => $integration->environment,
                'payment_integration_id' => $integration->id, 'external_id' => 'initiating-'.$idempotencyKey,
                'idempotency_key' => $idempotencyKey, 'status' => PaymentStatus::PENDING,
                'amount_cents' => $order->total_cents, 'is_demo' => false,
                'expires_at' => $integration->provider === 'asaas'
                    ? now()->addMinutes((int) ($integration->configuration['expiration_minutes'] ?? 60))
                    : now()->addHours((int) ($integration->configuration['expiration_hours'] ?? 72)),
                'provider_status' => 'initiating',
            ]);
        }, 3);
        if ($payment === null || $payment->checkout_url) {
            return $order->refresh();
        }
        if ($payment->gateway === 'asaas' && ! $createdNow) {
            // Asaas does not guarantee idempotence for POST /checkouts. Once an
            // attempt has been dispatched, another browser click MUST NOT send
            // the creation request again. Await its result or an authenticated
            // webhook; the payment hold stays active.
            return $order->refresh();
        }
        $integration = $payment->payment_integration_id === $integration->id ? $integration : \App\Domain\Payment\Models\PaymentIntegration::findOrFail($payment->payment_integration_id);
        $gateway = $registry->gateway($integration);
        $payment->update(['last_checked_at' => now()]);
        $order = $order->fresh(['items', 'address']);
        $items = [];
        foreach ($order->items as $item) {
            $items[] = ['title' => mb_substr($item->product_name, 0, 120), 'quantity' => (int) $item->quantity,
                'unit_price' => AsaasGateway::decimal((int) $item->unit_price)];
        }
        if ($order->shipping_cents > 0) {
            $items[] = ['title' => 'Frete', 'quantity' => 1, 'unit_price' => AsaasGateway::decimal($order->shipping_cents)];
        }
        if ($order->discount_cents > 0) {
            $items = []; // Discounts require a total-only line, never a negative price.
        }
        try {
            $result = $gateway->createPayment(new PaymentRequest(
                $order->number, $order->total_cents, $payment->idempotency_key,
                $order->customer_email, $items,
                route('store.order.payment-return', ['number' => $order->number, 'key' => hash_hmac('sha256', 'payment-return:'.$order->id, $order->checkout_token)]),
                (int) ($integration->configuration['expiration_hours'] ?? 72),
                (bool) ($integration->configuration['allow_offline'] ?? false),
                $integration->provider === 'asaas' ? (int) ($integration->configuration['expiration_minutes'] ?? 60) : null,
                $integration->provider === 'asaas' ? $order->customer_name : null,
                $integration->provider === 'asaas' ? $order->customer_document : null,
                $integration->provider === 'asaas' ? $order->customer_phone : null,
                $integration->provider === 'asaas' && $order->address !== null ? [
                    'street' => $order->address->street,
                    'number' => $order->address->number,
                    'postal_code' => $order->address->postal_code,
                    'neighborhood' => $order->address->neighborhood,
                    'complement' => $order->address->complement,
                ] : null,
            ));
        } catch (ValidationException $exception) {
            // A definitive input rejection is not a remote charge. Transport/HTTP uncertainty keeps the hold.
            DB::transaction(function () use ($order, $payment): void {
                $locked = Order::query()->lockForUpdate()->findOrFail($order->id);
                Payment::query()->whereKey($payment->id)->update(['status' => PaymentStatus::FAILED]);
                $locked->update(['payment_status' => PaymentStatus::FAILED]);
                app(ManageOrderInventoryAction::class)->releasePaymentHold($locked);
            });
            throw $exception;
        } catch (\RuntimeException $exception) {
            // Timeout/5xx/uncertain gateway result: retain the payment hold and
            // the original idempotency key for server-side reconciliation.
            throw ValidationException::withMessages([
                'payment' => 'Não foi possível confirmar a abertura do pagamento agora. Aguarde alguns instantes e consulte seu pedido.',
            ]);
        }
        DB::transaction(function () use ($order, $payment, $result): void {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);
            $saved = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            if ($saved->status !== PaymentStatus::PENDING) {
                return;
            }
            $saved->update(['external_id' => $result->externalId, 'checkout_url' => $result->checkoutUrl,
                'provider_status' => $result->providerStatus, 'status_detail' => $result->statusDetail]);
        }, 3);
        return $order->refresh();
    }

    private function assertPayable(Order $order): void
    {
        if ($order->status !== OrderStatus::RECEIVED || $order->total_cents === null || $order->shipping_cents === null) {
            throw ValidationException::withMessages(['payment' => 'O pedido não está disponível para pagamento ou ainda aguarda definição do frete.']);
        }
    }
}

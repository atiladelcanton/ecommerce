<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Actions\Catalog\ManageOrderInventoryAction;
use App\Actions\Coupon\ManageCouponUsageAction;
use App\Actions\Payment\HandleApprovedPaymentAction;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Data\PaymentRequest;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;
use App\Domain\Shipping\Enums\ShippingPricingStatus;
use App\Infrastructure\Payments\AsaasGateway;
use App\Infrastructure\Payments\FakePaymentGateway;
use App\Infrastructure\Payments\PaymentGatewayRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Start a hosted checkout only after the order has an immutable final total.
 * Multiple clicks reuse the same pending remote checkout. Unknown API outcomes
 * stay blocked for financial review; NEVER repeat a potentially successful POST.
 */
final class PayOrderAction
{
    public function execute(Order $order): Order
    {
        $registry = app(PaymentGatewayRegistry::class);

        // Create the reservation and unique attempt before making any remote API call.
        $prepared = DB::transaction(function () use ($order, $registry): array {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($locked->payment_status === PaymentStatus::APPROVED) {
                return [$locked, null, null, false];
            }
            if ($locked->status !== OrderStatus::RECEIVED || $locked->total_cents === null || $locked->total_cents <= 0
                || $locked->shipping_cents === null || $locked->status === OrderStatus::CANCELLED) {
                throw ValidationException::withMessages(['payment' => 'O pedido precisa estar aberto, com frete e total definidos, para receber pagamento.']);
            }
            if ($locked->shipping_pricing_status !== null && $locked->shipping_pricing_status !== ShippingPricingStatus::CONFIRMED) {
                throw ValidationException::withMessages(['payment' => 'Aceite a cotação de entrega antes de pagar.']);
            }
            $active = $locked->payments()->where('status', PaymentStatus::PENDING->value)->orderByDesc('id')->first();
            if ($active) {
                if ($active->amount_cents !== $locked->total_cents) {
                    throw ValidationException::withMessages(['payment' => 'Existe cobrança pendente com outro valor. Solicite revisão à loja.']);
                }
                return [$locked, $active, null, false];
            }

            $integration = $registry->active();
            if (! $integration && ! $registry->fakeAllowed()) {
                throw ValidationException::withMessages(['payment' => 'O pagamento online ainda não está configurado. Entre em contato com a loja.']);
            }
            if ($integration && ($integration->provider !== 'asaas' || $integration->accessToken() === '')) {
                throw ValidationException::withMessages(['payment' => 'A integração de pagamento está incompleta. Entre em contato com a loja.']);
            }

            // Recheck the physical stock after a long freight negotiation; keep it held
            // independently of the original 24-hour cart reservation while Asaas is active.
            app(ManageOrderInventoryAction::class)->holdForPayment($locked);
            if ($locked->coupon_id !== null) {
                app(ManageCouponUsageAction::class)->reserve($locked, $locked->coupon, (int) $locked->discount_cents);
            }
            $fake = $integration === null;
            $attempt = $locked->payments()->create([
                'gateway' => $fake ? 'fake' : 'asaas',
                'external_id' => 'initiating-'.Str::uuid(),
                'status' => PaymentStatus::PENDING,
                'amount_cents' => $locked->total_cents,
                'is_demo' => $fake,
                'payment_integration_id' => $integration?->id,
                'environment' => $integration?->environment,
                'idempotency_key' => (string) Str::uuid(),
                'expires_at' => $fake ? null : now()->addMinutes((int) ($integration->configuration['expiration_minutes'] ?? 60)),
            ]);
            $locked->update(['payment_status' => PaymentStatus::PENDING]);
            return [$locked, $attempt, $integration, true];
        }, 3);

        /** @var Order $locked */
        [$locked, $attempt, $integration, $new] = $prepared;
        if (! $new || ! $attempt) {
            return $locked->refresh();
        }

        $gateway = $integration ? $registry->gateway($integration) : new FakePaymentGateway();
        $address = $locked->address()->first();
        $returnKey = hash_hmac('sha256', 'payment-return:'.$locked->id, $locked->checkout_token);
        $returnUrl = route('store.order.payment-return', ['number' => $locked->number, 'key' => $returnKey]);
        $request = new PaymentRequest(
            orderNumber: $locked->number,
            amount: (int) $locked->total_cents,
            idempotencyKey: $attempt->idempotency_key,
            buyerEmail: $locked->customer_email,
            // The priced order snapshot is authoritative, including discounts and freight.
            items: [['title' => 'Pedido '.$locked->number, 'quantity' => 1, 'unit_price' => AsaasGateway::decimal((int) $locked->total_cents)]],
            returnUrl: $returnUrl,
            expirationMinutes: $integration ? (int) ($integration->configuration['expiration_minutes'] ?? 60) : 60,
            buyerName: $locked->customer_name,
            buyerDocument: $locked->customer_document,
            buyerPhone: $locked->customer_phone,
            buyerAddress: $address?->only(['postal_code', 'street', 'number', 'complement', 'neighborhood', 'city', 'state']),
        );

        try {
            $remote = $gateway->createPayment($request);
        } catch (ValidationException $exception) {
            // A provider 4xx validation response is a definite rejection, not an
            // uncertain remote checkout. Allow a new attempt after explicit retry.
            DB::transaction(function () use ($attempt): void {
                $order = Order::query()->lockForUpdate()->findOrFail($attempt->order_id);
                $current = Payment::query()->lockForUpdate()->findOrFail($attempt->id);
                if ($current->status === PaymentStatus::PENDING && str_starts_with($current->external_id, 'initiating-')) {
                    $current->update(['status' => PaymentStatus::FAILED]);
                    $order->update(['payment_status' => PaymentStatus::FAILED]);
                    app(ManageOrderInventoryAction::class)->releasePaymentHold($order);
                    app(ManageCouponUsageAction::class)->release($order);
                }
            }, 3);
            throw $exception;
        } catch (\Throwable $exception) {
            // Timeout, 5xx or unexpected response: checkout might exist remotely.
            // Keep hold and placeholder; an operator must reconcile before retrying.
            $attempt->update(['needs_review' => true, 'review_reason' => 'Resultado inconclusivo ao criar checkout Asaas: conferir no painel antes de tentar novamente.']);
            Log::warning('Hosted checkout creation uncertain.', ['order_id' => $locked->id, 'payment_id' => $attempt->id, 'exception' => $exception::class]);
            throw ValidationException::withMessages(['payment' => 'Não foi possível confirmar a criação da cobrança. Não tente pagar novamente; entre em contato com a loja.']);
        }

        DB::transaction(function () use ($attempt, $locked, $remote): void {
            $order = Order::query()->lockForUpdate()->findOrFail($locked->id);
            $payment = Payment::query()->lockForUpdate()->findOrFail($attempt->id);
            if ($payment->status === PaymentStatus::PENDING && str_starts_with($payment->external_id, 'initiating-')) {
                $payment->update([
                    'external_id' => $remote->externalId,
                    'checkout_url' => $remote->checkoutUrl,
                    'provider_status' => $remote->providerStatus,
                    'status_detail' => $remote->statusDetail,
                ]);
            }
            if ($remote->status === PaymentStatus::APPROVED && $remote->isDemo) {
                $payment->update(['external_id' => $remote->externalId]);
                app(HandleApprovedPaymentAction::class)->execute($order, $payment);
            }
        }, 3);

        return $locked->refresh();
    }
}

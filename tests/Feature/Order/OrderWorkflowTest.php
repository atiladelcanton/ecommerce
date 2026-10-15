<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use App\Actions\Order\TransitionOrderStatusAction;
use App\Actions\Payment\HandleApprovedPaymentAction;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class OrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_payment_moves_received_order_to_paid_once(): void
    {
        $order = $this->order(OrderStatus::PENDING, PaymentStatus::PENDING);
        $payment = $order->payments()->create([
            'gateway' => 'fake',
            'external_id' => 'fake-approved-1',
            'status' => PaymentStatus::PENDING,
            'amount_cents' => $order->total_cents,
            'is_demo' => true,
        ]);

        $action = app(HandleApprovedPaymentAction::class);
        $action->execute($order, $payment);
        $action->execute($order->fresh(), $payment->fresh());

        $this->assertSame(OrderStatus::PAID, $order->fresh()->status);
        $this->assertSame(PaymentStatus::APPROVED, $order->fresh()->payment_status);
        $this->assertSame(PaymentStatus::APPROVED, $payment->fresh()->status);
        $this->assertDatabaseCount('order_status_histories', 1);
    }

    public function test_standard_shipping_requires_carrier_and_tracking_code(): void
    {
        $order = $this->order(OrderStatus::PROCESSING, PaymentStatus::APPROVED, 'standard');

        try {
            app(TransitionOrderStatusAction::class)->execute($order, OrderStatus::SHIPPED);
            self::fail('Expected validation exception.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('carrier', $exception->errors());
        }

        $updated = app(TransitionOrderStatusAction::class)->execute(
            $order,
            OrderStatus::SHIPPED,
            carrier: 'Correios',
            trackingCode: 'BR123456789',
            trackingUrl: 'https://example.test/track/BR123456789',
        );

        $this->assertSame(OrderStatus::SHIPPED, $updated->status);
        $this->assertSame('Correios', $updated->carrier);
        $this->assertSame('BR123456789', $updated->tracking_code);
        $this->assertNotNull($updated->shipped_at);
    }

    public function test_pickup_uses_ready_for_pickup_path_and_does_not_require_tracking(): void
    {
        $order = $this->order(OrderStatus::PROCESSING, PaymentStatus::APPROVED, 'pickup');
        $ready = app(TransitionOrderStatusAction::class)->execute($order, OrderStatus::READY_FOR_PICKUP);
        $delivered = app(TransitionOrderStatusAction::class)->execute($ready, OrderStatus::DELIVERED);

        $this->assertSame(OrderStatus::DELIVERED, $delivered->status);
        $this->assertNull($delivered->tracking_code);
        $this->assertNotNull($delivered->delivered_at);
    }

    public function test_invalid_transition_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        app(TransitionOrderStatusAction::class)->execute(
            $this->order(OrderStatus::PENDING, PaymentStatus::PENDING),
            OrderStatus::SHIPPED,
            carrier: 'Correios',
            trackingCode: 'X',
        );
    }

    private function order(OrderStatus $status, PaymentStatus $paymentStatus, string $shipping = 'standard'): Order
    {
        return Order::query()->create([
            'number' => 'SABA-'.Str::upper(Str::random(8)),
            'checkout_token' => (string) Str::uuid(),
            'checkout_session_hash' => hash('sha256', Str::random(40)),
            'customer_name' => 'Cliente Teste',
            'customer_email' => Str::lower(Str::random(8)).'@saba.test',
            'customer_phone' => '11999999999',
            'customer_document' => '52998224725',
            'status' => $status,
            'payment_status' => $paymentStatus,
            'subtotal_cents' => 10000,
            'shipping_cents' => $shipping === 'pickup' ? 0 : 4990,
            'discount_cents' => 0,
            'total_cents' => $shipping === 'pickup' ? 10000 : 14990,
            'shipping_option_id' => $shipping,
            'shipping_name' => $shipping === 'pickup' ? 'Retirada / combinar com a loja' : 'Entrega padrão',
            'shipping_description' => 'Teste',
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Payment;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Catalog\ManageOrderInventoryAction;
use App\Actions\Order\CreateInitialOrderAction;
use App\Actions\Order\GetCheckoutTokenAction;
use App\Actions\Payment\ReconcileHostedPaymentAction;
use App\Domain\Catalog\Models\InventoryReservation;
use App\Domain\Catalog\Models\Product;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Data\PaymentRequest;
use App\Domain\Payment\Models\PaymentIntegration;
use App\Domain\Shipping\Models\ShippingIntegration;
use App\Infrastructure\Payments\MercadoPagoGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class MercadoPagoCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Legacy adapter regression tests only. The real storefront never exposes Mercado Pago.
        config()->set('payments.allow_legacy_test_driver', true);
    }

    private const REMOTE_ID = 'ORDTST01KS5AJ6HTK2HRQ3XJ3C2JCKP9';

    private function configure(): PaymentIntegration
    {
        ShippingIntegration::forProvider('local_pickup')->update(['is_enabled' => true, 'configuration' => ['address' => 'SABA']]);
        return PaymentIntegration::query()->create([
            'provider' => 'mercado_pago', 'environment' => 'sandbox', 'is_enabled' => true,
            'credentials' => ['access_token' => 'TEST-DO-NOT-USE', 'webhook_secret' => 'webhook-secret'],
            'configuration' => ['expiration_hours' => 72, 'allow_offline' => false],
        ]);
    }

    private function createCheckout(int $quantity = 2): array
    {
        $product = Product::factory()->create(['price' => 10000, 'stock' => 3]);
        app(AddCartItemAction::class)->execute($product->id, quantity: $quantity);
        Http::fake(['api.mercadopago.com/v1/orders' => Http::response([
            'id' => self::REMOTE_ID, 'checkout_url' => 'https://www.mercadopago.com.br/checkout/v1/redirect?order_id='.self::REMOTE_ID,
            'status' => 'created', 'status_detail' => 'created',
        ], 201)]);
        $order = app(CreateInitialOrderAction::class)->execute([
            'name' => 'Cliente', 'email' => 'cliente@example.test', 'phone' => '11999999999',
            'document' => '52998224725', 'postal_code' => '13077001', 'street' => 'Rua teste',
            'number' => '10', 'neighborhood' => 'Centro', 'city' => 'Campinas', 'state' => 'SP',
            'shipping_option' => 'local_pickup:pickup',
        ], app(GetCheckoutTokenAction::class)->execute());
        return [$product, $order, $order->payments()->sole()];
    }

    public function test_pending_charge_keeps_stock_even_after_old_24_hour_reservation_expires(): void
    {
        $this->configure();
        [$product, $order, $payment] = $this->createCheckout();
        $this->assertSame(PaymentStatus::PENDING, $order->fresh()->payment_status);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertSame(self::REMOTE_ID, $payment->external_id);
        $this->assertFalse($payment->is_demo);
        $reservation = InventoryReservation::query()->sole();
        $this->assertTrue($reservation->payment_hold);
        $reservation->update(['expires_at' => now()->subDay()]);
        $this->assertSame(0, app(ManageOrderInventoryAction::class)->expire());
        $this->assertNull($reservation->fresh()->released_at);
        $request = Http::recorded()->first()[0];
        $this->assertSame('P3D', $request['expiration_time']);
        $this->assertSame([
            'title' => $order->items()->firstOrFail()->product_name,
            'quantity' => 2,
            'unit_price' => '100.00',
        ], $request['items'][0]);
        $this->assertArrayNotHasKey('unit_measure', $request['items'][0]);
        $this->assertArrayNotHasKey('total_amount', $request['items'][0]);
        $this->assertSame(['ticket'], $request['config']['payment_method']['not_allowed_types']);
        $this->assertSame('200.00', $request['total_amount']);
        $this->assertSame('https://www.mercadopago.com.br/checkout/v1/redirect?order_id='.self::REMOTE_ID, $payment->checkout_url);
    }

    public function test_provider_confirmed_rejection_releases_hold_only_after_remote_check(): void
    {
        $this->configure();
        [$product, $order, $payment] = $this->createCheckout();
        Http::fake(['api.mercadopago.com/v1/orders/'.self::REMOTE_ID => Http::response([
            'id' => self::REMOTE_ID, 'status' => 'failed', 'status_detail' => 'rejected_by_issuer',
            'external_reference' => $order->number, 'currency' => 'BRL', 'total_amount' => '200.00',
        ])]);
        app(ReconcileHostedPaymentAction::class)->execute($payment);
        $this->assertSame(PaymentStatus::FAILED, $order->fresh()->payment_status);
        $this->assertNotNull(InventoryReservation::query()->sole()->released_at);
        $this->assertFalse(InventoryReservation::query()->sole()->payment_hold);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_approval_consumes_inventory_once_and_is_idempotent(): void
    {
        $this->configure();
        [$product, $order, $payment] = $this->createCheckout();
        Http::fake(['api.mercadopago.com/v1/orders/'.self::REMOTE_ID => Http::response([
            'id' => self::REMOTE_ID, 'status' => 'processed', 'status_detail' => 'accredited',
            'external_reference' => $order->number, 'currency' => 'BRL', 'total_amount' => '200.00',
        ])]);
        app(ReconcileHostedPaymentAction::class)->execute($payment);
        app(ReconcileHostedPaymentAction::class)->execute($payment);
        $this->assertSame(1, $product->fresh()->stock);
        $this->assertSame(PaymentStatus::APPROVED, $order->fresh()->payment_status);
        $this->assertSame('paid', $order->fresh()->status->value);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('inventory_reservations', 1);
        $this->assertNotNull(InventoryReservation::query()->sole()->consumed_at);
    }

    public function test_unsigned_webhook_is_rejected_and_never_changes_payment(): void
    {
        $this->configure();
        $this->postJson('/webhooks/payments/mercado-pago?data.id='.self::REMOTE_ID.'&type=order', [
            'id' => 'evt-1', 'type' => 'order', 'data' => ['id' => self::REMOTE_ID],
        ])->assertStatus(401);
        $this->assertDatabaseCount('payment_events', 0);
    }

    public function test_correctly_signed_webhook_is_deduplicated(): void
    {
        $this->configure();
        [$product, $order, $payment] = $this->createCheckout();
        Http::fake(['api.mercadopago.com/v1/orders/'.self::REMOTE_ID => Http::response([
            'id' => self::REMOTE_ID, 'status' => 'created', 'status_detail' => 'created',
            'external_reference' => $order->number, 'currency' => 'BRL', 'total_amount' => '200.00',
        ])]);
        $ts = (string) (time() * 1000);
        $id = strtolower(self::REMOTE_ID);
        $requestId = 'test-request-123';
        $hash = hash_hmac('sha256', "id:{$id};request-id:{$requestId};ts:{$ts};", 'webhook-secret');
        $url = '/webhooks/payments/mercado-pago?data.id='.self::REMOTE_ID.'&type=order';
        $payload = ['id' => 123456, 'type' => 'order', 'live_mode' => false, 'data' => ['id' => self::REMOTE_ID]];
        $headers = ['x-signature' => 'ts='.$ts.',v1='.$hash, 'x-request-id' => $requestId];
        $this->postJson($url, $payload, $headers)->assertOk();
        $this->postJson($url, $payload, $headers)->assertOk();
        $this->assertDatabaseCount('payment_events', 1);
        $this->assertNotNull(\App\Domain\Payment\Models\PaymentEvent::query()->sole()->processed_at);
    }

    public function test_provider_400_shows_only_public_message_and_logs_sanitized_diagnostics(): void
    {
        $integration = $this->configure();
        Log::spy();
        Http::fake(['api.mercadopago.com/v1/orders' => Http::response([
            'error' => 'property_value',
            'message' => 'Invalid config.online.success_url: https://example.com/secret@example.com',
            'cause' => [
                ['code' => 'property_value', 'field' => 'config.online.success_url', 'description' => 'Invalid APP_USR-VERY_PRIVATE_ACCESS_TOKEN'],
            ],
        ], 400, ['x-request-id' => 'mp-request-123'])]);

        $gateway = new MercadoPagoGateway($integration);
        try {
            $gateway->createPayment(new PaymentRequest(
                'SABA-000001', 10000, 'c17c0d34-06e4-422f-aa2f-d2d332a5e61a',
                'buyer@example.com', [], 'https://shop.example.com/payment-return', 72, false,
            ));
            $this->fail('Expected provider rejection');
        } catch (ValidationException $exception) {
            $message = $exception->errors()['payment'][0];
            $this->assertSame('Não foi possível iniciar seu pagamento. Tente novamente ou entre em contato com a loja.', $message);
            $this->assertStringNotContainsString('Mercado Pago', $message);
            $this->assertStringNotContainsString('400', $message);
        }

        Log::shouldHaveReceived('warning')->withArgs(function (string $message, array $context): bool {
            $encoded = json_encode($context);
            $this->assertSame('Payment provider rejected checkout initialization.', $message);
            $this->assertSame(400, $context['http_status']);
            $this->assertSame('property_value', $context['error_code']);
            $this->assertSame('mp-request-123', $context['request_id']);
            $this->assertSame('config.online.success_url', $context['details'][0]['field']);
            $this->assertStringNotContainsString('APP_USR-', $encoded);
            $this->assertStringNotContainsString('secret@example.com', $encoded);
            $this->assertStringNotContainsString('buyer@example.com', $encoded);
            return true;
        })->once();
    }

    public function test_offline_checkout_does_not_send_an_empty_payment_method_array(): void
    {
        $integration = $this->configure();
        Http::fake(['api.mercadopago.com/v1/orders' => Http::response([
            'id' => self::REMOTE_ID,
            'checkout_url' => 'https://www.mercadopago.com.br/checkout/v1/redirect?order_id='.self::REMOTE_ID,
        ], 201)]);
        (new MercadoPagoGateway($integration))->createPayment(new PaymentRequest(
            'SABA-000002', 10000, 'c17c0d34-06e4-422f-aa2f-d2d332a5e61a',
            'buyer@example.com', [], 'https://shop.example.com/payment-return', 72, true,
        ));
        $request = Http::recorded()->first()[0];
        $this->assertSame('P3D', $request['config']['default_payment_due_date']);
        $this->assertArrayNotHasKey('payment_method', $request['config']);
    }

    public function test_money_conversion_is_exact(): void
    {
        $this->assertSame('1299.90', MercadoPagoGateway::decimal(129990));
        $this->assertSame(129990, MercadoPagoGateway::cents('1299.90'));
        $this->assertNull(MercadoPagoGateway::cents('1299.901'));
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Payment;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Catalog\ManageOrderInventoryAction;
use App\Actions\Order\CreateInitialOrderAction;
use App\Actions\Order\GetCheckoutTokenAction;
use App\Domain\Catalog\Models\InventoryReservation;
use App\Domain\Catalog\Models\Product;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;
use App\Domain\Payment\Models\PaymentEvent;
use App\Domain\Payment\Models\PaymentIntegration;
use App\Domain\Shipping\Models\ShippingIntegration;
use App\Infrastructure\Payments\AsaasGateway;
use App\Domain\Payment\Data\PaymentRequest;
use Illuminate\Validation\ValidationException;
use App\Infrastructure\Payments\PaymentGatewayRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class AsaasCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private const CHECKOUT_ID = 'c7b1c696-b27b-4d3d-80b9-d1c018e387f8';

    private const TOKEN = 'asaas-webhook-test-secret-more-than-32-characters';

    private const CHECKOUT_LINK = 'https://sandbox.asaas.com/000/checkoutSession/show/'.self::CHECKOUT_ID;

    private function configure(): PaymentIntegration
    {
        ShippingIntegration::forProvider('local_pickup')->update(['is_enabled' => true, 'configuration' => ['address' => 'SABA']]);
        return PaymentIntegration::query()->create([
            'provider' => 'asaas', 'environment' => 'sandbox', 'is_enabled' => true,
            'credentials' => ['access_token' => '$aact_hmlg_FAKE', 'webhook_secret' => self::TOKEN],
            'configuration' => ['expiration_minutes' => 60, 'pix' => true, 'credit_card' => true, 'max_installments' => 6],
        ]);
    }

    private function createCheckout(): array
    {
        $product = Product::factory()->create(['price' => 10000, 'stock' => 5]);
        app(AddCartItemAction::class)->execute($product->id, quantity: 2);
        Http::fake(['api-sandbox.asaas.com/v3/checkouts' => Http::response(['id' => self::CHECKOUT_ID, 'link' => self::CHECKOUT_LINK], 200)]);
        $order = app(CreateInitialOrderAction::class)->execute([
            'name' => 'Cliente Teste', 'email' => 'cliente@example.test', 'phone' => '11999999999',
            'document' => '52998224725', 'postal_code' => '13077001', 'street' => 'Rua teste',
            'number' => '10', 'neighborhood' => 'Centro', 'city' => 'Campinas', 'state' => 'SP',
            'shipping_option' => 'local_pickup:pickup',
        ], app(GetCheckoutTokenAction::class)->execute());
        return [$product, $order, $order->payments()->sole()];
    }

    private function notify(string $event, string $status, string $externalId = self::CHECKOUT_ID): \Illuminate\Testing\TestResponse
    {
        $order = Payment::query()->where('gateway', 'asaas')->firstOrFail()->order;
        $payload = [
            'id' => 'evt-'.strtolower($event).'-'.substr($externalId, 0, 6),
            'event' => $event,
            'checkout' => [
                'id' => $externalId, 'status' => $status, 'externalReference' => $order->number,
                'items' => [['name' => 'Produto', 'quantity' => 2, 'value' => 100.00]],
            ],
        ];
        return $this->postJson('/webhooks/payments/asaas', $payload, ['asaas-access-token' => self::TOKEN]);
    }

    public function test_asaas_is_selected_and_legacy_mercado_pago_is_hidden_even_if_db_was_enabled(): void
    {
        $legacy = PaymentIntegration::query()->create([
            'provider' => 'mercado_pago', 'environment' => 'sandbox', 'is_enabled' => true,
            'credentials' => ['access_token' => 'FAKE', 'webhook_secret' => 'FAKE'],
        ]);
        $this->assertNull(app(PaymentGatewayRegistry::class)->active());
        $this->configure();
        $this->assertSame('asaas', app(PaymentGatewayRegistry::class)->active()->provider);
        $legacy->refresh();
        $this->assertTrue($legacy->is_enabled); // Migration disables this on existing installations.
    }

    public function test_new_checkout_redirect_and_stock_hold_is_not_affected_by_local_expiration(): void
    {
        $this->configure();
        [$product, $order, $payment] = $this->createCheckout();
        $this->assertSame('asaas', $payment->gateway);
        $this->assertSame(self::CHECKOUT_ID, $payment->external_id);
        $this->assertSame(self::CHECKOUT_LINK, $payment->checkout_url);
        $this->assertTrue(AsaasGateway::isCheckoutUrl($payment->checkout_url, 'sandbox'));
        $this->assertSame(PaymentStatus::PENDING, $order->fresh()->payment_status);
        $this->assertSame(5, $product->fresh()->stock);
        $reservation = InventoryReservation::query()->sole();
        $this->assertTrue($reservation->payment_hold);
        $reservation->update(['expires_at' => now()->subDay()]);
        $this->assertSame(0, app(ManageOrderInventoryAction::class)->expire());
        $post = Http::recorded()->first()[0];
        $data = $post->data();
        $this->assertSame(['PIX', 'CREDIT_CARD'], $data['billingTypes']);
        $this->assertSame(['DETACHED', 'INSTALLMENT'], $data['chargeTypes']);
        $this->assertSame(['maxInstallmentCount' => 6], $data['installment']);
        $this->assertSame(60, $data['minutesToExpire']);
        $this->assertSame($order->number, $data['externalReference']);
        $this->assertSame(100, $data['items'][0]['value']);
        $this->assertSame(2, $data['items'][0]['quantity']);
        $this->assertSame('Rua teste', $data['customerData']['address']);
        $this->assertSame('10', $data['customerData']['addressNumber']);
        $this->assertSame('13077001', $data['customerData']['postalCode']);
        $this->assertSame('Centro', $data['customerData']['province']);
        $this->assertSame('Cliente Teste', $data['customerData']['name']);
        $this->assertArrayNotHasKey('city', $data['customerData']); // City needs an IBGE code, not 'Campinas'.
        $this->assertStringContainsString('"value":100.00', $post->body());
    }

    public function test_gateway_rejects_missing_order_address_without_contacting_asaas(): void
    {
        $integration = $this->configure();
        Http::fake();

        try {
            (new AsaasGateway($integration))->createPayment(new PaymentRequest(
                'SABA-009999', 1000, 'unique-key', 'buyer@example.test',
                [['title' => 'Produto', 'quantity' => 1, 'unit_price' => '10.00']],
                'https://example.test/retorno', 72, false, 60,
                'Cliente Teste', '52998224725', '11999999999',
                ['street' => '', 'number' => '10', 'postal_code' => '13077001', 'neighborhood' => 'Centro'],
            ));
            $this->fail('Esperava erro de validação antes de enviar a cobrança.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('payment', $exception->errors());
        }

        Http::assertNothingSent();
    }

    public function test_gateway_sends_optional_complement_and_preserves_address_number_zero(): void
    {
        $integration = $this->configure();
        Http::fake(['api-sandbox.asaas.com/v3/checkouts' => Http::response(['id' => self::CHECKOUT_ID, 'link' => self::CHECKOUT_LINK], 200)]);

        (new AsaasGateway($integration))->createPayment(new PaymentRequest(
            'SABA-009998', 1000, 'unique-key', 'buyer@example.test',
            [['title' => 'Produto', 'quantity' => 1, 'unit_price' => '10.00']],
            'https://example.test/retorno', 72, false, 60,
            'Cliente Teste', '52998224725', '11999999999',
            ['street' => 'Av. Paulista', 'number' => '0', 'postal_code' => '01310-000',
                'neighborhood' => 'Bela Vista', 'complement' => 'Sala 201'],
        ));

        Http::assertSent(function ($request): bool {
            $data = $request->data();
            return $data['customerData']['address'] === 'Av. Paulista'
                && $data['customerData']['addressNumber'] === '0'
                && $data['customerData']['postalCode'] === '01310000'
                && $data['customerData']['province'] === 'Bela Vista'
                && $data['customerData']['complement'] === 'Sala 201';
        });
    }

    public function test_checkout_url_rejects_wrong_environment_or_untrusted_domains(): void
    {
        $this->assertTrue(AsaasGateway::isCheckoutUrl(self::CHECKOUT_LINK, 'sandbox'));
        $this->assertTrue(AsaasGateway::isCheckoutUrl('https://www.asaas.com/000/checkoutSession/show/'.self::CHECKOUT_ID, 'production'));
        $this->assertFalse(AsaasGateway::isCheckoutUrl(self::CHECKOUT_LINK, 'production'));
        $this->assertFalse(AsaasGateway::isCheckoutUrl('https://asaas.com/checkoutSession/show?id='.self::CHECKOUT_ID, 'sandbox'));
        $this->assertFalse(AsaasGateway::isCheckoutUrl('https://sandbox.asaas.com.evil.example/path', 'sandbox'));
        $this->assertFalse(AsaasGateway::isCheckoutUrl('http://sandbox.asaas.com/path', 'sandbox'));
        $this->assertFalse(AsaasGateway::isCheckoutUrl('https://sandbox.asaas.com:8443/path', 'sandbox'));
    }

    public function test_missing_checkout_link_is_not_replaced_with_an_invented_url(): void
    {
        $integration = $this->configure();
        Http::fake(['api-sandbox.asaas.com/v3/checkouts' => Http::response(['id' => self::CHECKOUT_ID], 200)]);
        $this->expectException(\RuntimeException::class);
        (new AsaasGateway($integration))->createPayment(new PaymentRequest(
            'SABA-009997', 1000, 'unique-key', 'buyer@example.test',
            [['title' => 'Produto', 'quantity' => 1, 'unit_price' => '10.00']],
            'https://example.test/retorno', 72, false, 60,
            'Cliente Teste', '52998224725', '11999999999',
            ['street' => 'Rua teste', 'number' => '10', 'postal_code' => '13077001', 'neighborhood' => 'Centro'],
        ));
    }

    public function test_webhook_received_before_create_response_does_not_invent_a_checkout_url(): void
    {
        $this->configure();
        [$product, $order, $payment] = $this->createCheckout();
        // Mimic the earlier in-flight state to exercise webhook recovery.
        $payment->update(['external_id' => 'initiating-old-unique-reference', 'checkout_url' => null]);
        $this->notify('CHECKOUT_CREATED', 'ACTIVE')->assertOk();
        $this->assertSame(self::CHECKOUT_ID, $payment->fresh()->external_id);
        $this->assertNull($payment->fresh()->checkout_url);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_invalid_or_missing_webhook_token_does_not_change_payment(): void
    {
        $this->configure();
        $this->createCheckout();
        $this->postJson('/webhooks/payments/asaas', [
            'id' => 'evt-unauthenticated', 'event' => 'CHECKOUT_PAID', 'checkout' => ['id' => self::CHECKOUT_ID],
        ])->assertUnauthorized();
        $this->assertDatabaseCount('payment_events', 0);
    }

    public function test_approved_webhook_consumes_reservation_once_even_when_replayed(): void
    {
        $this->configure();
        [$product, $order] = $this->createCheckout();
        $this->notify('CHECKOUT_PAID', 'PAID')->assertOk();
        $this->notify('CHECKOUT_PAID', 'PAID')->assertOk();
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertSame(PaymentStatus::APPROVED, $order->fresh()->payment_status);
        $this->assertSame('paid', $order->fresh()->status->value);
        $this->assertDatabaseCount('payment_events', 1);
        $this->assertNotNull(PaymentEvent::query()->sole()->processed_at);
        $this->assertNotNull(InventoryReservation::query()->sole()->consumed_at);
        $this->notify('CHECKOUT_EXPIRED', 'EXPIRED')->assertOk();
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertSame(PaymentStatus::APPROVED, $order->fresh()->payment_status);
    }

    public function test_expiration_releases_hold_and_allows_new_checkout(): void
    {
        $this->configure();
        [$product, $order, $payment] = $this->createCheckout();
        $this->notify('CHECKOUT_EXPIRED', 'EXPIRED')->assertOk();
        $this->assertSame(PaymentStatus::CANCELLED, $order->fresh()->payment_status);
        $this->assertFalse(InventoryReservation::query()->sole()->payment_hold);
        $this->assertNotNull(InventoryReservation::query()->sole()->released_at);
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertNull($payment->fresh()->checkout_url);
    }

    public function test_mismatched_amount_is_not_approved_and_flags_manual_review(): void
    {
        $this->configure();
        [$product, $order] = $this->createCheckout();
        $payload = [
            'id' => 'evt-mismatch-01', 'event' => 'CHECKOUT_PAID',
            'checkout' => ['id' => self::CHECKOUT_ID, 'status' => 'PAID',
                'externalReference' => $order->number, 'items' => [['quantity' => 2, 'value' => 1.00]]],
        ];
        $this->postJson('/webhooks/payments/asaas', $payload, ['asaas-access-token' => self::TOKEN])->assertOk();
        $this->assertSame(PaymentStatus::PENDING, $order->fresh()->payment_status);
        $this->assertTrue(Payment::query()->sole()->needs_review);
        $this->assertTrue(InventoryReservation::query()->sole()->payment_hold);
        $this->assertSame(5, $product->fresh()->stock);
    }
}

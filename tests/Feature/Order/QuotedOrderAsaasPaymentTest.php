<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Order\ConfirmQuotedOrderAction;
use App\Actions\Order\CreateInitialOrderAction;
use App\Actions\Order\GetCheckoutTokenAction;
use App\Actions\Order\TransitionOrderStatusAction;
use App\Actions\Shipping\GetOrderShippingQuoteLinkAction;
use App\Actions\Shipping\PublishOrderShippingQuoteAction;
use App\Domain\Catalog\Models\InventoryReservation;
use App\Domain\Catalog\Models\Product;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\PaymentIntegration;
use App\Domain\Shipping\Enums\ShippingPricingStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class QuotedOrderAsaasPaymentTest extends TestCase
{
    use RefreshDatabase;

    private const ID = 'c7b1c696-b27b-4d3d-80b9-d1c018e387f8';
    private const LINK = 'https://sandbox.asaas.com/000/checkoutSession/show/'.self::ID;
    private const SECRET = 'asaas-quote-webhook-secret-at-least-32-characters';

    private function order(): Order
    {
        $product = Product::factory()->create(['price' => 200000, 'stock' => 5]);
        app(AddCartItemAction::class)->execute($product->id);
        $order = app(CreateInitialOrderAction::class)->requestShippingQuote([
            'name' => 'Cliente', 'email' => 'cliente@example.test', 'phone' => '11999999999',
            'document' => '52998224725', 'postal_code' => '13077001', 'street' => 'Rua teste',
            'number' => '10', 'neighborhood' => 'Centro', 'city' => 'Campinas', 'state' => 'SP',
        ], app(GetCheckoutTokenAction::class)->execute());
        app(PublishOrderShippingQuoteAction::class)->execute($order, [
            'carrier_name' => 'Transportadora X', 'service_name' => 'Rodoviário', 'price_cents' => 28000,
            'estimated_days' => 7, 'expires_at' => now()->addDays(7),
        ], User::factory()->create()->id);
        return $order->refresh();
    }

    private function configure(): void
    {
        PaymentIntegration::query()->create([
            'provider' => 'asaas', 'environment' => 'sandbox', 'is_enabled' => true,
            'credentials' => ['access_token' => '$aact_hmlg_FAKE', 'webhook_secret' => self::SECRET],
            'configuration' => ['expiration_minutes' => 60, 'pix' => true, 'credit_card' => true, 'max_installments' => 1],
        ]);
        Http::fake(['api-sandbox.asaas.com/v3/checkouts' => Http::response(['id' => self::ID, 'link' => self::LINK], 200)]);
    }

    public function test_quote_acceptance_starts_one_real_pending_checkout_then_provider_approves(): void
    {
        $this->configure();
        $order = $this->order();
        $link = app(GetOrderShippingQuoteLinkAction::class)->execute($order->latestShippingQuote);

        $this->get($link)->assertOk();
        $this->get(route('store.order.payment-status', $order->number))->assertOk();
        $result = app(ConfirmQuotedOrderAction::class)->execute($order->number, 1, $link);
        app(ConfirmQuotedOrderAction::class)->execute($order->number, 1, $link);
        $this->assertSame(ShippingPricingStatus::CONFIRMED, $result->shipping_pricing_status);
        $this->assertSame(228000, $order->fresh()->total_cents);
        $this->assertSame(PaymentStatus::PENDING, $order->fresh()->payment_status);
        $this->assertSame(OrderStatus::RECEIVED, $order->fresh()->status);
        $this->assertSame(5, Product::query()->sole()->stock);
        $this->assertDatabaseCount('payments', 1);
        $this->assertSame(self::LINK, $order->payments()->sole()->checkout_url);
        $this->assertTrue(InventoryReservation::query()->sole()->payment_hold);
        Http::assertSentCount(1);

        // Local 24-hour expiration must never release a remote checkout's stock hold.
        InventoryReservation::query()->sole()->update(['expires_at' => now()->subDays(2)]);
        $this->assertSame(0, app(\App\Actions\Catalog\ManageOrderInventoryAction::class)->expire());

        $this->postJson('/webhooks/payments/asaas', [
            'id' => 'evt-quote-payment-approved-01',
            'event' => 'CHECKOUT_PAID',
            'checkout' => ['id' => self::ID, 'status' => 'PAID', 'externalReference' => $order->number,
                'items' => [['name' => 'Pedido '.$order->number, 'quantity' => 1, 'value' => 2280.00]]],
        ], ['asaas-access-token' => self::SECRET])->assertOk();

        $this->assertSame(PaymentStatus::APPROVED, $order->fresh()->payment_status);
        $this->assertSame(OrderStatus::PAID, $order->fresh()->status);
        $this->assertSame(4, Product::query()->sole()->stock);
        $this->assertNotNull(InventoryReservation::query()->sole()->consumed_at);
    }

    public function test_terminal_expired_webhook_can_release_stock_even_without_items(): void
    {
        $this->configure();
        $order = $this->order();
        $link = app(GetOrderShippingQuoteLinkAction::class)->execute($order->latestShippingQuote);
        app(ConfirmQuotedOrderAction::class)->execute($order->number, 1, $link);
        $this->assertTrue(InventoryReservation::query()->sole()->payment_hold);

        $this->postJson('/webhooks/payments/asaas', [
            'id' => 'evt-quote-checkout-expired-01',
            'event' => 'CHECKOUT_EXPIRED',
            'checkout' => ['id' => self::ID, 'status' => 'EXPIRED', 'externalReference' => $order->number],
        ], ['asaas-access-token' => self::SECRET])->assertOk();

        $this->assertSame(PaymentStatus::CANCELLED, $order->fresh()->payment_status);
        $this->assertNotNull(InventoryReservation::query()->sole()->released_at);
        $this->assertFalse(InventoryReservation::query()->sole()->payment_hold);
        $this->assertSame(5, Product::query()->sole()->stock);
    }

    public function test_no_repricing_or_order_cancellation_during_pending_asaas_checkout(): void
    {
        $this->configure();
        $order = $this->order();
        $link = app(GetOrderShippingQuoteLinkAction::class)->execute($order->latestShippingQuote);
        app(ConfirmQuotedOrderAction::class)->execute($order->number, 1, $link);

        try {
            app(PublishOrderShippingQuoteAction::class)->execute($order, [
                'carrier_name' => 'Alterada', 'service_name' => 'Nova', 'price_cents' => 999,
                'expires_at' => now()->addDay(),
            ], User::factory()->create()->id);
            self::fail('An active checkout must block repricing.');
        } catch (ValidationException) {
        }
        $this->expectException(ValidationException::class);
        app(TransitionOrderStatusAction::class)->execute($order, OrderStatus::CANCELLED);
    }
}

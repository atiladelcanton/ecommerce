<?php

declare(strict_types=1);

namespace Tests\Feature\Shipping;

use App\Actions\Order\TransitionOrderStatusAction;
use App\Actions\Shipping\CancelShipmentAction;
use App\Actions\Shipping\RefreshShipmentTrackingAction;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Shipping\Data\ShippingItem;
use App\Domain\Shipping\Data\ShippingQuoteRequest;
use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Domain\Shipping\Models\Shipment;
use App\Domain\Shipping\Models\ShippingCarrier;
use App\Domain\Shipping\Models\ShippingIntegration;
use App\Infrastructure\Shipping\MelhorEnvio\MelhorEnvioClient;
use App\Infrastructure\Shipping\MelhorEnvio\MelhorEnvioShippingProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ShippingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_oauth_token_exchange_sends_required_user_agent(): void
    {
        $integration = ShippingIntegration::query()->create([
            'provider' => 'melhor_envio',
            'is_enabled' => true,
            'environment' => 'sandbox',
            'configuration' => ['application_name' => 'SABA E-commerce', 'technical_email' => 'dev@saba.test'],
            'credentials' => ['client_id' => '123', 'client_secret' => 'secret'],
        ]);

        Http::fake([
            'https://sandbox.melhorenvio.com.br/oauth/token' => Http::response([
                'access_token' => 'access',
                'refresh_token' => 'refresh',
                'expires_in' => 2592000,
            ]),
        ]);

        $tokens = app(MelhorEnvioClient::class)
            ->exchangeAuthorizationCode($integration, 'authorization-code', 'https://shop.test/admin/shipping/melhor-envio/callback');

        self::assertSame('access', $tokens['access_token']);
        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://sandbox.melhorenvio.com.br/oauth/token'
                && $request->hasHeader('User-Agent', 'SABA E-commerce (dev@saba.test)')
                && $request['grant_type'] === 'authorization_code'
                && $request['client_id'] === '123'
                && $request['client_secret'] === 'secret'
                && $request['redirect_uri'] === 'https://shop.test/admin/shipping/melhor-envio/callback';
        });
    }

    public function test_melhor_envio_quote_is_normalized(): void
    {
        ShippingIntegration::query()->create([
            'provider' => 'melhor_envio',
            'is_enabled' => true,
            'environment' => 'sandbox',
            'configuration' => ['application_name' => 'SABA', 'technical_email' => 'dev@saba.test'],
            'credentials' => ['client_id' => 'id', 'client_secret' => 'secret'],
            'access_token' => 'token',
        ]);

        $carrier = ShippingCarrier::query()->create(['shipping_integration_id' => ShippingIntegration::query()->where('provider', 'melhor_envio')->value('id'), 'environment' => 'sandbox', 'external_id' => '1', 'name' => 'Correios', 'is_enabled' => true]);
        $carrier->services()->create(['external_id' => '2', 'name' => 'SEDEX', 'is_enabled' => true]);
        Http::fake([
            'https://sandbox.melhorenvio.com.br/api/v2/me/shipment/calculate' => Http::response([[
                'id' => 2,
                'name' => 'SEDEX',
                'price' => '82.40',
                'custom_price' => '79.90',
                'delivery_time' => 3,
                'custom_delivery_time' => 2,
                'company' => ['id' => 1, 'name' => 'Correios'],
                'packages' => [[
                    'dimensions' => ['height' => 10, 'width' => 20, 'length' => 30],
                    'weight' => '1.20',
                ]],
            ]], 200),
        ]);

        $options = app(MelhorEnvioShippingProvider::class)->quote(new ShippingQuoteRequest(
            destinationPostalCode: '80000000',
            items: [new ShippingItem(1, 1, '1.20', '10', '20', '30', 129900, 'Produto')],
            originPostalCode: '01001000',
        ));

        self::assertCount(1, $options);
        self::assertSame('melhor_envio:2', $options[0]->id);
        self::assertSame(7990, $options[0]->price);
        self::assertSame('Correios', $options[0]->carrierName);
        self::assertSame(2, $options[0]->estimatedDays);
    }

    public function test_ready_for_shipping_is_valid_operational_status(): void
    {
        $order = $this->order(OrderStatus::PROCESSING);

        $updated = app(TransitionOrderStatusAction::class)->execute($order, OrderStatus::READY_FOR_SHIPPING);

        self::assertSame(OrderStatus::READY_FOR_SHIPPING, $updated->status);
        self::assertNotNull($updated->ready_for_shipping_at);
    }

    public function test_valid_melhor_envio_webhook_moves_order_to_shipped_and_is_idempotent(): void
    {
        $secret = 'webhook-secret';
        ShippingIntegration::query()->create([
            'provider' => 'melhor_envio',
            'is_enabled' => true,
            'environment' => 'sandbox',
            'configuration' => [],
            'credentials' => ['client_id' => 'id', 'client_secret' => $secret],
        ]);
        $order = $this->order(OrderStatus::READY_FOR_SHIPPING);
        $shipment = Shipment::query()->create([
            'order_id' => $order->id,
            'provider' => 'melhor_envio',
            'external_id' => 'shipment-123',
            'carrier_name' => 'Correios',
            'service_external_id' => '2',
            'service_name' => 'SEDEX',
            'status' => ShipmentStatus::LABEL_GENERATED,
            'customer_price_cents' => 4990,
        ]);

        $payload = json_encode([
            'event' => 'order.posted',
            'data' => [
                'id' => 'shipment-123',
                'status' => 'posted',
                'tracking' => 'BR123456789',
                'tracking_url' => 'https://example.test/track',
                'posted_at' => '2026-10-12T12:00:00+00:00',
            ],
        ], JSON_THROW_ON_ERROR);
        $signature = base64_encode(hash_hmac('sha256', $payload, $secret, true));

        for ($i = 0; $i < 2; $i++) {
            $this->call('POST', route('webhooks.shipping.melhor-envio'), [], [], [], [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_ME_SIGNATURE' => $signature,
            ], $payload)->assertOk();
        }

        self::assertSame(OrderStatus::SHIPPED, $order->fresh()->status);
        self::assertSame('BR123456789', $order->fresh()->tracking_code);
        self::assertSame(ShipmentStatus::POSTED, $shipment->fresh()->status);
        $this->assertDatabaseCount('shipment_events', 1);
        self::assertSame(1, $order->statusHistories()->where('to_status', OrderStatus::SHIPPED->value)->count());
    }

    public function test_generated_label_can_be_cancelled_through_provider(): void
    {
        $this->melhorEnvioIntegration();
        $order = $this->order(OrderStatus::READY_FOR_SHIPPING);
        $shipment = $this->shipment($order, ShipmentStatus::LABEL_GENERATED);

        Http::fake([
            'https://sandbox.melhorenvio.com.br/api/v2/me/shipment/cancellable' => Http::response([
                $shipment->external_id => ['cancellable' => true],
            ]),
            'https://sandbox.melhorenvio.com.br/api/v2/me/shipment/cancel' => Http::response(['success' => true]),
        ]);

        $updated = app(CancelShipmentAction::class)->execute($shipment, 'Pedido cancelado pelo cliente');

        self::assertSame(ShipmentStatus::CANCELLED, $updated->status);
        self::assertSame(OrderStatus::READY_TO_SHIP, $order->fresh()->status);
        self::assertNotNull($updated->cancelled_at);
    }

    public function test_tracking_reconciliation_can_move_order_to_delivered(): void
    {
        $this->melhorEnvioIntegration();
        $order = $this->order(OrderStatus::SHIPPED);
        $shipment = $this->shipment($order, ShipmentStatus::POSTED);

        Http::fake([
            'https://sandbox.melhorenvio.com.br/api/v2/me/shipment/tracking' => Http::response([
                $shipment->external_id => [
                    'status' => 'delivered',
                    'tracking' => 'BR987654321',
                    'tracking_url' => 'https://example.test/track/BR987654321',
                ],
            ]),
        ]);

        $updated = app(RefreshShipmentTrackingAction::class)->execute($shipment);

        self::assertSame(ShipmentStatus::DELIVERED, $updated->status);
        self::assertSame(OrderStatus::DELIVERED, $order->fresh()->status);
        self::assertSame('BR987654321', $order->fresh()->tracking_code);
    }

    private function melhorEnvioIntegration(): ShippingIntegration
    {
        return ShippingIntegration::query()->create([
            'provider' => 'melhor_envio',
            'is_enabled' => true,
            'environment' => 'sandbox',
            'configuration' => ['application_name' => 'SABA', 'technical_email' => 'dev@saba.test'],
            'credentials' => ['client_id' => 'id', 'client_secret' => 'secret'],
            'access_token' => 'token',
        ]);
    }

    private function shipment(Order $order, ShipmentStatus $status): Shipment
    {
        return Shipment::query()->create([
            'order_id' => $order->id,
            'provider' => 'melhor_envio',
            'external_id' => 'shipment-'.Str::lower(Str::random(8)),
            'carrier_name' => 'Correios',
            'service_external_id' => '2',
            'service_name' => 'SEDEX',
            'status' => $status,
            'customer_price_cents' => 4990,
        ]);
    }

    private function order(OrderStatus $status): Order
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
            'payment_status' => PaymentStatus::APPROVED,
            'subtotal_cents' => 10000,
            'shipping_cents' => 4990,
            'discount_cents' => 0,
            'total_cents' => 14990,
            'shipping_option_id' => 'melhor_envio:2',
            'shipping_provider' => 'melhor_envio',
            'shipping_service_id' => '2',
            'shipping_name' => 'SEDEX',
            'shipping_description' => 'Correios · 2 dias úteis',
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Actions\Shipping\GenerateShipmentLabelAction;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Settings\Models\StoreSetting;
use App\Domain\Shipping\Models\Shipment;
use App\Domain\Shipping\Models\ShippingCarrier;
use App\Domain\Shipping\Models\ShippingIntegration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

trait ShippingFixtures
{
    private function integration(): ShippingIntegration
    {
        $integration = ShippingIntegration::forProvider('melhor_envio');
        $integration->update([
            'is_enabled' => true, 'environment' => 'sandbox', 'access_token' => 'access-test', 'refresh_token' => 'refresh-test',
            'credentials' => ['client_id' => 'client-test', 'client_secret' => 'secret-test'],
            'configuration' => ['application_name' => 'SABA', 'technical_email' => 'dev@saba.test'],
        ]);

        return $integration->refresh();
    }

    private function carrier(): ShippingCarrier
    {
        $integration = $this->integration();
        $carrier = ShippingCarrier::query()->create(['shipping_integration_id' => $integration->id, 'environment' => 'sandbox', 'external_id' => '901', 'name' => 'Transportadora de teste', 'is_enabled' => true]);
        $carrier->services()->create(['external_id' => '902', 'name' => 'Expresso de teste', 'is_enabled' => true, 'fulfillment_mode' => 'automatic']);

        return $carrier;
    }

    private function store(): void
    {
        StoreSetting::query()->create(['store_name' => 'SABA', 'legal_name' => 'Saba Equipamentos', 'cnpj' => '12345678000199', 'state_register' => 'ISENTO', 'phone' => '11999999999', 'email' => 'saba@example.test', 'zip_code' => '01001000', 'street' => 'Rua da loja', 'number' => '123', 'neighborhood' => 'Centro', 'city' => 'São Paulo', 'state' => 'SP']);
    }

    private function order(int $packageCount = 1): Order
    {
        $carrier = $this->carrier();
        $this->store();
        $snapshot = [
            'id' => 'melhor_envio:902', 'provider' => 'melhor_envio', 'integration_id' => $carrier->shipping_integration_id,
            'environment' => 'sandbox', 'fulfillment_mode' => 'automatic', 'service_external_id' => '902', 'carrier_external_id' => '901',
            'name' => 'Expresso de teste', 'carrier_name' => 'Transportadora de teste', 'price' => 4890, 'description' => '6 a 8 dias úteis',
            'packages' => array_fill(0, $packageCount, ['dimensions' => ['height' => 10, 'width' => 20, 'length' => 30], 'weight' => '1.25', 'price_cents' => 2445, 'insurance_cents' => 10000, 'products' => [['id' => '1', 'quantity' => 1]]]),
            'metadata' => ['products' => [['id' => '1', 'name' => 'Produto histórico', 'quantity' => $packageCount, 'unit_price_cents' => 10000]], 'grouped_volumes' => false, 'label_format' => 'pdf'],
        ];
        $order = Order::query()->create([
            'number' => 'SABA-'.Str::random(8), 'checkout_token' => (string) Str::uuid(), 'checkout_session_hash' => hash('sha256', 'session'),
            'customer_name' => 'Cliente Teste', 'customer_email' => 'cliente@example.test', 'customer_phone' => '11999999999', 'customer_document' => '52998224725',
            'status' => OrderStatus::PROCESSING, 'payment_status' => PaymentStatus::APPROVED,
            'subtotal_cents' => 10000 * $packageCount, 'shipping_cents' => 4890, 'discount_cents' => 0, 'total_cents' => 10000 * $packageCount + 4890,
            'shipping_provider' => 'melhor_envio', 'shipping_integration_id' => $carrier->shipping_integration_id,
            'shipping_option_id' => 'melhor_envio:902', 'shipping_service_id' => '902', 'shipping_name' => 'Expresso de teste', 'shipping_description' => 'Prazo contratado', 'shipping_quote_snapshot' => $snapshot,
        ]);
        $order->address()->create(['postal_code' => '13000000', 'street' => 'Rua cliente', 'number' => '1', 'neighborhood' => 'Centro', 'city' => 'Campinas', 'state' => 'SP']);
        $order->items()->create(['product_name' => 'Produto histórico', 'regular_unit_price' => 10000, 'unit_price' => 10000, 'quantity' => $packageCount, 'total' => 10000 * $packageCount]);

        return $order;
    }

    private function api(int $generationStatus = 200): void
    {
        $cartNumber = 0;
        Http::fake([
            '*/api/v2/me/cart' => function () use (&$cartNumber) {
                return Http::response(['id' => 'external-'.++$cartNumber, 'price' => '48.90']);
            },
            '*/api/v2/me/shipment/checkout' => Http::response(['success' => true]),
            '*/api/v2/me/shipment/generate' => $generationStatus === 200 ? Http::response(['success' => true]) : Http::sequence()->push(['success' => false], $generationStatus)->push(['success' => true]),
        ]);
    }

    private function generate(Order $order): Shipment
    {
        return app(GenerateShipmentLabelAction::class)->execute($order, $this->validInvoiceKey());
    }

    private function validInvoiceKey(): string
    {
        // Syntactically valid NF-e model 55 key used only with Http::fake() in tests.
        return '35261025256966000115550010000000011000000011';
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Coupon;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Coupon\ApplyCouponAction;
use App\Actions\Coupon\ManageCouponUsageAction;
use App\Actions\Coupon\QuoteCouponAction;
use App\Actions\Coupon\SaveCouponAction;
use App\Actions\Order\CreateInitialOrderAction;
use App\Actions\Order\GetCheckoutTokenAction;
use App\Actions\Payment\ProcessAsaasCheckoutEventAction;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Coupon\Models\Coupon;
use App\Domain\Coupon\Models\CouponClaim;
use App\Domain\Coupon\Models\CouponUsage;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Models\PaymentIntegration;
use App\Domain\Shipping\Models\ShippingIntegration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class CouponCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private const CHECKOUT_ID = '990d84db-a1c2-4ce9-adfe-6bd9b934fa54';
    private const TOKEN = 'asaas-webhook-secret-for-coupons-must-be-long';

    private function setupPickup(): void
    {
        ShippingIntegration::forProvider('local_pickup')->update([
            'is_enabled' => true, 'configuration' => ['address' => 'SABA'],
        ]);
    }

    private function coupon(array $overrides = []): Coupon
    {
        return Coupon::query()->create(array_merge([
            'code' => 'PRIMEIRA10', 'discount_type' => 'percentage', 'value' => 10,
            'scope' => 'all', 'is_active' => true, 'allow_sale_items' => false,
        ], $overrides));
    }

    private function order(Product $product, string $document, ?string $coupon = 'PRIMEIRA10'): Order
    {
        app(AddCartItemAction::class)->execute($product->id, quantity: 1);
        $token = app(GetCheckoutTokenAction::class)->execute();
        $input = [
            'name' => 'Cliente Teste', 'email' => 'cliente@example.test', 'phone' => '11999999999',
            'document' => $document, 'postal_code' => '13077001', 'street' => 'Rua teste',
            'number' => '10', 'neighborhood' => 'Centro', 'city' => 'Campinas', 'state' => 'SP',
            'shipping_option' => 'local_pickup:pickup',
        ];
        if ($coupon !== null) {
            $input['coupon_code'] = $coupon;
        }
        return app(CreateInitialOrderAction::class)->execute($input, $token);
    }

    private function enableAsaas(): void
    {
        PaymentIntegration::query()->create([
            'provider' => 'asaas', 'environment' => 'sandbox', 'is_enabled' => true,
            'credentials' => ['access_token' => '$aact_hmlg_TEST', 'webhook_secret' => self::TOKEN],
            'configuration' => ['expiration_minutes' => 60, 'pix' => true, 'credit_card' => true, 'max_installments' => 1],
        ]);
        Http::fake(['api-sandbox.asaas.com/v3/checkouts' => Http::response([
            'id' => self::CHECKOUT_ID,
            'link' => 'https://sandbox.asaas.com/000/checkoutSession/show/'.self::CHECKOUT_ID,
        ], 200)]);
    }

    public function test_coupon_only_applies_when_submitted_and_customer_cannot_use_twice_even_with_different_email(): void
    {
        $this->setupPickup();
        $coupon = $this->coupon();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        $first = $this->order($product, '52998224725', null);
        $this->assertSame(0, $first->discount_cents);
        $this->assertNull($first->coupon_id);
        $second = $this->order($product, '52998224725');
        $this->assertSame(1000, $second->discount_cents);
        $this->assertSame(9000, $second->total_cents);
        $this->assertSame($coupon->id, $second->coupon_id);
        $this->assertSame('consumed', CouponUsage::query()->where('order_id', $second->id)->firstOrFail()->status);
        $this->assertDatabaseCount('coupon_claims', 1);
        try {
            $this->order($product, '52998224725');
            $this->fail('CPF/CNPJ reutilizado deveria ser rejeitado.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('coupon', $exception->errors());
        }
        $this->assertDatabaseCount('coupon_usages', 1);
    }

    public function test_scope_and_promotional_products_are_not_discounted_by_default(): void
    {
        $category = Category::factory()->create();
        $eligible = Product::factory()->create(['category_id' => $category->id, 'price' => 10000]);
        $promo = Product::factory()->create(['category_id' => $category->id, 'price' => 10000, 'promotional_price' => 8000]);
        $other = Product::factory()->create(['price' => 5000]);
        $coupon = $this->coupon(['scope' => 'categories']);
        $coupon->categories()->attach($category->id);
        $cart = [
            ['product' => $eligible, 'offer' => (object) ['pricing' => new \App\Domain\Catalog\ValueObjects\ProductPricing(10000)], 'subtotal' => 10000],
            ['product' => $promo, 'offer' => (object) ['pricing' => new \App\Domain\Catalog\ValueObjects\ProductPricing(10000, 8000)], 'subtotal' => 8000],
            ['product' => $other, 'offer' => (object) ['pricing' => new \App\Domain\Catalog\ValueObjects\ProductPricing(5000)], 'subtotal' => 5000],
        ];
        $this->assertSame(1000, app(QuoteCouponAction::class)->calculate($coupon, $cart, 23000));
        $coupon->update(['allow_sale_items' => true]);
        $this->assertSame(1800, app(QuoteCouponAction::class)->calculate($coupon->fresh(), $cart, 23000));
    }

    public function test_asaas_checkout_holds_unique_coupon_until_expiration_and_allows_reuse_after_confirmed_expiration(): void
    {
        $this->setupPickup();
        $this->enableAsaas();
        $this->coupon();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 5]);
        $order = $this->order($product, '52998224725');
        $this->assertSame(9000, $order->total_cents);
        $this->assertSame('reserved', CouponUsage::query()->sole()->status);
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertDatabaseCount('coupon_claims', 1);
        $sent = Http::recorded()->first()[0]->data();
        $this->assertSame(90.0, (float) $sent['items'][0]['value']);
        $this->assertCount(1, $sent['items']);

        $payload = [
            'id' => 'evt-expire-unique-coupon', 'event' => 'CHECKOUT_EXPIRED',
            'checkout' => ['id' => self::CHECKOUT_ID, 'status' => 'EXPIRED',
                'externalReference' => $order->number,
                'items' => [['name' => 'Pedido', 'quantity' => 1, 'value' => 90.00]]],
        ];
        $this->postJson('/webhooks/payments/asaas', $payload, ['asaas-access-token' => self::TOKEN])->assertOk();
        $this->assertSame('released', CouponUsage::query()->sole()->status);
        $this->assertDatabaseCount('coupon_claims', 0);
        Http::fake(['api-sandbox.asaas.com/v3/checkouts' => Http::response([
            'id' => '15c327d0-3b0a-4f01-9497-ef5c8f9d4444',
            'link' => 'https://sandbox.asaas.com/000/checkoutSession/show/15c327d0-3b0a-4f01-9497-ef5c8f9d4444',
        ], 200)]);
        $this->order($product, '52998224725');
        $this->assertDatabaseCount('coupon_claims', 1);
    }

    public function test_paid_checkout_makes_coupon_use_permanent_even_after_commercial_cancellation(): void
    {
        $this->setupPickup();
        $this->enableAsaas();
        $this->coupon();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 5]);
        $order = $this->order($product, '52998224725');
        $payload = [
            'id' => 'evt-pay-unique-coupon', 'event' => 'CHECKOUT_PAID',
            'checkout' => ['id' => self::CHECKOUT_ID, 'status' => 'PAID',
                'externalReference' => $order->number,
                'items' => [['name' => 'Pedido', 'quantity' => 1, 'value' => 90.00]]],
        ];
        $this->postJson('/webhooks/payments/asaas', $payload, ['asaas-access-token' => self::TOKEN])->assertOk();
        $this->assertSame('consumed', CouponUsage::query()->sole()->status);
        $this->assertSame(4, $product->fresh()->stock);
        $this->assertDatabaseCount('coupon_claims', 1);
        app(\App\Actions\Order\TransitionOrderStatusAction::class)->execute($order, \App\Domain\Order\Enums\OrderStatus::CANCELLED);
        $this->assertSame('consumed', CouponUsage::query()->sole()->status);
        $this->assertDatabaseCount('coupon_claims', 1);
    }
}

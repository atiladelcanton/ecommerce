<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Coupon\QuoteCouponAction;
use App\Actions\Coupon\SaveCouponAction;
use App\Actions\Order\CreateInitialOrderAction;
use App\Actions\Order\GetCheckoutTokenAction;
use App\Actions\Order\TransitionOrderStatusAction;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Coupon\Models\Coupon;
use App\Domain\Coupon\Models\CouponUsage;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Shipping\Models\ShippingIntegration;
use App\Livewire\Store\CheckoutPage;
use App\Support\CheckoutCoupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

final class CouponCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        ShippingIntegration::forProvider('local_pickup')->update([
            'is_enabled' => true, 'configuration' => ['address' => 'SABA'],
        ]);
    }

    private function coupon(array $overrides = []): Coupon
    {
        return Coupon::query()->create(array_merge([
            'code' => 'SABA10', 'discount_type' => 'percentage', 'value' => 10,
            'scope' => 'all', 'is_active' => true, 'allow_sale_items' => false,
        ], $overrides));
    }

    private function input(string $document = '52998224725'): array
    {
        return [
            'name' => 'Cliente', 'email' => 'cliente@example.test', 'phone' => '11999999999',
            'document' => $document, 'postal_code' => '13077001', 'street' => 'Rua teste',
            'number' => '10', 'neighborhood' => 'Centro', 'city' => 'Campinas', 'state' => 'SP',
            'shipping_option' => 'local_pickup:pickup',
        ];
    }

    private function checkout(?string $code = 'SABA10', ?Product $product = null, ?string $document = null): Order
    {
        $product ??= Product::factory()->create(['price' => 10000, 'stock' => 10]);
        app(AddCartItemAction::class)->execute($product->id);
        $token = app(GetCheckoutTokenAction::class)->execute();
        if ($code !== null) {
            app(CheckoutCoupon::class)->set($token, $code);
        }
        return app(CreateInitialOrderAction::class)->execute($this->input($document ?? '52998224725'), $token);
    }

    public function test_percent_coupon_uses_original_schema_and_consumes_once(): void
    {
        $coupon = $this->coupon();
        $order = $this->checkout();
        $this->assertSame($coupon->id, $order->coupon_id);
        $this->assertSame('SABA10', $order->coupon_code);
        $this->assertSame(1000, $order->discount_cents);
        $this->assertSame(9000, $order->total_cents);
        $this->assertSame('consumed', CouponUsage::query()->sole()->status);
        $this->assertDatabaseCount('coupon_claims', 1);
        $this->assertDatabaseCount('coupon_usages', 1);
        app(\App\Actions\Payment\HandleApprovedPaymentAction::class)->execute($order, $order->payments()->sole());
        $this->assertDatabaseCount('coupon_claims', 1);
        $this->assertDatabaseCount('coupon_usages', 1);
    }

    public function test_same_document_cannot_apply_the_same_coupon_twice(): void
    {
        $this->coupon();
        $this->checkout();
        try {
            $this->checkout();
            $this->fail('Cupom deveria rejeitar segundo uso para o mesmo CPF/CNPJ.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('coupon', $exception->errors());
        }
        $this->assertDatabaseCount('coupon_usages', 1);
    }

    public function test_fixed_coupon_has_maximum_and_scoped_categories(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'price' => 10000]);
        $coupon = $this->coupon([
            'discount_type' => 'fixed', 'value' => 5000, 'scope' => 'categories', 'maximum_discount_cents' => 3000,
        ]);
        $coupon->categories()->attach($category->id);
        $this->assertSame(3000, $this->checkout(product: $product)->discount_cents);
    }

    public function test_sale_products_are_not_eligible_by_default(): void
    {
        $this->coupon();
        $product = Product::factory()->create(['price' => 10000, 'promotional_price' => 8000]);
        try {
            $this->checkout(product: $product);
            $this->fail('Produtos em promoção devem ser excluídos por padrão.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('coupon', $exception->errors());
        }
    }

    public function test_frontend_applies_and_removes_without_changing_order(): void
    {
        $this->coupon();
        app(AddCartItemAction::class)->execute(Product::factory()->create(['price' => 10000])->id);
        $component = Livewire::test(CheckoutPage::class)
            ->set('couponCode', 'saba10')->call('applyCoupon')->assertHasNoErrors();
        $this->assertSame('SABA10', app(CheckoutCoupon::class)->get($component->get('checkoutToken')));
        $component->assertSee('SABA10 aplicado')->call('removeCoupon')->assertHasNoErrors();
        $this->assertNull(app(CheckoutCoupon::class)->get($component->get('checkoutToken')));
    }

    public function test_pending_assisted_shipping_reserves_and_cancellation_releases_coupon(): void
    {
        $this->coupon();
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        app(AddCartItemAction::class)->execute($product->id);
        $token = app(GetCheckoutTokenAction::class)->execute();
        app(CheckoutCoupon::class)->set($token, 'SABA10');
        $order = app(CreateInitialOrderAction::class)->requestShippingQuote($this->input(), $token);
        $this->assertSame(1000, $order->discount_cents);
        $this->assertNull($order->total_cents);
        $this->assertSame('reserved', CouponUsage::query()->sole()->status);
        app(TransitionOrderStatusAction::class)->execute($order, OrderStatus::CANCELLED);
        $this->assertSame('released', CouponUsage::query()->sole()->status);
        $this->assertDatabaseCount('coupon_claims', 0);
    }

    public function test_admin_save_reuses_existing_columns_and_scope_relations(): void
    {
        $category = Category::factory()->create();
        $coupon = app(SaveCouponAction::class)->execute([
            'code' => ' boas_vindas ', 'discount_type' => 'percentage', 'value' => 15,
            'scope' => 'categories', 'category_ids' => [$category->id], 'is_active' => true,
        ]);
        $this->assertSame('BOAS_VINDAS', $coupon->code);
        $this->assertSame(15, $coupon->value);
        $this->assertSame('categories', $coupon->scope);
        $this->assertSame([$category->id], $coupon->categories()->pluck('categories.id')->all());
    }
}

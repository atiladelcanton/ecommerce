<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Cart\GetCartAction;
use App\Actions\Catalog\ManageOrderInventoryAction;
use App\Actions\Order\ConfirmQuotedOrderAction;
use App\Actions\Order\CreateInitialOrderAction;
use App\Actions\Order\GetCheckoutTokenAction;
use App\Actions\Order\TransitionOrderStatusAction;
use App\Actions\Shipping\GetOrderShippingQuoteLinkAction;
use App\Actions\Shipping\PublishOrderShippingQuoteAction;
use App\Domain\Catalog\Enums\ProductAvailability;
use App\Domain\Catalog\Models\InventoryReservation;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Services\InventoryAvailability;
use App\Domain\Customer\Models\Customer;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Models\OrderShippingQuote;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Shipping\Enums\ShippingPricingStatus;
use App\Domain\Shipping\Models\ShippingIntegration;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Livewire\Store\CheckoutPage;
use App\Livewire\Store\ShippingQuoteCheckout;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

final class AssistedShippingCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_creates_unpaid_order_unknown_totals_and_reservation_without_shipments(): void
    {
        $product = Product::factory()->create(['stock' => 3]);
        app(AddCartItemAction::class)->execute($product->id, quantity: 2);
        $token = app(GetCheckoutTokenAction::class)->execute();
        $order = app(CreateInitialOrderAction::class)->requestShippingQuote($this->input(), $token);
        $again = app(CreateInitialOrderAction::class)->requestShippingQuote($this->input(), $token);
        $this->assertSame($order->id, $again->id);
        $this->assertSame(OrderStatus::RECEIVED, $order->status);
        $this->assertSame(PaymentStatus::PENDING, $order->payment_status);
        $this->assertSame(ShippingPricingStatus::AWAITING_QUOTE, $order->shipping_pricing_status);
        $this->assertNull($order->shipping_cents);
        $this->assertNull($order->total_cents);
        $this->assertSame('A definir', $order->formattedTotal());
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertSame(1, app(InventoryAvailability::class)->available($product));
        $this->assertDatabaseCount('inventory_reservations', 1);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('shipments', 0);
        $this->assertSame([], app(GetCartAction::class)->execute()['items']);
        Http::assertNothingSent();
    }

    public function test_checkout_ui_and_confirmation_show_pending_freight_truthfully(): void
    {
        $product = Product::factory()->create();
        app(AddCartItemAction::class)->execute($product->id);
        $component = Livewire::test(CheckoutPage::class)->set('form', $this->input())->assertSee('Solicitar cotação de entrega');
        $component->call('requestShippingQuote')->assertHasNoErrors();
        $order = Order::query()->sole();
        $this->withCookie(config('session.cookie'), session()->getId())->get(route('store.order.confirmation', $order->number))
            ->assertOk()->assertSee('Solicitação de entrega recebida')->assertSee('A definir')->assertDontSee('Aprovado — ambiente de demonstração');
    }

    public function test_admin_publishes_quote_and_customer_pays_snapshot_once(): void
    {
        [$product, $order] = $this->request();
        $itemPrice = $order->items()->sole()->unit_price;
        $quote = $this->publish($order);
        $link = app(GetOrderShippingQuoteLinkAction::class)->execute($quote);
        $product->update(['price' => 999999]);
        $this->get($link)->assertOk()->assertSee('Revise e conclua seu pedido')->assertSee('R$ 48,90');
        $action = app(ConfirmQuotedOrderAction::class);
        $paid = $action->execute($order->number, $quote->revision, $link);
        $action->execute($order->number, $quote->revision, $link);
        $this->assertSame(OrderStatus::PAID, $paid->status);
        $this->assertSame(ShippingPricingStatus::CONFIRMED, $paid->shipping_pricing_status);
        $this->assertSame($itemPrice + 4890, $paid->total_cents);
        $this->assertSame(4, $product->fresh()->stock);
        $this->assertDatabaseCount('payments', 1);
        $this->assertNotNull($quote->fresh()->accepted_at);
        $this->assertNotNull(InventoryReservation::query()->sole()->consumed_at);
        Http::assertNothingSent();
    }

    public function test_revised_quote_invalidates_previous_link_and_preserves_revisions(): void
    {
        [, $order] = $this->request();
        $old = $this->publish($order);
        $link = app(GetOrderShippingQuoteLinkAction::class)->execute($old);
        $new = $this->publish($order, ['price_cents' => 9990]);
        $this->assertSame(2, $new->revision);
        $this->assertNotNull($old->fresh()->superseded_at);
        $this->get($link)->assertGone();
        $this->assertDatabaseCount('order_shipping_quotes', 2);
        $this->assertSame(9990, $order->fresh()->shipping_cents);
    }

    public function test_link_is_signed_owner_scoped_and_does_not_pay_on_get(): void
    {
        [, $order] = $this->request();
        $owner = Customer::factory()->create();
        $order->update(['customer_id' => $owner->id]);
        $quote = $this->publish($order);
        $link = app(GetOrderShippingQuoteLinkAction::class)->execute($quote);
        $this->get($link)->assertRedirect(route('customer.login'));
        $this->actingAs(Customer::factory()->create(), 'customer')->get($link)->assertForbidden();
        $this->actingAs($owner, 'customer')->get($link)->assertOk();
        $this->get(str_replace('signature=', 'signature=invalid', $link))->assertForbidden();
        $this->assertDatabaseCount('payments', 0);
        $this->assertNull($quote->fresh()->accepted_at);
    }

    public function test_livewire_review_requires_valid_capability_and_confirm_is_explicit(): void
    {
        [, $order] = $this->request();
        $quote = $this->publish($order);
        $link = app(GetOrderShippingQuoteLinkAction::class)->execute($quote);
        Livewire::test(ShippingQuoteCheckout::class, ['number' => $order->number, 'revision' => $quote->revision, 'signedUrl' => $link])
            ->assertSee('Confirmar pedido e pagamento de teste')->call('confirmPayment')->assertHasNoErrors()->assertSee('Pedido confirmado');
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_reservation_expires_without_scheduler_and_payment_rechecks_stock(): void
    {
        [$product, $order] = $this->request();
        $quote = $this->publish($order);
        $link = app(GetOrderShippingQuoteLinkAction::class)->execute($quote);
        $this->travel(25)->hours();
        $this->assertSame(5, app(InventoryAvailability::class)->available($product));
        $this->assertSame(1, app(ManageOrderInventoryAction::class)->expire());
        $this->assertSame(0, app(ManageOrderInventoryAction::class)->expire());
        $product->update(['stock' => 0]);
        try {
            app(ConfirmQuotedOrderAction::class)->execute($order->number, $quote->revision, $link);
            self::fail('Expected depleted stock');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('stock', $e->errors());
        }
        $this->assertDatabaseCount('payments', 0);
        $this->assertNull($quote->fresh()->accepted_at);
        $this->assertSame(ShippingPricingStatus::QUOTED, $order->fresh()->shipping_pricing_status);
    }

    public function test_expired_reservation_can_still_be_paid_if_stock_is_available(): void
    {
        [$product, $order] = $this->request();
        $quote = $this->publish($order);
        $link = app(GetOrderShippingQuoteLinkAction::class)->execute($quote);
        $this->travel(25)->hours();
        app(ConfirmQuotedOrderAction::class)->execute($order->number, 1, $link);
        $this->assertSame(4, $product->fresh()->stock);
    }

    public function test_cancel_releases_reservation_and_paid_cancellation_restores_only_once(): void
    {
        [$product, $order] = $this->request();
        app(TransitionOrderStatusAction::class)->execute($order, OrderStatus::CANCELLED);
        $this->assertSame(5, app(InventoryAvailability::class)->available($product));
        [$product, $order] = $this->request();
        $quote = $this->publish($order);
        app(ConfirmQuotedOrderAction::class)->execute($order->number, 1, app(GetOrderShippingQuoteLinkAction::class)->execute($quote));
        app(TransitionOrderStatusAction::class)->execute($order->fresh(), OrderStatus::CANCELLED);
        app(ManageOrderInventoryAction::class)->cancel($order);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_made_to_order_has_no_physical_reservation_or_stock_limit(): void
    {
        $product = Product::factory()->create(['availability' => ProductAvailability::ON_DEMAND, 'stock' => 0]);
        app(AddCartItemAction::class)->execute($product->id, quantity: 20);
        app(CreateInitialOrderAction::class)->requestShippingQuote($this->input(), app(GetCheckoutTokenAction::class)->execute());
        $this->assertDatabaseCount('inventory_reservations', 0);
    }

    public function test_unquoted_order_cannot_be_moved_to_paid_and_paid_quote_cannot_be_repriced(): void
    {
        [, $order] = $this->request();
        try {
            app(TransitionOrderStatusAction::class)->execute($order, OrderStatus::PAID);
            self::fail('Expected unquoted guard');
        } catch (ValidationException) {
        }
        $quote = $this->publish($order);
        app(ConfirmQuotedOrderAction::class)->execute($order->number, 1, app(GetOrderShippingQuoteLinkAction::class)->execute($quote));
        $this->expectException(ValidationException::class);
        $this->publish($order);
    }

    public function test_assisted_checkout_does_not_bypass_available_delivery_and_retains_cart(): void
    {
        // Pickup alone remains compatible with the request; a real automatic option cannot be bypassed.
        ShippingIntegration::forProvider('local_pickup')->update(['is_enabled' => true, 'configuration' => ['address' => 'SABA']]);
        [, $order] = $this->request();
        $this->assertSame(ShippingPricingStatus::AWAITING_QUOTE, $order->shipping_pricing_status);
    }

    public function test_admin_quote_action_renders_and_publishes_with_filament_5(): void
    {
        [, $order] = $this->request();
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::bootCurrentPanel();
        Livewire::test(ViewOrder::class, ['record' => $order->id])->mountAction('quoteShipping')
            ->fillForm(['carrier_name' => 'Transportadora contratada', 'service_name' => 'Rodoviário', 'price_cents' => '48,90', 'estimated_days' => 5, 'expires_at' => now()->addDays(7)->format('Y-m-d H:i:s')])
            ->callMountedAction()->assertHasNoErrors()->assertNotified('Cotação publicada');
        $this->assertSame(4890, $order->fresh()->shipping_cents);
    }

    private function request(): array
    {
        $product = Product::factory()->create(['stock' => 5]);
        app(AddCartItemAction::class)->execute($product->id);

        return [$product, app(CreateInitialOrderAction::class)->requestShippingQuote($this->input(), app(GetCheckoutTokenAction::class)->execute())];
    }

    private function publish(Order $order, array $overrides = []): OrderShippingQuote
    {
        return app(PublishOrderShippingQuoteAction::class)->execute($order, array_replace(['carrier_name' => 'Transportadora contratada', 'service_name' => 'Rodoviário', 'price_cents' => 4890, 'estimated_days' => 5, 'expires_at' => now()->addDays(7)], $overrides), User::factory()->create()->id);
    }

    private function input(): array
    {
        return ['name' => 'Cliente', 'email' => 'cliente@example.test', 'phone' => '11999999999', 'document' => '52998224725', 'postal_code' => '13077001', 'street' => 'Rua teste', 'number' => '10', 'neighborhood' => 'Centro', 'city' => 'Campinas', 'state' => 'SP'];
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Customer;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Customer\CreateCustomerAddressAction;
use App\Actions\Order\CreateInitialOrderAction;
use App\Actions\Order\GetCheckoutTokenAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Customer\Models\Customer;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Livewire\Store\CheckoutPage;
use App\Livewire\Store\CustomerOrderTracking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\Support\UsesDemoShipping;
use Tests\TestCase;

final class CustomerAccountTest extends TestCase
{
    use RefreshDatabase;
    use UsesDemoShipping;

    private function checkoutInput(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Cliente SABA', 'email' => 'cliente@saba.test', 'phone' => '11999991234',
            'document' => '52998224725', 'postal_code' => '13000000', 'street' => 'Rua Teste',
            'number' => '100', 'complement' => null, 'neighborhood' => 'Centro', 'city' => 'Campinas',
            'state' => 'SP', 'shipping_option' => 'standard',
        ], $overrides);
    }

    private function guestOrder(string $email = 'cliente@saba.test'): Order
    {
        app(AddCartItemAction::class)->execute(Product::factory()->create(['price' => 10000])->id);

        return app(CreateInitialOrderAction::class)->execute(
            $this->checkoutInput(['email' => $email]),
            app(GetCheckoutTokenAction::class)->execute(),
        );
    }

    public function test_customer_can_register_login_and_open_dashboard(): void
    {
        $this->post(route('customer.register.store'), [
            'name' => 'Cliente SABA',
            'email' => 'CLIENTE@SABA.TEST',
            'phone' => '(11) 99999-1234',
            'document' => '529.982.247-25',
            'password' => 'senha-segura',
            'password_confirmation' => 'senha-segura',
        ])->assertRedirect(route('customer.dashboard'));

        $customer = Customer::query()->sole();
        $this->assertSame('cliente@saba.test', $customer->email);
        $this->assertSame('11999991234', $customer->phone);
        $this->assertSame('52998224725', $customer->document);
        $this->assertAuthenticatedAs($customer, 'customer');
        $this->get(route('customer.dashboard'))->assertOk()->assertSee('Olá, Cliente SABA');
    }

    public function test_guest_order_from_same_session_is_linked_when_account_is_created_with_same_email(): void
    {
        $order = $this->guestOrder();
        $this->assertNull($order->customer_id);

        $this->post(route('customer.register.store'), [
            'name' => 'Cliente SABA', 'email' => 'cliente@saba.test', 'phone' => '11999991234',
            'document' => '52998224725', 'password' => 'senha-segura', 'password_confirmation' => 'senha-segura',
        ])->assertRedirect(route('customer.dashboard'));

        $this->assertSame(Customer::query()->sole()->id, $order->fresh()->customer_id);
    }

    public function test_guest_order_requires_matching_document_to_be_claimed(): void
    {
        $order = $this->guestOrder();

        $this->post(route('customer.register.store'), [
            'name' => 'Cliente SABA', 'email' => 'cliente@saba.test', 'phone' => '11999991234',
            'document' => '12345678901', 'password' => 'senha-segura', 'password_confirmation' => 'senha-segura',
        ]);

        $this->assertNull($order->fresh()->customer_id);
    }

    public function test_multiple_guest_orders_from_same_session_are_claimed(): void
    {
        $first = $this->guestOrder();
        $second = $this->guestOrder();

        $this->post(route('customer.register.store'), [
            'name' => 'Cliente SABA', 'email' => 'cliente@saba.test', 'phone' => '11999991234',
            'document' => '52998224725', 'password' => 'senha-segura', 'password_confirmation' => 'senha-segura',
        ]);

        $customer = Customer::query()->sole();
        $this->assertSame($customer->id, $first->fresh()->customer_id);
        $this->assertSame($customer->id, $second->fresh()->customer_id);
    }

    public function test_order_from_another_session_is_not_claimed_during_registration(): void
    {
        $order = $this->guestOrder();
        session()->flush();
        session()->migrate(true);

        $this->post(route('customer.register.store'), [
            'name' => 'Cliente SABA', 'email' => 'cliente@saba.test', 'phone' => '11999991234',
            'document' => '52998224725', 'password' => 'senha-segura', 'password_confirmation' => 'senha-segura',
        ]);

        $this->assertNull($order->fresh()->customer_id);
    }

    public function test_authenticated_checkout_is_attached_to_customer_and_prefills_identity(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Comprador', 'email' => 'comprador@saba.test', 'phone' => '41999990000', 'document' => '12345678901',
        ]);
        Auth::guard('customer')->login($customer);
        app(AddCartItemAction::class)->execute(Product::factory()->create(['price' => 12000])->id);

        $component = Livewire::test(CheckoutPage::class);
        $this->assertSame('Comprador', $component->get('form.name'));
        $this->assertSame('comprador@saba.test', $component->get('form.email'));
        $component->set('form', $this->checkoutInput([
            'name' => 'Comprador', 'email' => 'comprador@saba.test', 'phone' => '41999990000', 'document' => '12345678901',
        ]))->call('submit')->assertHasNoErrors();

        $this->assertSame($customer->id, Order::query()->sole()->customer_id);
    }

    public function test_authenticated_checkout_loads_default_address_and_can_switch_to_new_address(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Comprador', 'email' => 'comprador@saba.test', 'phone' => '41999990000', 'document' => '12345678901',
        ]);
        $default = app(CreateCustomerAddressAction::class)->execute($customer, [
            'label' => 'Casa', 'postal_code' => '80000000', 'street' => 'Rua Principal', 'number' => '99',
            'neighborhood' => 'Centro', 'city' => 'Curitiba', 'state' => 'PR', 'is_default' => false,
        ]);
        app(CreateCustomerAddressAction::class)->execute($customer, [
            'label' => 'Empresa', 'postal_code' => '81000000', 'street' => 'Rua Empresa', 'number' => '10',
            'neighborhood' => 'Industrial', 'city' => 'Curitiba', 'state' => 'PR', 'is_default' => false,
        ]);
        Auth::guard('customer')->login($customer);
        app(AddCartItemAction::class)->execute(Product::factory()->create(['price' => 12000])->id);

        Livewire::test(CheckoutPage::class)
            ->assertSet('addressChoice', 'saved:'.$default->id)
            ->assertSet('form.street', 'Rua Principal')
            ->set('addressChoice', 'new')
            ->assertSet('form.street', '');
    }

    public function test_customer_can_only_see_own_orders(): void
    {
        $customer = Customer::factory()->create();
        $other = Customer::factory()->create();
        $mine = $this->createOrderFor($customer, 'SABA-100001');
        $theirs = $this->createOrderFor($other, 'SABA-100002');
        Auth::guard('customer')->login($customer);

        $this->get(route('customer.order', $mine->number))->assertOk();
        $this->get(route('customer.order', $theirs->number))->assertNotFound();
        Livewire::test(CustomerOrderTracking::class, ['number' => $mine->number])->assertSee($mine->number);
    }

    public function test_tracking_view_shows_progress_payment_and_history(): void
    {
        $customer = Customer::factory()->create();
        $order = $this->createOrderFor($customer, 'SABA-100003', OrderStatus::SHIPPED);
        $order->update(['tracking_code' => 'BR123456789', 'tracking_url' => 'https://example.test/rastreio']);
        $order->statusHistories()->create(['from_status' => OrderStatus::PROCESSING, 'to_status' => OrderStatus::SHIPPED, 'description' => 'Pedido enviado.']);
        Auth::guard('customer')->login($customer);

        Livewire::test(CustomerOrderTracking::class, ['number' => $order->number])
            ->assertSee('Acompanhe seu pedido')
            ->assertSee('Enviado')
            ->assertSee('BR123456789')
            ->assertSee('Pagamento aprovado')
            ->assertSee('Pedido enviado.');
    }

    private function createOrderFor(Customer $customer, string $number, OrderStatus $status = OrderStatus::PAID): Order
    {
        $order = Order::query()->create([
            'customer_id' => $customer->id,
            'number' => $number,
            'checkout_token' => fake()->uuid(),
            'checkout_session_hash' => hash('sha256', $number),
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone ?: '11999990000',
            'customer_document' => $customer->document,
            'status' => $status,
            'payment_status' => PaymentStatus::APPROVED,
            'subtotal_cents' => 10000,
            'shipping_cents' => 4990,
            'discount_cents' => 0,
            'total_cents' => 14990,
            'shipping_option_id' => 'standard',
            'shipping_name' => 'Entrega padrão',
            'shipping_description' => '5 a 8 dias úteis',
        ]);
        $order->items()->create([
            'product_name' => 'Mesa de teste', 'regular_unit_price' => 10000, 'unit_price' => 10000,
            'quantity' => 1, 'total' => 10000,
        ]);
        $order->address()->create([
            'postal_code' => '80000000', 'street' => 'Rua Teste', 'number' => '10', 'neighborhood' => 'Centro',
            'city' => 'Curitiba', 'state' => 'PR',
        ]);
        $order->payments()->create([
            'gateway' => 'fake', 'external_id' => 'fake-'.$number, 'status' => PaymentStatus::APPROVED,
            'amount_cents' => 14990, 'is_demo' => true,
        ]);

        return $order;
    }
}

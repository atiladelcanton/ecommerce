<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Order\CreateInitialOrderAction;
use App\Actions\Order\GetCheckoutSummaryAction;
use App\Actions\Order\GetCheckoutTokenAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\Data\PaymentRequest;
use App\Domain\Payment\Data\PaymentResult;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Domain\Shipping\Data\ShippingQuoteRequest;
use App\Domain\Shipping\Exceptions\ShippingQuoteFailed;
use App\Infrastructure\Cart\SessionCart;
use App\Infrastructure\Shipping\FakeShippingProvider;
use App\Livewire\Store\CheckoutPage;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Support\UsesDemoShipping;
use Tests\TestCase;

final class InitialCheckoutTest extends TestCase
{
    use RefreshDatabase;
    use UsesDemoShipping;

    private function input(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Cliente de teste', 'email' => 'cliente@example.com', 'phone' => '(11) 99999-1234',
            'document' => '529.982.247-25', 'postal_code' => '13000-000', 'street' => 'Rua de Teste',
            'number' => '0', 'complement' => 'Sala 2', 'neighborhood' => 'Centro', 'city' => 'Campinas',
            'state' => 'sp', 'shipping_option' => 'standard',
        ], $overrides);
    }

    private function create(array $overrides = []): Order
    {
        $order = app(CreateInitialOrderAction::class)->execute($this->input($overrides), app(GetCheckoutTokenAction::class)->execute());
        $this->withCookie(config('session.cookie'), session()->getId());

        return $order;
    }

    public function test_public_checkout_requires_no_account_and_shows_form_and_demo_shipping(): void
    {
        app(AddCartItemAction::class)->execute(Product::factory()->create()->id);
        $this->get('/checkout')->assertOk()->assertSee('Dados do cliente')->assertSee('CPF/CNPJ')
            ->assertSee('CEP')->assertSee('Complemento')->assertSee('Entrega padrão')->assertSee('R$ 49,90')
            ->assertSee('Prazo demonstrativo: 5 a 8 dias úteis')->assertSee('Retirada / combinar com a loja')
            ->assertSee('Grátis')->assertSee('Pagamento de demonstração')->assertSee('Finalizar pedido de teste');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_creates_confirmed_order_address_approved_payment_and_status_history(): void
    {
        $product = Product::factory()->create(['price' => 129990]);
        app(AddCartItemAction::class)->execute($product->id, quantity: 2);
        $component = Livewire::test(CheckoutPage::class)->set('form', $this->input());
        $token = $component->get('checkoutToken');
        $component->call('submit')->assertHasNoErrors()->assertDispatched('cart-updated');
        $order = Order::query()->sole();
        $component->assertRedirect(route('store.order.confirmation', $order->number));
        $this->assertSame('SABA-000001', $order->number);
        $this->assertSame($token, $order->checkout_token);
        $this->assertSame(OrderStatus::PAID, $order->status);
        $this->assertSame(PaymentStatus::APPROVED, $order->payment_status);
        $this->assertSame(259980, $order->subtotal_cents);
        $this->assertSame(4990, $order->shipping_cents);
        $this->assertSame(0, $order->discount_cents);
        $this->assertSame(264970, $order->total_cents);
        $this->assertSame('Cliente de teste', $order->customer_name);
        $this->assertSame('cliente@example.com', $order->customer_email);
        $this->assertSame('11999991234', $order->customer_phone);
        $this->assertSame('52998224725', $order->customer_document);
        $this->assertSame('13000000', $order->address->postal_code);
        $this->assertSame('0', $order->address->number);
        $this->assertSame('SP', $order->address->state);
        $this->assertSame('Sala 2', $order->address->complement);
        $this->assertSame('standard', $order->shipping_option_id);
        $this->assertSame('fake', $order->payments->sole()->gateway);
        $this->assertTrue($order->payments->sole()->is_demo);
        $this->assertSame(PaymentStatus::APPROVED, $order->payments->sole()->status);
        $this->assertSame($order->total_cents, $order->payments->sole()->amount_cents);
        $this->assertSame([OrderStatus::PENDING, OrderStatus::PAID], $order->statusHistories->pluck('to_status')->all());
        $this->assertSame([null, OrderStatus::PENDING], $order->statusHistories->pluck('from_status')->all());
        $this->assertNull(session(SessionCart::SESSION_KEY));
        $this->assertNull(session(GetCheckoutTokenAction::SESSION_KEY));
        $this->assertGuest();
    }

    public function test_item_snapshots_preserve_variant_sku_and_effective_promotional_price(): void
    {
        $product = Product::factory()->create(['name' => 'Tinta Plastisol 1 kg', 'sku' => 'TINTA-01', 'price' => 5000, 'promotional_price' => 2990]);
        $variant = $product->variants()->create(['name' => 'Cor', 'value' => 'Vermelho']);
        app(AddCartItemAction::class)->execute($product->id, $variant->id, 3);
        $order = $this->create();
        $item = $order->items->sole();
        $this->assertSame($product->id, $item->product_id);
        $this->assertSame($variant->id, $item->product_variant_id);
        $this->assertSame('Tinta Plastisol 1 kg', $item->product_name);
        $this->assertSame('TINTA-01', $item->product_sku);
        $this->assertSame('Cor', $item->variant_name);
        $this->assertSame('Vermelho', $item->variant_value);
        $this->assertSame(5000, $item->regular_unit_price);
        $this->assertSame(2990, $item->unit_price);
        $this->assertSame(3, $item->quantity);
        $this->assertSame(8970, $item->total);
        $this->assertSame(13960, $order->total_cents);

        $product->update(['name' => 'Nome alterado', 'sku' => 'OUTRO', 'price' => 99999, 'promotional_price' => null]);
        $variant->update(['value' => 'Azul']);
        $this->get(route('store.order.confirmation', $order->number))->assertOk()
            ->assertSee('Tinta Plastisol 1 kg')->assertSee('Cor: Vermelho')->assertSee('TINTA-01')
            ->assertSee('R$ 29,90')->assertSee('R$ 139,60')->assertDontSee('Nome alterado');
        $this->assertSame(2990, $item->fresh()->unit_price);
    }

    public function test_catalog_deletion_preserves_order_history_and_line_snapshots(): void
    {
        $product = Product::factory()->create(['name' => 'Produto histórico', 'price' => 1999]);
        $variant = $product->variants()->create(['name' => 'Cor', 'value' => 'Branco']);
        app(AddCartItemAction::class)->execute($product->id, $variant->id);
        $order = $this->create();
        $product->delete();
        $item = $order->items()->sole();
        $this->assertNull($item->product_id);
        $this->assertNull($item->product_variant_id);
        $this->assertSame('Produto histórico', $item->product_name);
        $this->assertSame('Branco', $item->variant_value);
        $this->get(route('store.order.confirmation', $order->number))->assertOk()->assertSee('Produto histórico')->assertSee('Cor: Branco');
    }

    public function test_checkout_recalculates_prices_after_page_mount_and_ignores_browser_totals(): void
    {
        $product = Product::factory()->create(['price' => 10000]);
        app(AddCartItemAction::class)->execute($product->id, quantity: 2);
        $component = Livewire::test(CheckoutPage::class);
        $product->update(['price' => 20000, 'promotional_price' => 15000]);
        $component->set('form', $this->input([
            'price' => 1, 'subtotal' => 2, 'shipping' => 0, 'total' => 2, 'discount' => 999999,
            'items' => [['product_name' => 'Falso', 'unit_price' => 1]],
        ]))->call('submit')->assertHasNoErrors();
        $order = Order::query()->sole();
        $this->assertSame(30000, $order->subtotal_cents);
        $this->assertSame(4990, $order->shipping_cents);
        $this->assertSame(0, $order->discount_cents);
        $this->assertSame(34990, $order->total_cents);
        $this->assertSame(15000, $order->items->sole()->unit_price);
        $this->assertSame($product->name, $order->items->sole()->product_name);
    }

    public function test_pickup_is_free_and_keeps_delivery_snapshot(): void
    {
        app(AddCartItemAction::class)->execute(Product::factory()->create(['price' => 999])->id, quantity: 3);
        $order = $this->create(['shipping_option' => 'pickup', 'document' => '11.222.333/0001-81', 'complement' => '']);
        $this->assertSame(0, $order->shipping_cents);
        $this->assertSame(2997, $order->total_cents);
        $this->assertSame('Retirada / combinar com a loja', $order->shipping_name);
        $this->assertSame('11222333000181', $order->customer_document);
        $this->assertNull($order->address->complement);
    }

    public function test_shipping_choice_updates_livewire_summary_using_backend_options(): void
    {
        app(AddCartItemAction::class)->execute(Product::factory()->create(['price' => 1000])->id);
        $component = Livewire::test(CheckoutPage::class)->assertSee('R$ 59,90');
        $component->set('form.shipping_option', 'pickup')->assertSee('R$ 10,00')->assertDontSee('R$ 59,90');
        $summary = app(GetCheckoutSummaryAction::class)->execute('13000-000', 'pickup');
        $this->assertSame(0, $summary['totals']->shipping);
        $this->assertSame(1000, $summary['totals']->total);
    }

    public function test_double_submit_returns_same_order_and_charges_only_once(): void
    {
        app(AddCartItemAction::class)->execute(Product::factory()->create()->id);
        $gateway = $this->mock(PaymentGateway::class);
        $gateway->shouldReceive('createPayment')->once()->andReturnUsing(fn (PaymentRequest $request): PaymentResult => new PaymentResult('fake', 'fake-'.$request->idempotencyKey, PaymentStatus::APPROVED, true));
        $component = Livewire::test(CheckoutPage::class)->set('form', $this->input());
        $component->call('submit')->assertHasNoErrors();
        $first = Order::query()->sole();
        $component->call('submit')->assertHasNoErrors()->assertRedirect(route('store.order.confirmation', $first->number));
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('order_addresses', 1);
        $this->assertDatabaseCount('order_status_histories', 2);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_replay_of_old_token_does_not_clear_a_new_cart_and_numbers_are_unique(): void
    {
        $product = Product::factory()->create();
        app(AddCartItemAction::class)->execute($product->id);
        $token = app(GetCheckoutTokenAction::class)->execute();
        $first = app(CreateInitialOrderAction::class)->execute($this->input(), $token);
        app(AddCartItemAction::class)->execute($product->id, quantity: 2);
        $newToken = app(GetCheckoutTokenAction::class)->execute();
        $before = session(SessionCart::SESSION_KEY);
        $replay = app(CreateInitialOrderAction::class)->execute([], $token);
        $this->assertSame($first->id, $replay->id);
        $this->assertSame($before, session(SessionCart::SESSION_KEY));
        $this->assertSame($newToken, session(GetCheckoutTokenAction::SESSION_KEY));
        $second = $this->create();
        $this->assertSame('SABA-000001', $first->number);
        $this->assertSame('SABA-000002', $second->number);
        $this->assertNotSame($first->checkout_token, $second->checkout_token);
    }

    public function test_checkout_tabs_reuse_the_same_server_token(): void
    {
        app(AddCartItemAction::class)->execute(Product::factory()->create()->id);
        $first = Livewire::test(CheckoutPage::class);
        $second = Livewire::test(CheckoutPage::class);
        $this->assertSame($first->get('checkoutToken'), $second->get('checkoutToken'));
    }

    public function test_browser_cannot_replace_locked_checkout_token(): void
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(CheckoutPage::class)->set('checkoutToken', (string) Str::uuid());
    }

    public function test_empty_cart_does_not_create_order_or_payment(): void
    {
        Livewire::test(CheckoutPage::class)->set('form', $this->input())->call('submit')->assertHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    public static function invalidFields(): array
    {
        return [
            'name' => ['name', ''], 'email' => ['email', 'invalid'], 'phone' => ['phone', '123'],
            'document' => ['document', '123'], 'postal code' => ['postal_code', '123'], 'street' => ['street', ''],
            'number' => ['number', ''], 'neighborhood' => ['neighborhood', ''], 'city' => ['city', ''],
            'state' => ['state', 'XX'], 'shipping choice' => ['shipping_option', 'forged-free-delivery'],
        ];
    }

    #[DataProvider('invalidFields')]
    public function test_invalid_input_is_rejected_without_creating_order(string $field, string $value): void
    {
        app(AddCartItemAction::class)->execute(Product::factory()->create()->id);
        Livewire::test(CheckoutPage::class)->set('form', $this->input([$field => $value]))->call('submit')->assertHasErrors($field);
        $this->assertDatabaseCount('orders', 0);
        $this->assertNotNull(session(SessionCart::SESSION_KEY));
    }

    public function test_unavailable_cart_item_is_rejected_and_cart_is_not_cleared(): void
    {
        $product = Product::factory()->create();
        app(AddCartItemAction::class)->execute($product->id);
        $product->update(['availability' => 'unavailable']);
        $before = session(SessionCart::SESSION_KEY);
        try {
            $this->create();
            $this->fail('Produto indisponível deveria impedir a criação.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('cart', $exception->errors());
            $this->assertSame($before, session(SessionCart::SESSION_KEY));
            $this->assertDatabaseCount('orders', 0);
        }
    }

    public function test_payment_failure_rolls_back_all_order_records_and_preserves_cart_for_retry(): void
    {
        app(AddCartItemAction::class)->execute(Product::factory()->create()->id);
        $before = session(SessionCart::SESSION_KEY);
        $token = app(GetCheckoutTokenAction::class)->execute();
        $this->mock(PaymentGateway::class)->shouldReceive('createPayment')->once()->andThrow(new RuntimeException('Fake failure'));
        try {
            app(CreateInitialOrderAction::class)->execute($this->input(), $token);
            $this->fail('Falha no gateway deveria reverter a transação.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Fake failure', $exception->getMessage());
        }
        foreach (['orders', 'order_items', 'order_addresses', 'order_status_histories', 'payments'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
        $this->assertSame($before, session(SessionCart::SESSION_KEY));
        $this->assertSame($token, session(GetCheckoutTokenAction::SESSION_KEY));
    }

    public function test_unapproved_gateway_result_rolls_back_and_preserves_cart(): void
    {
        app(AddCartItemAction::class)->execute(Product::factory()->create()->id);
        $this->mock(PaymentGateway::class)->shouldReceive('createPayment')->once()
            ->andReturn(new PaymentResult('fake', 'failed-test', PaymentStatus::FAILED, true));
        Livewire::test(CheckoutPage::class)->set('form', $this->input())->call('submit')->assertHasErrors('payment');
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertNotNull(session(SessionCart::SESSION_KEY));
    }

    public function test_shipping_failure_disables_checkout_without_falling_back_to_free_delivery(): void
    {
        app(AddCartItemAction::class)->execute(Product::factory()->create()->id);
        $this->mock(ShippingProvider::class)->shouldReceive('quote')->andThrow(new ShippingQuoteFailed);
        Livewire::test(CheckoutPage::class)->set('form', $this->input())->call('submit')->assertHasErrors('shipping_option')
            ->assertSee('Frete sob consulta.');
        $this->assertDatabaseCount('orders', 0);
        $this->assertNotNull(session(SessionCart::SESSION_KEY));
    }

    public function test_provider_receives_catalog_dimensions_and_quantities(): void
    {
        $product = Product::factory()->create(['weight' => '1.250', 'height' => '10.00', 'width' => '20.00', 'length' => '30.00']);
        app(AddCartItemAction::class)->execute($product->id, quantity: 2);
        $this->mock(ShippingProvider::class)->shouldReceive('quote')->once()
            ->withArgs(function (ShippingQuoteRequest $request) use ($product): bool {
                $this->assertSame('13000000', $request->postalCode);
                $this->assertSame($product->id, $request->items[0]->productId);
                $this->assertSame(2, $request->items[0]->quantity);
                $this->assertSame('1.250', $request->items[0]->weight);
                $this->assertSame('30.00', $request->items[0]->length);

                return true;
            })->andReturn(app(FakeShippingProvider::class)->quote(new ShippingQuoteRequest('', [])));
        $order = $this->create();
        $this->assertSame(4990, $order->shipping_cents);
    }

    public function test_confirmation_requires_originating_session_and_uses_no_store_header(): void
    {
        app(AddCartItemAction::class)->execute(Product::factory()->create()->id);
        $order = $this->create();
        $url = route('store.order.confirmation', $order->number);
        $response = $this->get($url)->assertOk()->assertSee('Pedido realizado com sucesso')->assertSee($order->number)
            ->assertSee('Cliente de teste')->assertSee('Rua de Teste')->assertSee('Entrega padrão')
            ->assertSee('Aprovado — ambiente de demonstração');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->withCookie(config('session.cookie'), Str::random(40));
        $this->get($url)->assertNotFound()->assertDontSee('cliente@example.com');
    }

    public function test_visible_simulation_notice_is_limited_to_local_or_development(): void
    {
        app(AddCartItemAction::class)->execute(Product::factory()->create()->id);
        $order = $this->create();
        foreach (['local', 'development', 'testing', 'production'] as $environment) {
            $this->app->instance('env', $environment);
            $response = $this->get(route('store.order.confirmation', $order->number))->assertOk();
            if (in_array($environment, ['local', 'development'], true)) {
                $response->assertSee('Este pedido utilizou pagamento simulado.');
            } else {
                $response->assertDontSee('Este pedido utilizou pagamento simulado.');
            }
        }
    }

    public function test_shipping_addition_cannot_overflow_integer_money(): void
    {
        app(AddCartItemAction::class)->execute(Product::factory()->create(['price' => PHP_INT_MAX])->id);
        Livewire::test(CheckoutPage::class)->assertSee('O valor do pedido excede o limite permitido.')
            ->set('form', $this->input())->call('submit')->assertHasErrors('checkout');
        $this->assertDatabaseCount('orders', 0);
        $this->assertNotNull(session(SessionCart::SESSION_KEY));
    }

    public function test_validation_failure_can_be_corrected_and_retried_with_the_same_token(): void
    {
        app(AddCartItemAction::class)->execute(Product::factory()->create()->id);
        $component = Livewire::test(CheckoutPage::class)->set('form', $this->input(['email' => 'invalid']));
        $token = $component->get('checkoutToken');
        $component->call('submit')->assertHasErrors('email');
        $this->assertSame($token, session(GetCheckoutTokenAction::SESSION_KEY));
        $component->set('form.email', 'cliente@example.com')->call('submit')->assertHasNoErrors();
        $this->assertDatabaseCount('orders', 1);
        $this->assertSame($token, Order::query()->sole()->checkout_token);
    }

    public function test_forged_token_is_rejected_before_order_creation(): void
    {
        app(AddCartItemAction::class)->execute(Product::factory()->create()->id);
        app(GetCheckoutTokenAction::class)->execute();
        try {
            app(CreateInitialOrderAction::class)->execute($this->input(), (string) Str::uuid());
            $this->fail('O token precisa ser o token criado para a sessão.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('checkout', $exception->errors());
            $this->assertDatabaseCount('orders', 0);
        }
    }

    public function test_database_uniquely_constrains_order_number_and_idempotency_token(): void
    {
        $product = Product::factory()->create();
        app(AddCartItemAction::class)->execute($product->id);
        $first = $this->create();
        app(AddCartItemAction::class)->execute($product->id);
        $second = $this->create();
        try {
            $second->update(['checkout_token' => $first->checkout_token]);
            $this->fail('O token deve ter restrição única no banco.');
        } catch (UniqueConstraintViolationException) {
            $this->assertDatabaseCount('orders', 2);
        }
        $this->expectException(UniqueConstraintViolationException::class);
        $second->update(['number' => $first->number]);
    }
}

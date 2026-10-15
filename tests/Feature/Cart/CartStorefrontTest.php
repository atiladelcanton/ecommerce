<?php

declare(strict_types=1);

namespace Tests\Feature\Cart;

use App\Actions\Cart\AddCartItemAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImage;
use App\Infrastructure\Cart\SessionCart;
use App\Livewire\Store\CartBadge;
use App\Livewire\Store\CartPage;
use App\Livewire\Store\ProductPurchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Exceptions\PublicPropertyNotFoundException;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\Support\UsesDemoShipping;
use Tests\TestCase;

final class CartStorefrontTest extends TestCase
{
    use RefreshDatabase;
    use UsesDemoShipping;

    public function test_cart_page_is_public_and_has_empty_state_and_products_link(): void
    {
        $this->get('/carrinho')->assertOk()->assertSee('Seu carrinho está vazio.')
            ->assertSee('Ver produtos')->assertSee(route('store.products'))
            ->assertSee('data-cart-count="0"', false)->assertDontSee('Continuar para o checkout');
        $this->assertGuest();
        $this->get('/checkout')->assertOk()->assertSee('Seu carrinho está vazio.');
    }

    public function test_guest_adds_product_via_livewire_and_cart_survives_navigation_and_new_component(): void
    {
        $product = Product::factory()->create(['price' => 1999]);
        Livewire::test(ProductPurchase::class, ['productId' => $product->id])
            ->call('add')->assertHasNoErrors()->assertSet('added', true)
            ->assertDispatched('cart-updated')->assertDispatched('toast', type: 'success')
            ->assertDontSee('Produto adicionado ao carrinho.');

        $this->get('/produtos')->assertOk()->assertSee('data-cart-count="1"', false);
        $this->get('/carrinho')->assertOk()->assertSee($product->name)->assertSee('R$ 19,99')
            ->assertSessionHas(SessionCart::SESSION_KEY, [
                $product->id.':none' => ['product_id' => $product->id, 'product_variant_id' => null, 'quantity' => 1],
            ]);
        Livewire::test(CartPage::class)->assertSee($product->name)->assertSee('R$ 19,99');
        $this->assertGuest();
    }

    public function test_product_requires_color_then_adds_selected_variant_via_livewire(): void
    {
        $product = Product::factory()->create();
        $variant = $product->variants()->create(['name' => 'Cor', 'value' => 'Vermelho']);
        $purchase = Livewire::test(ProductPurchase::class, ['productId' => $product->id]);
        $purchase->assertSet('productVariantId', null)->call('add')
            ->assertHasErrors('productVariantId')->assertNotDispatched('cart-updated');
        $this->assertNull(session(SessionCart::SESSION_KEY));

        $purchase->set('productVariantId', $variant->id)->call('add')
            ->assertHasNoErrors()->assertDispatched('cart-updated');
        $this->get('/carrinho')->assertOk()->assertSee('Cor: Vermelho');
    }

    public function test_page_shows_primary_image_current_price_quantity_summary_and_links(): void
    {
        $product = Product::factory()->create(['name' => 'Máquina SABA', 'price' => 19990, 'promotional_price' => 14990]);
        ProductImage::factory()->for($product)->create(['path' => 'products/primary.jpg', 'is_primary' => true]);
        app(AddCartItemAction::class)->execute($product->id, quantity: 3);
        $this->get('/carrinho')->assertOk()->assertSee('products/primary.jpg', false)
            ->assertSee('Máquina SABA')->assertSee('R$ 149,90')->assertSee('R$ 449,70')
            ->assertSeeInOrder(['Produto', 'Preço', 'Quantidade', 'Subtotal', 'Remover'])
            ->assertSee('Resumo do carrinho')->assertSee('Calculado no checkout')->assertSee('Total parcial')
            ->assertSee('Continuar para o checkout')->assertSee(url('/checkout'))
            ->assertSee('Continuar comprando')->assertSee('Limpar carrinho')
            ->assertSee('data-cart-count="3"', false);
    }

    public function test_livewire_quantity_controls_remove_and_clear_update_session_and_emit_badge_event(): void
    {
        $product = Product::factory()->create(['price' => 1999]);
        $other = Product::factory()->create(['price' => 1000]);
        app(AddCartItemAction::class)->execute($product->id);
        app(AddCartItemAction::class)->execute($other->id);
        $page = Livewire::test(CartPage::class);
        $page->call('changeQuantity', $product->id.':none', 1)->assertHasNoErrors()
            ->assertSee('R$ 49,98')->assertDispatched('cart-updated');
        $this->assertSame(2, session(SessionCart::SESSION_KEY)[$product->id.':none']['quantity']);
        $page->call('changeQuantity', $product->id.':none', -1)->assertSee('R$ 29,99');
        $page->call('remove', $product->id.':none')->assertDontSee($product->name)
            ->assertSee($other->name)->assertDispatched('cart-updated');
        $page->call('clear')->assertSee('Seu carrinho está vazio.')->assertDispatched('cart-updated');
        $this->assertNull(session(SessionCart::SESSION_KEY));
    }

    public function test_decrement_at_one_and_invalid_adjustments_cannot_remove_items_or_set_negative_quantity(): void
    {
        $product = Product::factory()->create();
        app(AddCartItemAction::class)->execute($product->id);
        $page = Livewire::test(CartPage::class);
        $page->call('changeQuantity', $product->id.':none', -1)->assertHasErrors('quantity');
        $this->assertSame(1, session(SessionCart::SESSION_KEY)[$product->id.':none']['quantity']);
        $page->call('changeQuantity', $product->id.':none', -100)->assertHasErrors('quantity');
        $this->assertSame(1, session(SessionCart::SESSION_KEY)[$product->id.':none']['quantity']);
        $page->call('changeQuantity', 'invalid-item', 1)->assertHasErrors('quantity');
    }

    public function test_badge_counts_units_and_refreshes_on_each_cart_event(): void
    {
        $product = Product::factory()->create();
        $badge = Livewire::test(CartBadge::class)->assertSee('data-cart-count="0"', false);
        $purchase = Livewire::test(ProductPurchase::class, ['productId' => $product->id])->call('add');
        $purchase->assertDispatched('cart-updated');
        $badge->dispatch('cart-updated')->assertSee('data-cart-count="1"', false);
        $page = Livewire::test(CartPage::class)->call('changeQuantity', $product->id.':none', 1);
        $page->assertDispatched('cart-updated');
        $badge->dispatch('cart-updated')->assertSee('data-cart-count="2"', false)
            ->assertSee('Carrinho, 2 itens')->assertSee(route('store.cart'));
        $page->call('remove', $product->id.':none')->assertDispatched('cart-updated');
        $badge->dispatch('cart-updated')->assertSee('data-cart-count="0"', false);
    }

    public function test_client_cannot_inject_price_into_purchase_component(): void
    {
        $product = Product::factory()->create(['price' => 9900]);
        $this->expectException(PublicPropertyNotFoundException::class);
        Livewire::test(ProductPurchase::class, ['productId' => $product->id])->set('price', 1);
    }

    public function test_client_cannot_change_locked_product_identity(): void
    {
        [$product, $other] = Product::factory()->count(2)->create()->all();
        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(ProductPurchase::class, ['productId' => $product->id])->set('productId', $other->id);
    }

    public function test_product_detail_exposes_purchase_button_without_login(): void
    {
        $product = Product::factory()->create();
        $this->get(route('store.product', $product->slug))->assertOk()
            ->assertSee('Adicionar ao carrinho')->assertSee('wire:click="add"', false);
    }
}

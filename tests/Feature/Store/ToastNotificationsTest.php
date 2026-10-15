<?php

declare(strict_types=1);

namespace Tests\Feature\Store;

use App\Domain\Catalog\Enums\ProductAvailability;
use App\Domain\Catalog\Models\Product;
use App\Infrastructure\Cart\SessionCart;
use App\Livewire\Store\ProductPurchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ToastNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_success_dispatches_product_name_and_cart_action_without_inline_message(): void
    {
        $product = Product::factory()->create(['name' => 'Mesa Corrida SABA 8 Berços']);
        Livewire::test(ProductPurchase::class, ['productId' => $product->id])
            ->call('add')->assertHasNoErrors()
            ->assertDispatched('toast',
                type: 'success',
                title: 'Produto adicionado',
                message: 'Mesa Corrida SABA 8 Berços foi adicionado ao carrinho.',
                actionLabel: 'Ver carrinho',
                actionUrl: '/carrinho',
            )->assertDispatched('cart-updated')->assertDontSee('Produto adicionado ao carrinho.');
    }

    public function test_success_message_includes_selected_variant_from_catalog(): void
    {
        $product = Product::factory()->create(['name' => 'Tinta Plastisol 1 kg']);
        $variant = $product->variants()->create(['name' => 'Cor', 'value' => 'Vermelho']);
        Livewire::test(ProductPurchase::class, ['productId' => $product->id])
            ->set('productVariantId', $variant->id)->call('add')->assertHasNoErrors()
            ->assertDispatched('toast',
                type: 'success',
                title: 'Produto adicionado',
                message: 'Tinta Plastisol 1 kg — Cor: Vermelho foi adicionado ao carrinho.',
                actionLabel: 'Ver carrinho',
                actionUrl: '/carrinho',
            );
    }

    public function test_missing_color_dispatches_warning_and_preserves_existing_validation(): void
    {
        $product = Product::factory()->create();
        $product->variants()->create(['name' => 'Cor', 'value' => 'Vermelho']);
        Livewire::test(ProductPurchase::class, ['productId' => $product->id])
            ->call('add')->assertHasErrors('productVariantId')->assertSet('added', false)
            ->assertDispatched('toast',
                type: 'warning',
                title: 'Selecione uma opção',
                message: 'Escolha a cor antes de adicionar o produto ao carrinho.',
            )->assertNotDispatched('cart-updated');
        $this->assertNull(session(SessionCart::SESSION_KEY));
    }

    public function test_invalid_color_uses_existing_field_error_in_warning(): void
    {
        $product = Product::factory()->create();
        $variant = $product->variants()->create(['name' => 'Cor', 'value' => 'Branco', 'is_active' => false]);
        Livewire::test(ProductPurchase::class, ['productId' => $product->id])
            ->set('productVariantId', $variant->id)->call('add')->assertHasErrors('productVariantId')
            ->assertDispatched('toast',
                type: 'warning',
                title: 'Selecione uma opção',
                message: 'Selecione uma cor disponível deste produto.',
            )->assertNotDispatched('cart-updated');
    }

    public function test_other_validation_failures_dispatch_error_without_changing_cart(): void
    {
        $product = Product::factory()->create(['availability' => ProductAvailability::UNAVAILABLE]);
        Livewire::test(ProductPurchase::class, ['productId' => $product->id])
            ->call('add')->assertHasErrors('product')
            ->assertDispatched('toast',
                type: 'error',
                title: 'Não foi possível adicionar',
                message: 'Este produto não está disponível para compra.',
            )->assertNotDispatched('cart-updated');
        $this->assertNull(session(SessionCart::SESSION_KEY));
    }

    public function test_global_container_has_live_regions_and_accessible_close_control(): void
    {
        foreach (['/', '/produtos', '/carrinho', '/quem-somos', '/contato'] as $path) {
            $this->get($path)->assertOk()->assertSee('data-store-toasts', false)
                ->assertSee('@toast.window="notify($event.detail)"', false)
                ->assertSee('aria-live="polite"', false)->assertSee('aria-live="assertive"', false)
                ->assertSee('aria-label="Fechar notificação"', false);
        }
    }
}

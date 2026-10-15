<?php

declare(strict_types=1);

namespace Tests\Feature\Cart;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Cart\ClearCartAction;
use App\Actions\Cart\GetCartAction;
use App\Actions\Cart\RemoveCartItemAction;
use App\Actions\Cart\UpdateCartQuantityAction;
use App\Domain\Catalog\Enums\ProductAvailability;
use App\Domain\Catalog\Models\Product;
use App\Infrastructure\Cart\SessionCart;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class SessionCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_adds_product_and_only_identifiers_and_quantity_are_stored(): void
    {
        $product = Product::factory()->create();
        app(AddCartItemAction::class)->execute($product->id, quantity: 2);

        $this->assertGuest();
        $this->assertSame([
            $product->id.':none' => ['product_id' => $product->id, 'product_variant_id' => null, 'quantity' => 2],
        ], session(SessionCart::SESSION_KEY));
        $this->assertSame(2, app(GetCartAction::class)->execute()['count']);
    }

    public function test_repeated_additions_merge_same_product_and_variant_but_keep_colors_separate(): void
    {
        $product = Product::factory()->create();
        $red = $product->variants()->create(['name' => 'Cor', 'value' => 'Vermelho']);
        $blue = $product->variants()->create(['name' => 'Cor', 'value' => 'Azul']);
        $add = app(AddCartItemAction::class);
        $add->execute($product->id, $red->id, 2);
        $add->execute($product->id, $red->id);
        $add->execute($product->id, $blue->id);

        $cart = app(GetCartAction::class)->execute();
        $this->assertCount(2, $cart['items']);
        $this->assertSame(4, $cart['count']);
        $this->assertSame(3, $cart['items'][$product->id.':'.$red->id]['item']->quantity);
        $this->assertSame('Vermelho', $cart['items'][$product->id.':'.$red->id]['variant']->value);
        $this->assertSame($red->id, session(SessionCart::SESSION_KEY)[$product->id.':'.$red->id]['product_variant_id']);
    }

    public static function invalidVariants(): array
    {
        return ['missing' => ['missing'], 'foreign product' => ['foreign'], 'inactive' => ['inactive'], 'all inactive' => ['all-inactive']];
    }

    #[DataProvider('invalidVariants')]
    public function test_variant_selection_must_be_active_and_belong_to_the_product(string $scenario): void
    {
        $product = Product::factory()->create();
        $product->variants()->create(['name' => 'Cor', 'value' => 'Branco', 'is_active' => $scenario !== 'all-inactive']);
        $variantId = match ($scenario) {
            'foreign' => Product::factory()->create()->variants()->create(['name' => 'Cor', 'value' => 'Branco'])->id,
            'inactive' => $product->variants()->create(['name' => 'Cor', 'value' => 'Preto', 'is_active' => false])->id,
            default => null,
        };

        try {
            app(AddCartItemAction::class)->execute($product->id, $variantId);
            $this->fail('A seleção inválida deveria ser rejeitada.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('productVariantId', $exception->errors());
            $this->assertNull(session(SessionCart::SESSION_KEY));
        }
    }

    public function test_product_without_colors_rejects_an_arbitrary_variant(): void
    {
        $product = Product::factory()->create();
        $this->expectException(ValidationException::class);
        app(AddCartItemAction::class)->execute($product->id, 999999);
    }

    public function test_changes_quantity_and_removes_only_the_selected_line(): void
    {
        [$product, $other] = Product::factory()->count(2)->create()->all();
        app(AddCartItemAction::class)->execute($product->id);
        app(AddCartItemAction::class)->execute($other->id, quantity: 2);
        app(UpdateCartQuantityAction::class)->execute($product->id.':none', 4);
        $this->assertSame(6, app(GetCartAction::class)->execute()['count']);
        app(RemoveCartItemAction::class)->execute($product->id.':none');
        $cart = app(GetCartAction::class)->execute();
        $this->assertCount(1, $cart['items']);
        $this->assertSame(2, $cart['count']);
        $this->assertArrayHasKey($other->id.':none', $cart['items']);
    }

    public static function invalidQuantities(): array
    {
        return ['zero' => [0], 'negative' => [-1], 'above limit' => [1000]];
    }

    #[DataProvider('invalidQuantities')]
    public function test_add_and_update_reject_invalid_quantities_without_changing_session(int $quantity): void
    {
        $product = Product::factory()->create();
        app(AddCartItemAction::class)->execute($product->id);
        foreach ([AddCartItemAction::class, UpdateCartQuantityAction::class] as $action) {
            try {
                if ($action === AddCartItemAction::class) {
                    app($action)->execute($product->id, quantity: $quantity);
                } else {
                    app($action)->execute($product->id.':none', $quantity);
                }
                $this->fail('A quantidade inválida deveria ser rejeitada.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('quantity', $exception->errors());
                $this->assertSame(1, app(GetCartAction::class)->execute()['count']);
            }
        }
    }

    public function test_repeated_addition_cannot_exceed_quantity_limit(): void
    {
        $product = Product::factory()->create(['stock' => null]);
        app(AddCartItemAction::class)->execute($product->id, quantity: 999);
        try {
            app(AddCartItemAction::class)->execute($product->id);
            $this->fail('O limite deveria ser aplicado à quantidade acumulada.');
        } catch (ValidationException) {
            $this->assertSame(999, app(GetCartAction::class)->execute()['count']);
        }
    }

    public function test_subtotal_uses_integer_cents_and_effective_promotional_price(): void
    {
        $regular = Product::factory()->create(['price' => 1999]);
        $promotion = Product::factory()->create(['price' => 5000, 'promotional_price' => 2990]);
        app(AddCartItemAction::class)->execute($regular->id, quantity: 3);
        app(AddCartItemAction::class)->execute($promotion->id, quantity: 2);
        $cart = app(GetCartAction::class)->execute();

        $this->assertSame(11977, $cart['subtotal']);
        $this->assertSame(2990, $cart['items'][$promotion->id.':none']['unitPrice']);
        $this->assertSame(5980, $cart['items'][$promotion->id.':none']['subtotal']);
        $this->assertSame(5, $cart['count']);
    }

    public function test_prices_names_and_totals_from_untrusted_data_never_override_catalog(): void
    {
        $product = Product::factory()->create(['name' => 'Produto real', 'price' => 10000, 'promotional_price' => 8000]);
        $this->withSession([SessionCart::SESSION_KEY => [$product->id.':none' => [
            'product_id' => $product->id, 'product_variant_id' => null, 'quantity' => 2,
            'price' => 1, 'name' => 'Nome falso', 'total' => 2,
        ]]]);
        $cart = app(GetCartAction::class)->execute();
        $this->assertSame(16000, $cart['subtotal']);
        $this->assertSame('Produto real', $cart['items'][$product->id.':none']['product']->name);
        $this->assertArrayNotHasKey('cost_price', $cart['items'][$product->id.':none']['product']->getAttributes());
        $this->get('/carrinho?price=1&name=Nome%20falso&total=2')->assertOk()
            ->assertSee('Produto real')->assertSee('R$ 160,00')->assertDontSee('Nome falso');
    }

    public function test_cart_recalculates_current_catalog_prices_without_snapshotting_an_order(): void
    {
        $product = Product::factory()->create(['price' => 10000]);
        app(AddCartItemAction::class)->execute($product->id, quantity: 2);
        $product->update(['price' => 12000, 'promotional_price' => 9900]);
        $this->assertSame(19800, app(GetCartAction::class)->execute()['subtotal']);
    }

    public function test_line_subtotal_cannot_overflow_or_convert_money_to_float(): void
    {
        $product = Product::factory()->create(['price' => PHP_INT_MAX]);
        app(AddCartItemAction::class)->execute($product->id);
        try {
            app(UpdateCartQuantityAction::class)->execute($product->id.':none', 2);
            $this->fail('Um subtotal acima do limite inteiro deveria ser rejeitado.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('quantity', $exception->errors());
            $this->assertSame(1, app(GetCartAction::class)->execute()['count']);
            $this->assertSame(PHP_INT_MAX, app(GetCartAction::class)->execute()['subtotal']);
        }
    }

    public function test_total_overflow_rejects_addition_without_changing_session(): void
    {
        [$product, $other] = Product::factory()->count(2)->create(['price' => PHP_INT_MAX])->all();
        app(AddCartItemAction::class)->execute($product->id);
        $before = session(SessionCart::SESSION_KEY);
        try {
            app(AddCartItemAction::class)->execute($other->id);
            $this->fail('Um total acima do limite inteiro deveria ser rejeitado.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('quantity', $exception->errors());
            $this->assertSame($before, session(SessionCart::SESSION_KEY));
            $this->assertSame(PHP_INT_MAX, app(GetCartAction::class)->execute()['subtotal']);
        }
    }

    public function test_clears_cart_without_clearing_other_session_data(): void
    {
        $this->withSession(['unrelated' => 'preservado']);
        app(AddCartItemAction::class)->execute(Product::factory()->create()->id);
        app(ClearCartAction::class)->execute();
        $this->assertNull(session(SessionCart::SESSION_KEY));
        $this->assertSame('preservado', session('unrelated'));
        $this->assertSame(['items' => [], 'subtotal' => 0, 'count' => 0], app(GetCartAction::class)->execute());
    }

    public function test_unavailable_products_cannot_be_added_but_made_to_order_products_can(): void
    {
        $product = Product::factory()->create(['availability' => ProductAvailability::ON_DEMAND]);
        app(AddCartItemAction::class)->execute($product->id);
        $product->update(['availability' => ProductAvailability::UNAVAILABLE]);
        $this->expectException(ValidationException::class);
        app(AddCartItemAction::class)->execute($product->id);
    }

    public function test_inactive_products_cannot_be_added(): void
    {
        $product = Product::factory()->create(['is_active' => false]);
        $this->expectException(ModelNotFoundException::class);
        app(AddCartItemAction::class)->execute($product->id);
    }

    public function test_deleted_inactive_unavailable_or_invalid_variant_items_are_pruned(): void
    {
        $products = Product::factory()->count(5)->create();
        foreach ($products as $product) {
            app(AddCartItemAction::class)->execute($product->id);
        }
        $products[0]->delete();
        $products[1]->update(['is_active' => false]);
        $products[2]->update(['availability' => ProductAvailability::UNAVAILABLE]);
        $products[3]->variants()->create(['name' => 'Cor', 'value' => 'Branco']);
        $cart = app(GetCartAction::class)->execute();
        $this->assertSame(1, $cart['count']);
        $this->assertCount(1, session(SessionCart::SESSION_KEY));
        $this->assertArrayHasKey($products[4]->id.':none', $cart['items']);
    }

    public function test_inactive_selected_variant_is_removed_from_cart(): void
    {
        $product = Product::factory()->create();
        $variant = $product->variants()->create(['name' => 'Cor', 'value' => 'Branco']);
        app(AddCartItemAction::class)->execute($product->id, $variant->id);
        $variant->update(['is_active' => false]);
        $this->assertSame(0, app(GetCartAction::class)->execute()['count']);
        $this->assertSame([], session(SessionCart::SESSION_KEY));
    }
}

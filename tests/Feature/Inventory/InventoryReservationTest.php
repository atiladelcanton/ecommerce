<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Cart\GetCartAction;
use App\Actions\Catalog\DeleteProductAction;
use App\Actions\Catalog\SyncProductVariantsAction;
use App\Actions\Catalog\UpdateProductAction;
use App\Actions\Order\CreateInitialOrderAction;
use App\Actions\Order\GetCheckoutTokenAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Shipping\Models\ShippingIntegration;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Infrastructure\Cart\SessionCart;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

final class InventoryReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_variations_sharing_parent_stock_do_not_multiply_inventory(): void
    {
        $product = Product::factory()->create(['stock' => 3]);
        $red = $product->variants()->create(['name' => 'Cor', 'value' => 'Vermelha']);
        $blue = $product->variants()->create(['name' => 'Cor', 'value' => 'Azul']);
        app(AddCartItemAction::class)->execute($product->id, $red->id, 2);
        try {
            app(AddCartItemAction::class)->execute($product->id, $blue->id, 2);
            self::fail('Expected shared stock limit');
        } catch (ValidationException) {
            $this->assertSame(2, app(GetCartAction::class)->execute()['count']);
        }
    }

    public function test_reservation_blocks_another_cart_but_does_not_remove_an_existing_line(): void
    {
        $product = Product::factory()->create(['stock' => 2]);
        app(AddCartItemAction::class)->execute($product->id, quantity: 2);
        app(CreateInitialOrderAction::class)->requestShippingQuote($this->input(), app(GetCheckoutTokenAction::class)->execute());
        // Simulate another session that already had the same item before the reservation.
        session()->put(SessionCart::SESSION_KEY, [$product->id.':none' => ['product_id' => $product->id, 'product_variant_id' => null, 'quantity' => 1]]);
        $cart = app(GetCartAction::class)->execute();
        $this->assertCount(1, $cart['items']);
        $this->assertNotEmpty($cart['stockError']);
        $this->get('/checkout')->assertOk()->assertSee('Quantidade indisponível');
    }

    public function test_stock_cannot_be_reduced_below_reserved_and_product_cannot_be_deleted(): void
    {
        $product = Product::factory()->create(['stock' => 3]);
        app(AddCartItemAction::class)->execute($product->id, quantity: 2);
        app(CreateInitialOrderAction::class)->requestShippingQuote($this->input(), app(GetCheckoutTokenAction::class)->execute());
        foreach ([fn () => app(UpdateProductAction::class)->execute($product, ['stock' => 1]), fn () => app(DeleteProductAction::class)->execute($product)] as $operation) {
            try {
                $operation();
                self::fail('Expected protected inventory');
            } catch (ValidationException) {
            }
        }
        $this->assertModelExists($product);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_variation_combination_cannot_be_changed_while_reserved(): void
    {
        $product = Product::factory()->create();
        app(SyncProductVariantsAction::class)->execute($product, [['attributes' => ['Poliéster' => '44'], 'price' => 12000, 'stock' => 3, 'is_active' => true]]);
        $variant = $product->variants()->sole();
        app(AddCartItemAction::class)->execute($product->id, $variant->id);
        app(CreateInitialOrderAction::class)->requestShippingQuote($this->input(), app(GetCheckoutTokenAction::class)->execute());
        $this->expectException(ValidationException::class);
        app(SyncProductVariantsAction::class)->execute($product, [array_merge($variant->toArray(), ['attributes' => ['Poliéster' => '77']])]);
    }

    public function test_normal_checkout_debits_selected_variation_once_and_keeps_snapshots(): void
    {
        ShippingIntegration::forProvider('local_pickup')->update(['is_enabled' => true, 'configuration' => ['address' => 'SABA']]);
        $product = Product::factory()->create(['price' => 10000]);
        app(SyncProductVariantsAction::class)->execute($product, [['attributes' => ['Cor' => 'Preta', 'Embalagem' => '900 ml'], 'sku' => 'TINTA-PRETA-900', 'price' => 4500, 'stock' => 3, 'is_active' => true]]);
        $variant = $product->variants()->sole();
        app(AddCartItemAction::class)->execute($product->id, $variant->id, 2);
        $token = app(GetCheckoutTokenAction::class)->execute();
        $order = app(CreateInitialOrderAction::class)->execute($this->input() + ['shipping_option' => 'local_pickup:pickup'], $token);
        app(CreateInitialOrderAction::class)->execute($this->input() + ['shipping_option' => 'local_pickup:pickup'], $token);
        $this->assertSame(1, $variant->fresh()->stock);
        $item = $order->items()->sole();
        $this->assertSame(4500, $item->unit_price);
        $this->assertSame('TINTA-PRETA-900', $item->product_sku);
        $this->assertSame(['Cor' => 'Preta', 'Embalagem' => '900 ml'], $item->variant_attributes_snapshot);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('inventory_reservations', 1);
    }

    public function test_variant_admin_can_save_twice_without_creating_duplicate_combinations(): void
    {
        $product = Product::factory()->create(['price' => 10000]);
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::bootCurrentPanel();
        $component = Livewire::test(EditProduct::class, ['record' => $product->id])
            ->fillForm(['variants' => [['attributes' => ['Poliéster' => '44', 'Tamanho' => '40x50'], 'price' => '120,00', 'stock' => 3, 'is_active' => true]]])
            ->call('save')->assertHasNoFormErrors();
        $id = $product->variants()->sole()->id;
        $component->call('save')->assertHasNoFormErrors();
        $this->assertDatabaseCount('product_variants', 1);
        $this->assertSame($id, $product->variants()->sole()->id);
        $this->assertSame(12000, $product->variants()->sole()->price);
    }

    public function test_variant_domain_errors_are_mapped_to_the_repeater_item(): void
    {
        $product = Product::factory()->create(['price' => 10000]);
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::bootCurrentPanel();
        $component = Livewire::test(EditProduct::class, ['record' => $product->id])
            ->fillForm(['variants' => [['attributes' => ['Poliéster' => '44'], 'price' => '120,00', 'promotional_price' => '130,00', 'is_active' => true]]]);
        $key = array_key_first($component->get('data.variants'));
        $component->call('save')->assertHasErrors(['data.variants.'.$key.'.promotional_price']);
        $this->assertDatabaseCount('product_variants', 0);
    }

    private function input(): array
    {
        return ['name' => 'Cliente', 'email' => 'cliente@example.test', 'phone' => '11999999999', 'document' => '52998224725', 'postal_code' => '13077001', 'street' => 'Rua teste', 'number' => '10', 'neighborhood' => 'Centro', 'city' => 'Campinas', 'state' => 'SP'];
    }
}

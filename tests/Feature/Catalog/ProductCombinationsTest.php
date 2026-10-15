<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Cart\GetCartAction;
use App\Actions\Catalog\ResolveProductOfferAction;
use App\Actions\Catalog\Storefront\GetProductPurchaseDataAction;
use App\Actions\Catalog\Storefront\ListProductsAction;
use App\Actions\Catalog\SyncProductVariantsAction;
use App\Domain\Catalog\Models\Product;
use App\Livewire\Store\ProductPurchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductCombinationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_combinations_have_independent_prices_stock_sku_and_cart_lines(): void
    {
        $product = Product::factory()->create(['price' => 10000]);
        app(SyncProductVariantsAction::class)->execute($product, [
            $this->row(['Poliéster' => '44', 'Tamanho' => '40x50'], 12000, 3, 'TELA-44'),
            $this->row(['Poliéster' => '77', 'Tamanho' => '40x50'], 18000, 2, 'TELA-77'),
        ]);
        [$first, $second] = $product->variants()->orderBy('id')->get()->all();
        app(AddCartItemAction::class)->execute($product->id, $first->id, 2);
        app(AddCartItemAction::class)->execute($product->id, $second->id);
        $cart = app(GetCartAction::class)->execute();
        $this->assertSame(42000, $cart['subtotal']);
        $this->assertSame('TELA-77', $cart['items'][$product->id.':'.$second->id]['offer']->sku);
        $this->assertCount(2, $cart['items']);
    }

    public function test_same_combination_cannot_be_duplicated_by_reordering_or_case(): void
    {
        $product = Product::factory()->create();
        $this->expectException(ValidationException::class);
        try {
            app(SyncProductVariantsAction::class)->execute($product, [
                $this->row(['Cor' => 'Preta', 'Embalagem' => '1 kg']),
                $this->row(['embalagem' => '1 KG', 'cor' => ' PRETA ']),
            ]);
        } finally {
            $this->assertDatabaseCount('product_variants', 0);
        }
    }

    public function test_legacy_color_inherits_price_and_stock_but_override_does_not_inherit_parent_promotion(): void
    {
        $product = Product::factory()->create(['price' => 10000, 'promotional_price' => 9000]);
        $color = $product->variants()->create(['name' => 'Cor', 'value' => 'Preta']);
        $offer = app(ResolveProductOfferAction::class)->execute($product, $color);
        $this->assertSame(9000, $offer->pricing->finalPrice());
        $this->assertSame(['Cor' => 'Preta'], $offer->attributes);
        $color->update(['price' => 15000]);
        $this->assertSame(15000, app(ResolveProductOfferAction::class)->execute($product, $color->fresh())->pricing->finalPrice());
    }

    public function test_invalid_variant_promotion_and_fractional_stock_are_rejected(): void
    {
        $product = Product::factory()->create();
        foreach ([['promotional_price' => 10001], ['stock' => '1.5'], ['price' => '10000']] as $invalid) {
            try {
                app(SyncProductVariantsAction::class)->execute($product, [array_merge($this->row(['Poliéster' => '44'], 10000), $invalid)]);
                self::fail('Expected invalid variation');
            } catch (ValidationException) {
            }
        }
        $this->assertDatabaseCount('product_variants', 0);
    }

    public function test_variant_measurements_override_as_complete_package_and_incomplete_override_is_rejected(): void
    {
        $product = Product::factory()->create();
        $row = array_merge($this->row(['Embalagem' => '900 ml']), ['weight' => '1.2', 'height' => '10', 'width' => '20', 'length' => '30']);
        app(SyncProductVariantsAction::class)->execute($product, [$row]);
        $offer = app(ResolveProductOfferAction::class)->execute($product, $product->variants()->sole());
        $this->assertSame('1.200', $offer->measurements['weight']);
        $this->expectException(ValidationException::class);
        app(SyncProductVariantsAction::class)->execute($product, [array_merge($this->row(['Embalagem' => '1 kg']), ['weight' => '1'])]);
    }

    public function test_selection_changes_price_and_rejects_nonexistent_combination(): void
    {
        $product = Product::factory()->create();
        app(SyncProductVariantsAction::class)->execute($product, [
            $this->row(['Poliéster' => '44', 'Tamanho' => '40x50'], 12000),
            $this->row(['Poliéster' => '77', 'Tamanho' => '60x80'], 18000),
        ]);
        Livewire::test(ProductPurchase::class, ['productId' => $product->id])
            ->set('selectedAttributes', ['77'])->set('selectedAttributes', ['77', '60x80'])->assertSee('R$ 180,00')
            ->call('add')->assertHasNoErrors();
        $data = app(GetProductPurchaseDataAction::class)->execute($product->fresh(), ['44', '60x80']);
        $this->assertNull($data['selectedVariant']);
        $this->assertFalse($data['canPurchase']);
    }

    public function test_listing_price_filter_and_metadata_use_variant_prices(): void
    {
        $product = Product::factory()->create(['price' => 100000]);
        app(SyncProductVariantsAction::class)->execute($product, [$this->row(['Poliéster' => '44'], 12000), $this->row(['Poliéster' => '77'], 18000)]);
        $results = app(ListProductsAction::class)->execute(['maximum' => '150,00', 'sort' => 'menor-preco']);
        $this->assertSame([$product->id], $results->pluck('id')->all());
        $this->get(route('store.product', $product->slug))->assertOk()->assertSee('A partir de')->assertSee('R$ 120,00')->assertSee('AggregateOffer')->assertSee('180.00');
    }

    private function row(array $attributes, int $price = 10000, ?int $stock = 5, ?string $sku = null): array
    {
        return compact('attributes', 'price', 'stock', 'sku') + ['is_active' => true];
    }
}

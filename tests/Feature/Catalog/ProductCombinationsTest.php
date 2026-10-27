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

    public function test_generic_variations_support_nylon_and_clothing_with_optional_price_override(): void
    {
        $nylon = Product::factory()->create(['price' => 5000]);
        app(SyncProductVariantsAction::class)->execute($nylon, [
            $this->row(['Cor' => 'Amarelo', 'Fios' => '77'], 5000),
            $this->row(['Cor' => 'Amarelo', 'Fios' => '90'], 6500),
        ]);

        $base = $nylon->variants()->where('value', 'Amarelo / 77')->firstOrFail();
        $override = $nylon->variants()->where('value', 'Amarelo / 90')->firstOrFail();
        $base->update(['price' => null]);

        $this->assertSame(5000, app(ResolveProductOfferAction::class)->execute($nylon, $base->fresh())->pricing->finalPrice());
        $this->assertSame(6500, app(ResolveProductOfferAction::class)->execute($nylon, $override)->pricing->finalPrice());

        $shirt = Product::factory()->create(['price' => 7990]);
        app(SyncProductVariantsAction::class)->execute($shirt, [
            $this->row(['Cor' => 'Preto', 'Tamanho' => 'M'], 7990),
            $this->row(['Cor' => 'Preto', 'Tamanho' => 'G'], 8990),
        ]);

        $data = app(GetProductPurchaseDataAction::class)->execute($shirt->fresh());
        $this->assertSame(['Cor', 'Tamanho'], array_column($data['fields'], 'name'));
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

    public function test_active_combinations_must_have_the_same_characteristics(): void
    {
        $product = Product::factory()->create();
        try {
            app(SyncProductVariantsAction::class)->execute($product, [
                $this->row(['Cor' => 'Preta', 'Embalagem' => '900 ml']),
                $this->row(['Cor' => 'Branca']),
            ]);
            self::fail('An incomplete combination must not be sold');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('variants.1.attributes', $exception->errors());
        }
        $this->assertDatabaseCount('product_variants', 0);
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

    public function test_two_attribute_product_uses_clickable_options_and_recalculates_price(): void
    {
        $product = Product::factory()->create(['price' => 15000]);
        app(SyncProductVariantsAction::class)->execute($product, [
            $this->row(['Cor' => 'Magenta', 'Embalagem' => '900ml'], 4342),
            $this->row(['Cor' => 'Cyan', 'Embalagem' => '900ml'], 5000),
        ]);

        $this->get(route('store.product', $product->slug))->assertOk()
            ->assertSee('data-color-selector', false)
            ->assertSee('wire:click="selectOption(0,', false)
            ->assertDontSee('<select', false);

        Livewire::test(ProductPurchase::class, ['productId' => $product->id])
            ->assertSee('Escolha Cor primeiro')
            ->call('selectOption', 0, 'Magenta')
            ->assertSee('900ml')
            ->call('selectOption', 1, '900ml')
            ->assertSee('R$ 43,42')
            ->assertSet('productVariantId', $product->variants()->where('value', 'Magenta / 900ml')->value('id'))
            ->call('add')
            ->assertHasNoErrors();
    }

    public function test_product_with_many_attributes_offers_one_click_complete_combinations(): void
    {
        $product = Product::factory()->create(['price' => 15000]);
        app(SyncProductVariantsAction::class)->execute($product, [
            $this->row(['Comprimento' => '100 cm', 'Cor' => 'Branco', 'Fios' => '140', 'Largura' => '110 cm', 'Material' => 'Poliéster'], 1010),
            $this->row(['Comprimento' => '100 cm', 'Cor' => 'Amarelo', 'Fios' => '120', 'Largura' => '110 cm', 'Material' => 'Poliéster'], 2000),
        ]);
        $data = app(GetProductPurchaseDataAction::class)->execute($product->fresh());
        $this->assertSame('combinations', $data['selectionMode']);
        $this->assertCount(2, $data['combinations']);
        $this->assertCount(5, $data['combinations'][0]['attributes']);

        $variantId = $product->variants()->where('price', 1010)->value('id');
        Livewire::test(ProductPurchase::class, ['productId' => $product->id])
            ->assertSee('Escolha a configuração')
            ->assertSee('selectCombination(', false)
            ->assertDontSee('Selecione uma combinação disponível.')
            ->call('selectCombination', $variantId)
            ->assertSet('productVariantId', $variantId)
            ->assertSee('R$ 10,10')
            ->call('add')
            ->assertHasNoErrors();
    }

    public function test_sold_out_choices_are_visible_but_disabled_and_cannot_be_selected(): void
    {
        $product = Product::factory()->create(['stock' => null]);
        app(SyncProductVariantsAction::class)->execute($product, [
            $this->row(['Cor' => 'Branco', 'Embalagem' => '900ml'], 4342, 0),
            $this->row(['Cor' => 'Magenta', 'Embalagem' => '900ml'], 5000, 3),
        ]);
        $data = app(GetProductPurchaseDataAction::class)->execute($product->fresh());
        $this->assertSame('attributes', $data['selectionMode']);
        $this->assertFalse($data['fields'][0]['values'][0]['available']);
        $this->assertTrue($data['fields'][0]['values'][1]['available']);

        Livewire::test(ProductPurchase::class, ['productId' => $product->id])
            ->assertSee('Opção indisponível')
            ->call('selectOption', 0, 'Branco')
            ->assertSet('selectedAttributes', [])
            ->call('selectOption', 0, 'Magenta')
            ->assertSet('selectedAttributes', [0 => 'Magenta']);
    }

    public function test_complete_variant_selection_rejects_invalid_and_sold_out_ids(): void
    {
        $product = Product::factory()->create(['stock' => null]);
        app(SyncProductVariantsAction::class)->execute($product, [
            $this->row(['Cor' => 'Branco', 'Fios' => '120', 'Material' => 'Nylon'], 1000, 0),
            $this->row(['Cor' => 'Amarelo', 'Fios' => '140', 'Material' => 'Poliéster'], 2000, 2),
        ]);
        $otherProduct = Product::factory()->create();
        $otherVariant = $otherProduct->variants()->create(['name' => 'Cor', 'value' => 'Preto']);
        $outOfStockId = $product->variants()->where('price', 1000)->value('id');

        Livewire::test(ProductPurchase::class, ['productId' => $product->id])
            ->call('selectCombination', $outOfStockId)
            ->assertSet('productVariantId', null)
            ->call('selectCombination', $otherVariant->id)
            ->assertSet('productVariantId', null)
            ->call('selectCombination', $product->variants()->where('price', 2000)->value('id'))
            ->assertSee('R$ 20,00');
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

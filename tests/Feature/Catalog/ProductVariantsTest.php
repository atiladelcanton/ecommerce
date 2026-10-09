<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Actions\Catalog\Storefront\GetProductDetailAction;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImage;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Settings\Models\StoreSetting;
use Database\Seeders\DemoCatalogSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductVariantsTest extends TestCase
{
    use RefreshDatabase;

    private const COLORS = ['Branco', 'Preto', 'Vermelho', 'Azul', 'Amarelo', 'Verde'];

    public function test_demo_has_requested_categories_and_no_separate_color_products(): void
    {
        $this->seed(DemoCatalogSeeder::class);

        $this->assertSame([
            'maquinas-de-serigrafia' => 'Máquinas de Serigrafia',
            'bercos' => 'Berços',
            'secagem-e-cura' => 'Secagem e Cura',
            'tinta-plastisol' => 'Tinta Plastisol',
            'tinta-base-agua' => 'Tinta Base Água',
            'telas-e-quadros' => 'Telas e Quadros',
            'rodos' => 'Rodos',
            'acessorios' => 'Acessórios',
        ], Category::ordered()->pluck('name', 'slug')->all());
        $this->assertFalse(Category::query()->where('name', 'like', '%Platens%')->exists());
        $this->assertFalse(Category::query()->where('slug', 'like', '%platens%')->exists());
        $this->assertDatabaseCount('products', 30);
        $this->assertSame([6, 4, 6, 1, 1, 4, 4, 4], Category::ordered()->withCount('products')->pluck('products_count')->all());
        $this->assertEqualsCanonicalizing(
            ['Tinta Plastisol 1 kg', 'Tinta Base Água 1 kg'],
            Product::query()->where('name', 'like', 'Tinta %')->pluck('name')->all(),
        );
        foreach (['tinta-plastisol-1-kg', 'tinta-base-agua-1-kg'] as $slug) {
            $product = Product::query()->where('slug', $slug)->firstOrFail();
            $this->assertSame(self::COLORS, $product->variants->pluck('value')->all());
            $this->assertSame(['Cor'], $product->variants->pluck('name')->unique()->values()->all());
            foreach ($product->variants as $variant) {
                $this->assertTrue($variant->product->is($product));
                $this->assertTrue($variant->is_active);
                $this->assertNull($variant->sku);
                $this->assertNull($variant->stock);
            }
        }
    }

    public function test_repeating_seed_preserves_ids_images_and_existing_prices(): void
    {
        $this->seed(DemoCatalogSeeder::class);
        $product = Product::query()->where('slug', 'tinta-plastisol-1-kg')->firstOrFail();
        $product->update(['price' => 9900]);
        $image = ProductImage::factory()->for($product)->create();
        $ids = ProductVariant::query()->orderBy('id')->pluck('id')->all();
        $productIds = Product::query()->orderBy('id')->pluck('id')->all();
        $categoryIds = Category::query()->orderBy('id')->pluck('id')->all();

        $this->seed(DemoCatalogSeeder::class);

        $this->assertDatabaseCount('categories', 8);
        $this->assertDatabaseCount('products', 30);
        $this->assertDatabaseCount('product_variants', 12);
        $this->assertDatabaseCount('product_images', 1);
        $this->assertSame($ids, ProductVariant::query()->orderBy('id')->pluck('id')->all());
        $this->assertSame($productIds, Product::query()->orderBy('id')->pluck('id')->all());
        $this->assertSame($categoryIds, Category::query()->orderBy('id')->pluck('id')->all());
        $this->assertSame(9900, $product->fresh()->price);
        $this->assertModelExists($image);
    }

    public function test_seed_retires_previous_demo_but_keeps_custom_products(): void
    {
        $oldCategory = Category::factory()->create(['name' => 'Secadores', 'slug' => 'secadores']);
        $oldProduct = Product::factory()->for($oldCategory, 'category')->create([
            'slug' => 'secador-saba-compacto',
            'short_description' => 'Produto fictício para demonstração local.',
        ]);
        $custom = Product::factory()->create();

        $this->seed(DemoCatalogSeeder::class);

        $this->assertModelMissing($oldProduct);
        $this->assertModelMissing($oldCategory);
        $this->assertModelExists($custom);
    }

    public function test_variant_quote_contains_color_and_requires_selection_in_detail(): void
    {
        $this->seed(DemoCatalogSeeder::class);
        StoreSetting::query()->create(['store_name' => 'SABA', 'whatsapp' => '5511999990000']);
        $data = app(GetProductDetailAction::class)->execute('tinta-plastisol-1-kg');
        $product = $data['product'];
        $red = $product->variants->firstWhere('value', 'Vermelho');

        $this->assertNull($data['quoteUrl']);
        $this->assertCount(6, $data['variantQuoteUrls']);
        parse_str(parse_url($data['variantQuoteUrls'][$red->id], PHP_URL_QUERY), $query);
        $this->assertSame(
            "Olá! Tenho interesse neste produto:\n\nTinta Plastisol 1 kg\nCor: Vermelho\n\n"
            .route('store.product', $product->slug)."\n\nGostaria de mais informações e de uma cotação.",
            $query['text'],
        );
        $this->get(route('store.product', $product->slug))->assertOk()
            ->assertSee('data-color-selector', false)
            ->assertSee('selectedColor: null', false)
            ->assertSee('aria-disabled="true"', false)
            ->assertSee(':href="quoteUrls[selectedColor] || null"', false)
            ->assertSeeInOrder(self::COLORS);
    }

    public function test_inactive_colors_do_not_appear_or_get_quote_links(): void
    {
        $product = Product::factory()->create();
        $product->variants()->create(['name' => 'Cor', 'value' => 'Cor oculta', 'is_active' => false]);
        $visible = $product->variants()->create(['name' => 'Cor', 'value' => 'Branco']);
        StoreSetting::query()->create(['store_name' => 'SABA', 'whatsapp' => '5511999990000']);

        $data = app(GetProductDetailAction::class)->execute($product->slug);
        $this->assertSame([$visible->id], array_keys($data['variantQuoteUrls']));
        $this->get(route('store.product', $product->slug))->assertOk()
            ->assertSee('Branco')->assertDontSee('Cor oculta');
    }

    public function test_product_without_variants_keeps_regular_quote_and_no_selector(): void
    {
        $product = Product::factory()->create();
        StoreSetting::query()->create(['store_name' => 'SABA', 'whatsapp' => '5511999990000']);
        $data = app(GetProductDetailAction::class)->execute($product->slug);

        $this->assertSame([], $data['variantQuoteUrls']);
        $this->assertStringNotContainsString('Cor:', rawurldecode($data['quoteUrl']));
        $this->get(route('store.product', $product->slug))->assertOk()
            ->assertDontSee('data-color-selector', false)
            ->assertSee($data['quoteUrl'])->assertSee('Cotar pelo WhatsApp');
    }

    public function test_missing_phone_or_disabled_quotes_do_not_create_variant_quote_links(): void
    {
        $product = Product::factory()->create();
        $product->variants()->create(['name' => 'Cor', 'value' => 'Branco']);
        $this->assertSame([], app(GetProductDetailAction::class)->execute($product->slug)['variantQuoteUrls']);
        $this->get(route('store.product', $product->slug))->assertOk()->assertDontSee('Cotar pelo WhatsApp');
        StoreSetting::query()->create(['store_name' => 'SABA', 'whatsapp' => '5511999990000']);
        $product->update(['whatsapp_quote_enabled' => false]);
        $this->assertSame([], app(GetProductDetailAction::class)->execute($product->slug)['variantQuoteUrls']);
    }

    public function test_deleting_product_cascades_variants(): void
    {
        $product = Product::factory()->create();
        $variant = $product->variants()->create(['name' => 'Cor', 'value' => 'Branco']);
        $product->delete();
        $this->assertModelMissing($variant);
    }

    public function test_database_rejects_duplicate_product_color(): void
    {
        $product = Product::factory()->create();
        $product->variants()->create(['name' => 'Cor', 'value' => 'Branco']);
        $this->expectException(QueryException::class);
        $product->variants()->create(['name' => 'Cor', 'value' => 'Branco']);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Actions\Catalog\ImportCatalogUrlsAction;
use App\Actions\Catalog\Storefront\GetHomeDataAction;
use App\Actions\Catalog\Storefront\GetStoreContextAction;
use App\Actions\Catalog\Storefront\PublicProducts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ImportCatalogUrlsTest extends TestCase
{
    use RefreshDatabase;

    private const URL = 'https://8.8.8.8/produto/tinta-hidrocryl-nova-911';

    private function html(string $name): string
    {
        return '<html><head><meta name="description" content="SEO da loja original"></head><body>'
            .'<h1>'.htmlspecialchars($name).'</h1>'
            .'<div class="short-description"><p>Descrição técnica da Tele Silk.</p><p>Segunda linha detalhada.</p></div>'
            .'<select name="cor"><option value="">Selecione</option><option>Azul</option></select>'
            .'</body></html>';
    }

    public function test_imports_specific_url_creates_category_and_uses_short_description_idempotently(): void
    {
        Http::fake(['https://8.8.8.8/*' => Http::response($this->html('Tinta Hidrocryl Nova'), 200)]);
        Storage::fake('public');
        $first = app(ImportCatalogUrlsAction::class)->execute([self::URL], ['no_images' => true], static function (string $text): void {});
        $second = app(ImportCatalogUrlsAction::class)->execute([self::URL], ['no_images' => true], static function (string $text): void {});

        $this->assertSame(1, $first['created']);
        $this->assertSame(1, $first['categories_created']);
        $this->assertSame(1, $second['existing']);
        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('catalog_import_sources', 1);
        $this->assertDatabaseCount('catalog_import_options', 1);
        $this->assertDatabaseHas('categories', ['slug' => 'tinta-base-agua', 'is_active' => 1]);
        $product = DB::table('products')->first();
        $this->assertStringContainsString('Descrição técnica da SABA.', $product->description);
        $this->assertStringContainsString('Segunda linha detalhada.', $product->description);
        $this->assertStringStartsWith('SABA-TA-', $product->sku);
        $this->assertSame(0, (int) $product->is_active);
        $this->assertSame(0, (int) $product->price);
        $this->assertSame(0, (int) $product->stock);
        $this->assertSame('unavailable', $product->availability);
    }

    public function test_unknown_category_and_products_are_hidden_even_if_admin_activates_them(): void
    {
        Http::fake(['https://8.8.8.8/*' => Http::response($this->html('Produto Especial Desconhecido'), 200)]);
        $result = app(ImportCatalogUrlsAction::class)->execute([self::URL], ['no_images' => true], static function (string $text): void {});
        $this->assertSame(1, $result['uncategorized']);
        $this->assertDatabaseHas('categories', ['slug' => 'sem-definicao', 'is_active' => 0]);
        DB::table('products')->update(['is_active' => true, 'price' => 1000, 'availability' => 'immediate']);
        DB::table('categories')->where('slug', 'sem-definicao')->update(['is_active' => true]);
        $this->assertSame(0, app(PublicProducts::class)->query()->count());
        $this->assertCount(0, app(GetHomeDataAction::class)->execute()['categories']);
        $this->assertCount(0, app(GetStoreContextAction::class)->execute()['navigationCategories']);
        $this->get(route('store.product', DB::table('products')->value('slug')))->assertNotFound();
    }

    public function test_dry_run_never_persists_categories_products_or_options(): void
    {
        Http::fake(['https://8.8.8.8/*' => Http::response($this->html('Sem categoria'), 200)]);
        $stats = app(ImportCatalogUrlsAction::class)->execute([self::URL], ['dry_run' => true], static function (string $text): void {});
        $this->assertSame(0, $stats['failed']);
        $this->assertDatabaseCount('categories', 0);
        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('catalog_import_sources', 0);
    }



    public function test_seeded_ink_creates_inactive_variant_drafts_and_reuses_them(): void
    {
        config()->set('saba-homolog-inks.host', 'https://8.8.8.8');
        $url = 'https://8.8.8.8/produto/tinta-hidrocryl-perolizado-249';
        Http::fake(['https://8.8.8.8/*' => Http::response($this->html('Tinta Hidrocryl Perolizado'), 200)]);
        $action = app(ImportCatalogUrlsAction::class);
        $created = $action->execute([$url], ['no_images' => true], static function (string $text): void {});
        $repeat = $action->execute([$url], ['no_images' => true], static function (string $text): void {});
        $this->assertSame(4, $created['variant_drafts']);
        $this->assertSame(0, $repeat['variant_drafts']);
        $this->assertDatabaseCount('product_variants', 4);
        $this->assertSame(0, DB::table('product_variants')->where('is_active', true)->count());
        $variant = DB::table('product_variants')->first();
        $this->assertStringStartsWith('SABA-TA-', $variant->sku);
        $this->assertNotNull($variant->combination_key);
        $this->assertSame(0, (int) $variant->stock);
    }

    public function test_explicit_new_category_is_created_and_preview_shows_product_without_enabling_checkout(): void
    {
        Http::fake(['https://8.8.8.8/*' => Http::response($this->html('Kit Especial'), 200)]);
        $stats = app(ImportCatalogUrlsAction::class)->execute([self::URL], [
            'no_images' => true, 'category' => 'Kits especiais',
            'publish_preview' => true, 'demo_price_cents' => 8900,
        ], static function (string $text): void {});
        $this->assertSame(1, $stats['preview_visible']);
        $this->assertDatabaseHas('categories', ['slug' => 'kits-especiais', 'is_active' => 1]);
        $this->assertDatabaseHas('products', ['is_active' => 1, 'price' => 8900, 'availability' => 'unavailable']);
        $this->assertSame(1, app(PublicProducts::class)->query()->count());
    }

    public function test_even_preview_cannot_publish_products_in_uncategorized(): void
    {
        Http::fake(['https://8.8.8.8/*' => Http::response($this->html('Item Sem Categoria'), 200)]);
        $stats = app(ImportCatalogUrlsAction::class)->execute([self::URL], [
            'no_images' => true, 'publish_preview' => true, 'demo_price_cents' => 8900,
        ], static function (string $text): void {});
        $this->assertSame(1, $stats['uncategorized']);
        $this->assertSame(0, $stats['preview_visible']);
        $this->assertDatabaseHas('categories', ['slug' => 'sem-definicao', 'is_active' => 0]);
        $this->assertDatabaseHas('products', ['is_active' => 0, 'price' => 0, 'availability' => 'unavailable']);
    }

    public function test_complete_variation_select_imports_individual_inactive_drafts_and_is_idempotent(): void
    {
        $html = '<html><body><h1>TECIDO</h1><div class="short-description">Tecido para tela serigráfica</div>'
            .'<select name="variacoes"><option>Selecione</option>'
            .'<option>NYLON 120 fios - branco - 100cm comprimento x 110cm</option>'
            .'<option>Poliéster 77 fios - amarelo - 100cm comprimento x 160cm</option>'
            .'</select></body></html>';
        Http::fake(['https://8.8.8.8/*' => Http::response($html, 200)]);
        $url = 'https://8.8.8.8/produto/tecido-teste-591';
        $action = app(ImportCatalogUrlsAction::class);
        $first = $action->execute([$url], ['no_images' => true], static function (string $log): void {});
        $second = $action->execute([$url], ['no_images' => true], static function (string $log): void {});

        $this->assertSame(2, $first['variant_drafts']);
        $this->assertSame(0, $second['variant_drafts']);
        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('product_variants', 2);
        $this->assertDatabaseHas('categories', ['slug' => 'telas-e-quadros']);
        $this->assertDatabaseHas('catalog_import_options', ['name' => 'Variação']);
        $rows = DB::table('product_variants')->orderBy('sort_order')->get();
        $this->assertSame('SABA-TQ-', substr($rows[0]->sku, 0, 8));
        $this->assertSame(0, (int) $rows[0]->is_active);
        $this->assertSame(0, (int) $rows[0]->stock);
        $this->assertNull($rows[0]->price);
        $this->assertSame([
            'Material' => 'Nylon', 'Fios' => '120', 'Cor' => 'Branco',
            'Comprimento' => '100 cm', 'Largura' => '110 cm',
        ], json_decode($rows[0]->attributes, true));
    }

    public function test_reimport_fills_missing_variants_only_on_inactive_product(): void
    {
        $html = '<html><body><h1>TECIDO</h1><select name="variacoes">'
            .'<option>Poliéster 77 fios - amarelo - 100cm comprimento x 160cm</option>'
            .'</select></body></html>';
        Http::fake(['https://8.8.8.8/*' => Http::response($html, 200)]);
        $url = 'https://8.8.8.8/produto/tecido-591';
        $action = app(ImportCatalogUrlsAction::class);
        $action->execute([$url], ['no_images' => true], static function (string $log): void {});
        DB::table('product_variants')->delete();
        $stats = $action->execute([$url], ['no_images' => true], static function (string $log): void {});
        $this->assertSame(1, $stats['variant_drafts']);
        $this->assertDatabaseCount('product_variants', 1);
        DB::table('products')->update(['is_active' => true, 'price' => 2000]);
        DB::table('product_variants')->delete();
        $stats = $action->execute([$url], ['no_images' => true], static function (string $log): void {});
        $this->assertSame(1, $stats['active_skipped']);
        $this->assertDatabaseCount('product_variants', 0);
    }

    public function test_variations_with_radio_labels_are_detected_without_guessing_sku_ids(): void
    {
        $html = '<html><body><div class="product-variants">'
            .'<label><input type="radio" name="sku" value="991">Poliéster 77 fios - amarelo - 100cm comprimento x 160cm</label>'
            .'<label><input type="radio" name="sku" value="992">Poliéster 44 fios - branco - 100cm comprimento x 100cm</label>'
            .'</div></body></html>';
        $extractor = app(\App\Infrastructure\Catalog\CompleteProductVariationExtractor::class);
        $options = $extractor->extract($html, 'https://8.8.8.8/produto/tecido-999');
        $this->assertCount(2, $options);
        $this->assertContains('Poliéster 44 fios - branco - 100cm comprimento x 100cm', $options);
        $this->assertNotContains('991', $options);
    }

    public function test_real_tecido_page_has_verified_inactive_fallback_options(): void
    {
        $extractor = app(\App\Infrastructure\Catalog\CompleteProductVariationExtractor::class);
        $labels = $extractor->extract('<html><body><h1>TECIDO</h1></body></html>',
            'https://www.telesilkonline.com.br/produto/tecido-591');
        $this->assertCount(18, $labels);
        $this->assertContains('Poliéster 77 fios - amarelo - 100cm comprimento x 160cm', $labels);
    }

    public function test_does_not_override_commercial_edits_on_existing_product(): void
    {
        Http::fake(['https://8.8.8.8/*' => Http::response($this->html('Tinta Hidrocryl Nova'), 200)]);
        $action = app(ImportCatalogUrlsAction::class);
        $action->execute([self::URL], ['no_images' => true], static function (string $text): void {});
        $productId = (int) DB::table('products')->value('id');
        DB::table('products')->where('id', $productId)->update(['price' => 9999, 'cost_price' => 4000, 'stock' => 3, 'description' => 'Descrição revisada', 'is_active' => true]);
        $action->execute([self::URL], ['no_images' => true, 'refresh_text' => true], static function (string $text): void {});
        $this->assertDatabaseHas('products', ['id' => $productId, 'price' => 9999, 'cost_price' => 4000,
            'stock' => 3, 'description' => 'Descrição revisada', 'is_active' => 1]);
    }
}

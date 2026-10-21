<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Actions\Catalog\ImportCatalogCsvAction;
use App\Infrastructure\Catalog\CatalogCsv;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class CatalogImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_category_product_photo_and_unverified_options_idempotently(): void
    {
        Storage::fake('public');
        DB::table('categories')->insert([
            'id' => 6, 'name' => 'Tinta Base Água', 'slug' => 'tinta-base-agua',
            'sort_order' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $imageUrl = 'https://cdn.vnda.com.br/150x/teste.png';
        $imagePath = 'products/imported/'.substr(hash('sha256', 'www.telesilkonline.com.br'), 0, 12);
        $imagePath .= '/hidrocryl-mix-248-'.substr(hash('sha256', $imageUrl), 0, 18).'.png';
        Storage::disk('public')->put($imagePath, 'conteudo-ja-existente-no-disco');
        $folder = sys_get_temp_dir().'/saba-catalog-test-'.uniqid();
        $csv = app(CatalogCsv::class);
        $productsFile = $folder.'/products.csv';
        $imagesFile = $folder.'/images.csv';
        $optionsFile = $folder.'/options.csv';
        $csv->write($productsFile, [[
            'category_id' => '6', 'category_name' => 'Tinta Base Água', 'name' => 'Hidrocryl Mix',
            'slug' => 'hidrocryl-mix-248', 'sku' => 'T56', 'price' => '0',
            'source_url' => 'https://www.telesilkonline.com.br/produto/hidrocryl-mix-248',
            'price_status' => 'preco_nao_exibido_na_fonte',
        ]], ['category_id', 'category_name', 'name', 'slug', 'sku', 'price', 'source_url', 'price_status']);
        $csv->write($imagesFile, [[
            'product_slug' => 'hidrocryl-mix-248', 'image_url' => $imageUrl,
            'alt_text' => 'Tinta Mix', 'sort_order' => 0, 'is_primary' => 1,
        ]], ['product_slug', 'image_url', 'alt_text', 'sort_order', 'is_primary']);
        $csv->write($optionsFile, [[
            'product_slug' => 'hidrocryl-mix-248', 'option_name' => 'Cor', 'option_value' => 'Azul',
        ]], ['product_slug', 'option_name', 'option_value']);

        try {
            $arguments = ['images' => $imagesFile, 'options' => $optionsFile];
            $first = app(ImportCatalogCsvAction::class)->execute($productsFile, $arguments);
            $second = app(ImportCatalogCsvAction::class)->execute($productsFile, $arguments);
            $this->assertSame(1, $first['created']);
            $this->assertSame(1, $second['existing']);
            $this->assertDatabaseCount('products', 1);
            $this->assertDatabaseCount('product_images', 1);
            $this->assertDatabaseCount('catalog_import_sources', 1);
            $this->assertDatabaseCount('catalog_import_options', 1);
            $this->assertDatabaseCount('product_variants', 0);
            $this->assertDatabaseHas('products', [
                'slug' => 'hidrocryl-mix-248', 'category_id' => 6,
                'availability' => 'unavailable', 'price' => 0, 'is_active' => 0,
            ]);
            $this->assertDatabaseHas('product_images', ['path' => $imagePath, 'is_primary' => 1]);
        } finally {
            foreach (glob($folder.'/*') ?: [] as $path) {
                unlink($path);
            }
            rmdir($folder);
        }
    }

    public function test_mismatched_category_cannot_silently_be_linked_by_id(): void
    {
        DB::table('categories')->insert([
            'id' => 6, 'name' => 'Outra Categoria', 'slug' => 'outra-categoria',
            'sort_order' => 0, 'is_active' => true,
        ]);
        $file = tempnam(sys_get_temp_dir(), 'saba-test-');
        app(CatalogCsv::class)->write($file, [[
            'category_id' => '6', 'category_name' => 'Tinta Base Água',
            'name' => 'Teste', 'slug' => 'teste', 'price' => '1234',
            'source_url' => 'https://www.telesilkonline.com.br/produto/teste',
        ]], ['category_id', 'category_name', 'name', 'slug', 'price', 'source_url']);
        try {
            $result = app(ImportCatalogCsvAction::class)->execute($file, ['no_images' => true]);
            $this->assertSame(1, $result['failed']);
            $this->assertDatabaseCount('products', 0);
        } finally {
            unlink($file);
        }
    }
}

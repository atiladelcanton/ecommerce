<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Actions\Catalog\AddProductImageAction;
use App\Actions\Catalog\CreateCategoryAction;
use App\Actions\Catalog\CreateProductAction;
use App\Actions\Catalog\DeleteCategoryAction;
use App\Actions\Catalog\DeleteProductAction;
use App\Actions\Catalog\RemoveProductImageAction;
use App\Actions\Catalog\SetPrimaryProductImageAction;
use App\Actions\Catalog\UpdateCategoryAction;
use App\Actions\Catalog\UpdateProductAction;
use App\Actions\Catalog\UpdateProductImageOrderAction;
use App\Domain\Catalog\Enums\ProductAvailability;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImage;
use Database\Seeders\CatalogSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class CatalogTest extends TestCase
{
    use RefreshDatabase;

    private function createProduct(array $overrides = []): Product
    {
        return app(CreateProductAction::class)->execute(array_replace([
            'category_id' => Category::factory()->create()->id,
            'name' => 'Máquina Serigráfica SABA 4x4',
            'availability' => ProductAvailability::IMMEDIATE,
            'cost_price' => 80000,
            'price' => 129990,
        ], $overrides));
    }

    public function test_category_creation_update_activation_and_ordering(): void
    {
        $category = app(CreateCategoryAction::class)->execute(['name' => 'Máquinas de Serigrafia', 'sort_order' => 10]);
        $this->assertSame('maquinas-de-serigrafia', $category->slug);
        $other = Category::factory()->create(['sort_order' => 1]);
        $this->assertSame([$other->id, $category->id], Category::ordered()->pluck('id')->all());
        $category = app(UpdateCategoryAction::class)->execute($category, ['name' => 'Novo nome', 'is_active' => false]);
        $this->assertSame('Novo nome', $category->name);
        $this->assertSame('maquinas-de-serigrafia', $category->slug);
        $this->assertSame([$other->id], Category::active()->pluck('id')->all());
        $category = app(UpdateCategoryAction::class)->execute($category, ['slug' => 'novo-slug']);
        $this->assertSame('novo-slug', $category->slug);
    }

    public function test_category_requires_a_name(): void
    {
        $this->expectException(ValidationException::class);
        app(CreateCategoryAction::class)->execute(['name' => '']);
    }

    public function test_category_slug_is_unique(): void
    {
        app(CreateCategoryAction::class)->execute(['name' => 'Secadores']);
        $this->expectException(ValidationException::class);
        app(CreateCategoryAction::class)->execute(['name' => 'Secadores']);
    }

    public function test_empty_category_can_be_deleted(): void
    {
        $category = Category::factory()->create();
        app(DeleteCategoryAction::class)->execute($category);
        $this->assertModelMissing($category);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $product = $this->createProduct();
        try {
            app(DeleteCategoryAction::class)->execute($product->category);
            $this->fail('Expected category deletion to be rejected.');
        } catch (ValidationException) {
            $this->assertModelExists($product);
            $this->assertModelExists($product->category);
        }
    }

    public function test_database_restricts_category_deletion(): void
    {
        $product = $this->createProduct();
        $this->expectException(QueryException::class);
        $product->category->delete();
    }

    public function test_product_creation_relationships_enum_defaults_and_integer_prices(): void
    {
        $product = $this->createProduct();
        $this->assertTrue($product->is_active);
        $this->assertFalse($product->is_featured);
        $this->assertTrue($product->whatsapp_quote_enabled);
        $this->assertSame('maquina-serigrafica-saba-4x4', $product->slug);
        $this->assertSame(ProductAvailability::IMMEDIATE, $product->availability);
        $this->assertTrue($product->category->products->first()->is($product));
        $this->assertSame(129990, $product->price);
        $this->assertSame(129990, $product->regular_price);
        $this->assertSame(129990, $product->final_price);
        $this->assertSame(80000, $product->cost_price);
        $this->assertFalse($product->has_promotion);
        $this->assertSame(0, $product->discount_percentage);
        $this->assertArrayNotHasKey('cost_price', $product->toArray());
        $this->assertStringNotContainsString('cost_price', $product->toJson());
        $this->assertSame('Disponibilidade imediata', ProductAvailability::IMMEDIATE->label());
        $this->assertSame('Sob encomenda', ProductAvailability::ON_DEMAND->label());
        $this->assertSame('Indisponível', ProductAvailability::UNAVAILABLE->label());
    }

    public function test_product_update_preserves_slug_and_updates_flags(): void
    {
        $product = $this->createProduct();
        $updated = app(UpdateProductAction::class)->execute($product, ['name' => 'Novo nome', 'is_active' => false, 'is_featured' => true, 'availability' => 'on_demand']);
        $this->assertSame($product->slug, $updated->slug);
        $this->assertFalse($updated->is_active);
        $this->assertTrue($updated->is_featured);
        $this->assertSame(ProductAvailability::ON_DEMAND, $updated->availability);
        $updated = app(UpdateProductAction::class)->execute($updated, ['slug' => 'slug-explicito']);
        $this->assertSame('slug-explicito', $updated->slug);
    }

    public function test_product_slug_is_unique(): void
    {
        $this->createProduct();
        $this->expectException(ValidationException::class);
        $this->createProduct();
    }

    public function test_product_sku_is_unique_when_provided(): void
    {
        $this->createProduct(['sku' => 'SABA-001']);
        $this->expectException(ValidationException::class);
        $this->createProduct(['name' => 'Outro', 'sku' => 'SABA-001']);
    }

    public function test_valid_promotion_and_removal(): void
    {
        $product = $this->createProduct(['price' => 10000, 'promotional_price' => 7500]);
        $this->assertSame(7500, $product->final_price);
        $this->assertTrue($product->has_promotion);
        $this->assertSame(25, $product->discount_percentage);
        $product = app(UpdateProductAction::class)->execute($product, ['promotional_price' => null]);
        $this->assertSame(10000, $product->final_price);
        $this->assertFalse($product->has_promotion);
    }

    public static function invalidPrices(): array
    {
        return [
            'zero selling price' => [['price' => 0]],
            'equal' => [['promotional_price' => 129990]],
            'greater' => [['promotional_price' => 130000]],
            'zero' => [['promotional_price' => 0]],
            'negative promotion' => [['promotional_price' => -1]],
            'negative cost' => [['cost_price' => -1]],
            'negative regular' => [['price' => -1]],
            'float regular' => [['price' => 1299.90]],
            'float cost' => [['cost_price' => 10.0]],
            'float promotion' => [['promotional_price' => 10.5]],
            'numeric string' => [['price' => '129990']],
        ];
    }

    #[DataProvider('invalidPrices')]
    public function test_invalid_prices_rejected_on_creation(array $data): void
    {
        $this->expectException(ValidationException::class);
        $this->createProduct($data);
    }

    #[DataProvider('invalidPrices')]
    public function test_invalid_prices_rejected_on_update(array $data): void
    {
        $product = $this->createProduct();
        try {
            app(UpdateProductAction::class)->execute($product, $data);
            $this->fail('Expected invalid prices to be rejected.');
        } catch (ValidationException) {
            $this->assertSame(129990, $product->fresh()->price);
            $this->assertNull($product->fresh()->promotional_price);
        }
    }

    public function test_lowering_regular_price_checks_existing_promotion(): void
    {
        $product = $this->createProduct(['promotional_price' => 100000]);
        $this->expectException(ValidationException::class);
        app(UpdateProductAction::class)->execute($product, ['price' => 90000]);
    }

    public function test_product_scopes(): void
    {
        $normal = Product::factory()->create();
        $featured = Product::factory()->create(['is_featured' => true]);
        $promotion = Product::factory()->onPromotion()->create();
        Product::factory()->create(['is_active' => false]);
        // Scope remains defensive against invalid legacy/raw writes.
        Product::factory()->create(['promotional_price' => 129990, 'is_active' => false]);
        $this->assertEqualsCanonicalizing([$normal->id, $featured->id, $promotion->id], Product::active()->pluck('id')->all());
        $this->assertSame([$featured->id], Product::featured()->pluck('id')->all());
        $this->assertSame([$promotion->id], Product::onPromotion()->pluck('id')->all());
    }

    public function test_gallery_primary_order_and_removal(): void
    {
        $product = $this->createProduct();
        $add = app(AddProductImageAction::class);
        $first = $add->execute($product, ['path' => 'products/first.jpg', 'sort_order' => 20]);
        $second = $add->execute($product, ['path' => 'products/second.jpg', 'sort_order' => 2]);
        $third = $add->execute($product, ['path' => 'products/third.jpg', 'sort_order' => 1]);
        $this->assertTrue($first->is_primary);
        $this->assertTrue($first->product->is($product));
        $this->assertSame([$first->id, $third->id, $second->id], $product->images()->pluck('id')->all());
        app(SetPrimaryProductImageAction::class)->execute($second);
        app(SetPrimaryProductImageAction::class)->execute($second);
        $this->assertSame(1, $product->images()->where('is_primary', true)->count());
        $this->assertTrue($second->fresh()->is_primary);
        app(UpdateProductImageOrderAction::class)->execute($first, 0);
        $this->assertSame([$second->id, $first->id, $third->id], $product->images()->pluck('id')->all());
        app(RemoveProductImageAction::class)->execute($second);
        $this->assertModelMissing($second);
        $this->assertTrue($first->fresh()->is_primary);
        app(RemoveProductImageAction::class)->execute($third);
        $this->assertTrue($first->fresh()->is_primary);
        app(RemoveProductImageAction::class)->execute($first);
        $this->assertSame(0, $product->images()->count());
    }

    public function test_adding_primary_image_does_not_affect_other_products(): void
    {
        $a = Product::factory()->create();
        $b = Product::factory()->create();
        $add = app(AddProductImageAction::class);
        $old = $add->execute($a, ['path' => 'a.jpg']);
        $other = $add->execute($b, ['path' => 'b.jpg']);
        $new = $add->execute($a, ['path' => 'c.jpg', 'is_primary' => true]);
        $this->assertFalse($old->fresh()->is_primary);
        $this->assertTrue($new->fresh()->is_primary);
        $this->assertTrue($other->fresh()->is_primary);
    }

    public function test_image_order_ties_use_id(): void
    {
        $product = Product::factory()->create();
        $images = ProductImage::factory()->count(3)->for($product)->create();
        $this->assertSame($images->modelKeys(), $product->images()->pluck('id')->all());
    }

    public function test_deleting_product_cascades_images_but_preserves_category(): void
    {
        $product = $this->createProduct();
        $image = app(AddProductImageAction::class)->execute($product, ['path' => 'products/a.jpg']);
        $category = $product->category;
        app(DeleteProductAction::class)->execute($product);
        $this->assertModelMissing($product);
        $this->assertModelMissing($image);
        $this->assertModelExists($category);
    }

    public function test_development_seeder_is_repeatable_and_valid(): void
    {
        $this->seed(CatalogSeeder::class);
        $this->seed(CatalogSeeder::class);
        $this->assertDatabaseCount('categories', 8);
        $this->assertDatabaseCount('products', 30);
        foreach (Product::all() as $product) {
            $this->assertIsInt($product->final_price);
        }
    }
}

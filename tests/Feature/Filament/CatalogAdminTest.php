<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Actions\Catalog\AddProductImageAction;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\RelationManagers\ImagesRelationManager;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class CatalogAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::bootCurrentPanel();
        app()->setLocale('pt_BR');
        $this->actingAs(User::factory()->create());
        Storage::fake('public');
    }

    private function productData(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Máquina SABA 4x4',
            'category_id' => Category::factory()->create()->id,
            'availability' => 'immediate',
            'cost_price' => '8.000,50',
            'price' => '12.900,00',
            'promotional_price' => '11.500,90',
            'is_active' => true,
            'is_featured' => true,
            'whatsapp_quote_enabled' => true,
        ], $overrides);
    }

    public function test_admin_can_create_category_with_generated_slug(): void
    {
        Livewire::test(CreateCategory::class)->fillForm([
            'name' => 'Máquinas de Serigrafia', 'sort_order' => 2, 'is_active' => true,
        ])->call('create')->assertHasNoFormErrors();
        $this->assertDatabaseHas('categories', ['name' => 'Máquinas de Serigrafia', 'slug' => 'maquinas-de-serigrafia', 'sort_order' => 2]);
    }

    public function test_category_edit_preserves_slug_and_reuses_delete_protection(): void
    {
        $category = Category::factory()->create();
        Product::factory()->for($category)->create();
        Livewire::test(EditCategory::class, ['record' => $category->id])
            ->fillForm(['name' => 'Nome atualizado', 'is_active' => false])->call('save')->assertHasNoFormErrors()
            ->callAction('delete')->assertNotified('Não foi possível excluir');
        $this->assertSame('Nome atualizado', $category->fresh()->name);
        $this->assertSame($category->slug, $category->fresh()->slug);
        $this->assertFalse($category->fresh()->is_active);
        $this->assertModelExists($category);
    }

    public function test_category_table_orders_counts_filters_and_deletes(): void
    {
        $b = Category::factory()->create(['name' => 'B', 'sort_order' => 0]);
        $a = Category::factory()->create(['name' => 'A', 'sort_order' => 0]);
        $inactive = Category::factory()->create(['sort_order' => 1, 'is_active' => false]);
        Livewire::test(ListCategories::class)->assertCanSeeTableRecords([$a, $b, $inactive], inOrder: true)
            ->filterTable('is_active', false)->assertCanSeeTableRecords([$inactive])->assertCanNotSeeTableRecords([$a, $b])
            ->callAction(TestAction::make('delete')->table($inactive));
        $this->assertModelMissing($inactive);
    }

    public function test_admin_can_create_product_with_integer_cents_and_category(): void
    {
        $data = $this->productData();
        Livewire::test(CreateProduct::class)->fillForm($data)->call('create')->assertHasNoFormErrors();
        $product = Product::query()->sole();
        $this->assertSame('maquina-saba-4x4', $product->slug);
        $this->assertSame($data['category_id'], $product->category_id);
        $this->assertSame(800050, $product->cost_price);
        $this->assertSame(1290000, $product->price);
        $this->assertSame(1150090, $product->promotional_price);
        $this->assertTrue($product->is_featured);
    }

    public function test_brazilian_amounts_and_zero_cost_are_saved_as_integer_cents(): void
    {
        Livewire::test(CreateProduct::class)->fillForm($this->productData([
            'cost_price' => '0,00', 'price' => '12.500,00', 'promotional_price' => '1.299,90',
        ]))->call('create')->assertHasNoFormErrors();
        $product = Product::query()->sole();
        $this->assertSame(0, $product->cost_price);
        $this->assertSame(1250000, $product->price);
        $this->assertSame(129990, $product->promotional_price);
        Livewire::test(ListProducts::class)->assertSee('R$ 12.500,00')->assertSee('R$ 1.299,90');
    }

    public function test_selling_price_is_required_and_promotion_error_is_friendly(): void
    {
        Livewire::test(CreateProduct::class)->fillForm($this->productData(['price' => '']))
            ->call('create')->assertHasFormErrors(['price' => 'required']);
        Livewire::test(CreateProduct::class)->fillForm($this->productData(['promotional_price' => '12.900,00']))
            ->call('create')->assertHasFormErrors(['promotional_price'])
            ->assertSee('O preço promocional deve ser menor que o preço de venda.');
    }

    public static function invalidMoney(): array
    {
        return [
            'zero selling price' => ['price', '0,00'],
            'equal promotion' => ['promotional_price', '12.900,00'],
            'higher promotion' => ['promotional_price', '13.000,00'],
            'zero promotion' => ['promotional_price', '0,00'],
            'negative selling price' => ['price', '-1,00'],
            'negative cost' => ['cost_price', '-0,01'],
            'invalid notation' => ['price', '12,900.00'],
            'overflow' => ['price', '999999999999999999999999'],
        ];
    }

    #[DataProvider('invalidMoney')]
    public function test_admin_rejects_invalid_prices_with_field_error(string $field, string $value): void
    {
        Livewire::test(CreateProduct::class)->fillForm($this->productData([$field => $value]))
            ->call('create')->assertHasFormErrors([$field]);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_product_edit_formats_cost_and_preserves_money_and_slug(): void
    {
        $product = Product::factory()->create(['price' => 1290000, 'cost_price' => 800050, 'promotional_price' => 1100000]);
        $component = Livewire::test(EditProduct::class, ['record' => $product->id])
            ->assertFormSet(['price' => '12.900,00', 'cost_price' => '8.000,50', 'promotional_price' => '11.000,00'])
            ->fillForm(['name' => 'Nome alterado'])->call('save')->assertHasNoFormErrors();
        $this->assertSame(800050, $product->fresh()->cost_price);
        $this->assertSame(1290000, $product->fresh()->price);
        $this->assertSame($product->slug, $product->fresh()->slug);
        $component->fillForm(['price' => '10.000,00'])->call('save')->assertHasFormErrors(['promotional_price']);
        $this->assertSame(1290000, $product->fresh()->price);
        $component->fillForm(['price' => '10.000,00', 'promotional_price' => null])->call('save')->assertHasNoFormErrors();
        $this->assertNull($product->fresh()->promotional_price);
    }

    public function test_admin_uploads_multiple_images_and_edit_does_not_duplicate_them(): void
    {
        Livewire::test(CreateProduct::class)->fillForm($this->productData([
            'new_images' => [UploadedFile::fake()->image('front.jpg'), UploadedFile::fake()->image('back.png')],
        ]))->call('create')->assertHasNoFormErrors();
        $product = Product::query()->sole();
        $this->assertCount(2, $product->images);
        $this->assertSame(1, $product->images()->where('is_primary', true)->count());
        foreach ($product->images as $image) {
            Storage::disk('public')->assertExists($image->path);
        }
        Livewire::test(EditProduct::class, ['record' => $product->id])
            ->fillForm(['new_images' => [UploadedFile::fake()->image('detail.jpg')]])
            ->call('save')->assertHasNoFormErrors()
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame(3, $product->images()->count());
    }

    public function test_gallery_actions_set_primary_order_and_remove_consistently(): void
    {
        $product = Product::factory()->create();
        $first = app(AddProductImageAction::class)->execute($product, ['path' => 'products/a.jpg']);
        $second = app(AddProductImageAction::class)->execute($product, ['path' => 'products/b.jpg']);
        Livewire::test(ImagesRelationManager::class, ['ownerRecord' => $product, 'pageClass' => EditProduct::class])
            ->assertCanSeeTableRecords([$first, $second])
            ->callAction(TestAction::make('primary')->table($second))
            ->callAction(TestAction::make('order')->table($first), ['sort_order' => 5]);
        $this->assertTrue($second->fresh()->is_primary);
        $this->assertFalse($first->fresh()->is_primary);
        $this->assertSame(5, $first->fresh()->sort_order);
        Livewire::test(ImagesRelationManager::class, ['ownerRecord' => $product, 'pageClass' => EditProduct::class])
            ->callAction(TestAction::make('delete')->table($second));
        $this->assertModelMissing($second);
        $this->assertTrue($first->fresh()->is_primary);
    }

    public function test_product_table_search_filters_and_delete(): void
    {
        $featured = Product::factory()->onPromotion()->create(['is_featured' => true, 'sku' => 'SABA-TESTE']);
        $other = Product::factory()->create(['is_active' => false, 'availability' => 'unavailable']);
        Livewire::test(ListProducts::class)->assertCanSeeTableRecords([$featured, $other])
            ->searchTable('SABA-TESTE')->assertCanSeeTableRecords([$featured])->assertCanNotSeeTableRecords([$other])
            ->searchTable('')->filterTable('on_promotion')->assertCanSeeTableRecords([$featured])->assertCanNotSeeTableRecords([$other])
            ->removeTableFilters()->filterTable('category_id', $other->category_id)->assertCanSeeTableRecords([$other])->assertCanNotSeeTableRecords([$featured])
            ->removeTableFilters()->filterTable('availability', 'unavailable')->assertCanSeeTableRecords([$other])->assertCanNotSeeTableRecords([$featured])
            ->callAction(TestAction::make('delete')->table($other));
        $this->assertModelMissing($other);
    }

    public function test_invalid_promotion_can_be_corrected_without_losing_uploads(): void
    {
        Livewire::test(CreateProduct::class)->fillForm($this->productData([
            'promotional_price' => '13.000,00',
            'new_images' => [UploadedFile::fake()->image('front.jpg')],
        ]))->call('create')->assertHasFormErrors(['promotional_price'])
            ->fillForm(['promotional_price' => '12.000,00'])
            ->call('create')->assertHasNoFormErrors();
        $product = Product::query()->sole();
        $this->assertSame(1, $product->images()->count());
        Storage::disk('public')->assertExists($product->images()->first()->path);
    }

    public function test_upload_cannot_attach_an_arbitrary_existing_path(): void
    {
        Storage::disk('public')->put('products/unrelated.jpg', 'existing file');
        Livewire::test(CreateProduct::class)->fillForm($this->productData([
            'new_images' => ['products/unrelated.jpg'],
        ]))->call('create')->assertHasFormErrors(['new_images']);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_product_active_and_featured_filters(): void
    {
        $active = Product::factory()->create(['is_active' => true, 'is_featured' => false]);
        $featured = Product::factory()->create(['is_active' => false, 'is_featured' => true]);
        Livewire::test(ListProducts::class)->filterTable('is_active', true)
            ->assertCanSeeTableRecords([$active])->assertCanNotSeeTableRecords([$featured])
            ->removeTableFilters()->filterTable('is_featured', true)
            ->assertCanSeeTableRecords([$featured])->assertCanNotSeeTableRecords([$active]);
    }

    public function test_local_admin_routes_render_in_portuguese(): void
    {
        config(['app.env' => 'local', 'app.locale' => 'en']);
        $product = Product::factory()->create();
        $this->get('/admin/categories')->assertOk()->assertSee('Categorias');
        $this->get('/admin/products')->assertOk()->assertSee('Produtos');
        $this->get('/admin/products/'.$product->id.'/edit')->assertOk()->assertSee('Comercial')->assertSee('Disponibilidade');
        $this->assertSame('pt_BR', app()->getLocale());
    }

    public function test_guests_cannot_access_catalog_pages(): void
    {
        auth()->logout();
        $this->get('/admin/products')->assertRedirect('/admin/login');
        $this->get('/admin/categories')->assertRedirect('/admin/login');
    }
}

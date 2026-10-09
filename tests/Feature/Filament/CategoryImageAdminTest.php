<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Domain\Catalog\Models\Category;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

final class CategoryImageAdminTest extends TestCase
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

    public function test_admin_can_create_category_without_an_image(): void
    {
        Livewire::test(CreateCategory::class)->fillForm(['name' => 'Sem imagem'])
            ->call('create')->assertHasNoFormErrors();
        $this->assertNull(Category::query()->sole()->image_path);
    }

    public function test_upload_preview_preservation_replacement_and_removal(): void
    {
        Livewire::test(CreateCategory::class)->fillForm([
            'name' => 'Máquinas', 'image_path' => UploadedFile::fake()->image('machines.jpg'),
        ])->call('create')->assertHasNoFormErrors();
        $category = Category::query()->sole();
        $original = $category->image_path;
        $this->assertStringStartsWith('categories/', $original);
        Storage::disk('public')->assertExists($original);

        $page = Livewire::test(EditCategory::class, ['record' => $category->id])
            ->assertFormSet(['image_path' => $original])
            ->fillForm(['name' => 'Máquinas SABA'])->call('save')->assertHasNoFormErrors();
        $this->assertSame($original, $category->fresh()->image_path);
        $page->fillForm(['image_path' => null])
            ->fillForm(['image_path' => [UploadedFile::fake()->image('replacement.png')]])
            ->call('save')->assertHasNoFormErrors();
        $replacement = $category->fresh()->image_path;
        $this->assertNotSame($original, $replacement);
        Storage::disk('public')->assertExists($replacement);
        $page->fillForm(['image_path' => null])->call('save')->assertHasNoFormErrors();
        $this->assertNull($category->fresh()->image_path);
        Storage::disk('public')->assertExists($original);
        Storage::disk('public')->assertExists($replacement);
    }

    public function test_create_and_edit_reject_unrelated_existing_image_paths(): void
    {
        Storage::disk('public')->put('categories/unrelated.jpg', UploadedFile::fake()->image('unrelated.jpg')->getContent());
        Livewire::test(CreateCategory::class)->fillForm([
            'name' => 'Inválida', 'image_path' => ['categories/unrelated.jpg'],
        ])->call('create')->assertHasFormErrors(['image_path']);
        $this->assertDatabaseCount('categories', 0);

        $category = Category::factory()->create();
        Livewire::test(EditCategory::class, ['record' => $category->id])->fillForm([
            'image_path' => ['categories/unrelated.jpg'],
        ])->call('save')->assertHasFormErrors(['image_path']);
        $this->assertNull($category->fresh()->image_path);
    }

    public function test_upload_survives_domain_validation_retry(): void
    {
        Category::factory()->create(['slug' => 'existente']);
        Livewire::test(CreateCategory::class)->fillForm([
            'name' => 'Nova', 'slug' => 'existente', 'image_path' => UploadedFile::fake()->image('photo.jpg'),
        ])->call('create')->assertHasFormErrors(['slug'])
            ->fillForm(['slug' => 'nova'])->call('create')->assertHasNoFormErrors();
        Storage::disk('public')->assertExists(Category::query()->where('slug', 'nova')->sole()->image_path);
    }

    public function test_upload_rejects_non_images_and_files_over_five_megabytes(): void
    {
        Livewire::test(CreateCategory::class)->fillForm([
            'name' => 'Inválida', 'image_path' => UploadedFile::fake()->create('file.pdf', 20, 'application/pdf'),
        ])->call('create')->assertHasFormErrors(['image_path']);
        Livewire::test(CreateCategory::class)->fillForm([
            'name' => 'Grande', 'image_path' => UploadedFile::fake()->image('large.jpg')->size(5121),
        ])->call('create')->assertHasFormErrors(['image_path']);
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_table_displays_thumbnail_and_discreet_placeholder(): void
    {
        $withImage = Category::factory()->create(['image_path' => 'categories/thumbnail.jpg']);
        $withoutImage = Category::factory()->create();
        Storage::disk('public')->put($withImage->image_path, UploadedFile::fake()->image('thumbnail.jpg')->getContent());
        Livewire::test(ListCategories::class)->assertCanSeeTableRecords([$withImage, $withoutImage])
            ->assertTableColumnStateSet('image_path', $withImage->image_path, $withImage)
            ->assertTableColumnStateSet('image_path', null, $withoutImage)
            ->assertSee(Storage::disk('public')->url($withImage->image_path), false)
            ->assertSee('Sem imagem');
    }
}

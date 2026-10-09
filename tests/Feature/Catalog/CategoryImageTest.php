<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Actions\Catalog\CreateCategoryAction;
use App\Actions\Catalog\UpdateCategoryAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class CategoryImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_image_is_optional_and_can_be_set_preserved_replaced_and_removed(): void
    {
        $category = app(CreateCategoryAction::class)->execute(['name' => 'Máquinas']);
        $this->assertNull($category->image_path);

        $category = app(UpdateCategoryAction::class)->execute($category, ['image_path' => 'categories/machines.jpg']);
        $this->assertSame('categories/machines.jpg', $category->image_path);
        $category = app(UpdateCategoryAction::class)->execute($category, ['name' => 'Máquinas SABA']);
        $this->assertSame('categories/machines.jpg', $category->image_path);
        $category = app(UpdateCategoryAction::class)->execute($category, ['image_path' => 'categories/new.webp']);
        $this->assertSame('categories/new.webp', $category->image_path);
        $category = app(UpdateCategoryAction::class)->execute($category, ['image_path' => null]);
        $this->assertNull($category->image_path);
    }

    public function test_creation_accepts_image_path_and_empty_image_means_null(): void
    {
        $category = app(CreateCategoryAction::class)->execute(['name' => 'Secadores', 'image_path' => 'categories/dryers.png']);
        $this->assertSame('categories/dryers.png', $category->image_path);
        $category = app(UpdateCategoryAction::class)->execute($category, ['image_path' => '']);
        $this->assertNull($category->image_path);
    }

    public static function invalidPaths(): array
    {
        return [
            'absolute' => ['/etc/passwd'],
            'URL' => ['https://example.com/image.jpg'],
            'another module' => ['products/image.jpg'],
            'traversal' => ['categories/../image.jpg'],
            'nested traversal' => ['categories/sub/../../image.jpg'],
            'script' => ['categories/file.php'],
            'SVG' => ['categories/file.svg'],
            'wrong type' => [['categories/image.jpg']],
        ];
    }

    #[DataProvider('invalidPaths')]
    public function test_actions_reject_invalid_paths_without_modifying_the_category(mixed $path): void
    {
        try {
            app(CreateCategoryAction::class)->execute(['name' => 'Inválida', 'image_path' => $path]);
            $this->fail('Expected an invalid path to be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('image_path', $exception->errors());
            $this->assertDatabaseCount('categories', 0);
        }

        $category = app(CreateCategoryAction::class)->execute(['name' => 'Válida', 'image_path' => 'categories/valid.jpg']);
        try {
            app(UpdateCategoryAction::class)->execute($category, ['name' => 'Alterada', 'image_path' => $path]);
            $this->fail('Expected an invalid update to be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('image_path', $exception->errors());
            $this->assertSame('Válida', $category->fresh()->name);
            $this->assertSame('categories/valid.jpg', $category->fresh()->image_path);
        }
    }

    public function test_home_uses_registered_images_and_typographic_fallback_for_active_categories(): void
    {
        $visual = app(CreateCategoryAction::class)->execute(['name' => 'Máquinas com imagem', 'image_path' => 'categories/machines.jpg']);
        $text = app(CreateCategoryAction::class)->execute(['name' => 'Categoria sem imagem']);
        app(CreateCategoryAction::class)->execute(['name' => 'Categoria inativa', 'image_path' => 'categories/inactive.jpg', 'is_active' => false]);

        $response = $this->get('/')->assertOk();
        $response->assertSee(Storage::disk('public')->url($visual->image_path), false)
            ->assertSee('store-category--image', false)
            ->assertSee($visual->name)->assertSee($text->name)
            ->assertSee(route('store.products', ['categoria' => $visual->slug]), false)
            ->assertSee(route('store.products', ['categoria' => $text->slug]), false)
            ->assertDontSee('categories/inactive.jpg');
        $this->assertSame(1, substr_count($response->getContent(), 'store-category--image'));
    }
}

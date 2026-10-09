<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Domain\Catalog\Models\Product;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\RichEditor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductRichDescriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::bootCurrentPanel();
        $this->actingAs(User::factory()->create());
    }

    public function test_description_uses_native_editor_and_saves_formatting_in_existing_column(): void
    {
        $product = Product::factory()->create();
        Livewire::test(EditProduct::class, ['record' => $product->id])
            ->assertFormFieldExists('description', fn ($field): bool => $field instanceof RichEditor)
            ->fillForm(['description' => '<h2>Informações</h2><p><strong>Qualidade</strong> ✅</p>'])
            ->call('save')->assertHasNoFormErrors();

        $this->assertSame('<h2>Informações</h2><p><strong>Qualidade</strong> ✅</p>', $product->fresh()->description);
    }

    public function test_editor_loads_existing_plain_text_with_line_breaks(): void
    {
        $product = Product::factory()->create(['description' => "Primeira linha\nSegunda linha"]);
        Livewire::test(EditProduct::class, ['record' => $product->id])
            ->call('save')->assertHasNoFormErrors();
        $this->assertStringContainsString('<br', $product->fresh()->description);
        $this->assertStringContainsString('Segunda linha', $product->fresh()->description);
    }
}

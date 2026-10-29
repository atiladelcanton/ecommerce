<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Actions\Settings\UpdateStoreSettingsAction;
use App\Domain\Settings\Models\StoreSetting;
use App\Support\Storefront\Appearance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class StoreAppearanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_appearance_can_be_saved_without_changing_template_selection(): void
    {
        config(['storefront_templates.active' => 2]);

        $settings = app(UpdateStoreSettingsAction::class)->execute([
            'store_name' => 'Loja Exemplo',
            'color_primary' => '#123abc',
            'color_secondary' => '#f0f0f0',
            'color_button' => '#eeeeee',
        ]);

        $this->assertSame('#123ABC', $settings->color_primary);
        $this->assertSame('#F0F0F0', $settings->color_secondary);
        $this->assertSame('#EEEEEE', $settings->color_button);
        $this->assertSame(2, \App\Support\Storefront\Template::active());

        $response = $this->get('/quem-somos')->assertOk();
        $response->assertSee('--color-store-red:#123ABC', false)
            ->assertSee('--color-store-secondary:#F0F0F0', false)
            ->assertSee('--color-store-button:#EEEEEE', false)
            ->assertSee('--color-store-button-text:#000000', false)
            ->assertSee('css/storefront/appearance.css');
    }

    public function test_logo_is_rendered_on_both_templates_without_escaping_other_paths(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('store/branding/my-logo.png', 'fake image bytes');
        app(UpdateStoreSettingsAction::class)->execute([
            'store_name' => 'Loja Personalizada',
            'logo_path' => 'store/branding/my-logo.png',
        ]);
        foreach ([1, 2] as $template) {
            config(['storefront_templates.active' => $template]);
            $this->get('/quem-somos')->assertOk()
                ->assertSee('/storage/store/branding/my-logo.png')
                ->assertSee('Loja Personalizada');
        }
        $this->assertDatabaseCount('store_settings', 1);
    }

    public function test_defaults_are_preserved_when_colors_and_logo_are_missing(): void
    {
        $this->assertStringContainsString('--color-store-red:#DC2626', Appearance::forTemplate(null, 1)['css']);
        $this->assertStringContainsString('--color-store-red-dark:#B91C1C', Appearance::forTemplate(null, 1)['css']);
        $this->assertStringContainsString('--color-store-red:#156583', Appearance::forTemplate(null, 2)['css']);
        $this->assertStringContainsString('--color-store-red-dark:#104D67', Appearance::forTemplate(null, 2)['css']);
        $this->assertSame(asset('images/saba-logo.jpeg'), Appearance::logoUrl(null));
    }

    public function test_invalid_hex_or_logo_paths_are_rejected_and_do_not_change_settings(): void
    {
        app(UpdateStoreSettingsAction::class)->execute(['store_name' => 'Loja Inicial']);
        foreach ([
            'color_primary' => 'red; background:url(javascript:alert(1))',
            'color_secondary' => '#FFF',
            'color_button' => '#GG1122',
            'logo_path' => '../../.env',
        ] as $field => $invalid) {
            try {
                app(UpdateStoreSettingsAction::class)->execute(['store_name' => 'Loja Inicial', $field => $invalid]);
                $this->fail("Expected {$field} to be rejected.");
            } catch (ValidationException) {
                $this->assertNull(StoreSetting::query()->firstOrFail()->{$field});
            }
        }
        $this->assertSame('Loja Inicial', StoreSetting::query()->firstOrFail()->store_name);
    }

    public function test_customization_can_be_cleared_to_restore_template_defaults(): void
    {
        $action = app(UpdateStoreSettingsAction::class);
        $action->execute(['store_name' => 'Loja', 'color_primary' => '#112233', 'color_button' => '#AABBCC']);
        $settings = $action->execute(['store_name' => 'Loja', 'color_primary' => null, 'color_button' => '']);
        $this->assertNull($settings->color_primary);
        $this->assertNull($settings->color_button);
        $this->assertSame('#DC2626', Appearance::forTemplate($settings, 1)['primary']);
    }
}

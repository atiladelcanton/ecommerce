<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Actions\Settings\UpdateStoreSettingsAction;
use App\Domain\Settings\Models\StoreSetting;
use App\Support\Storefront\Appearance;
use App\Support\Storefront\StoreBrand;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class StoreBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploaded_logo_and_store_name_are_shared_by_the_storefront_and_admin(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('store/branding/logo.png', 'logo');

        app(UpdateStoreSettingsAction::class)->execute([
            'store_name' => 'Loja Exemplo',
            'logo_path' => 'store/branding/logo.png',
        ]);

        $settings = StoreSetting::query()->findOrFail(StoreSetting::MAIN_ID);
        $url = Storage::disk('public')->url('store/branding/logo.png');

        $this->assertSame('Loja Exemplo', StoreBrand::currentName());
        $this->assertSame($url, Appearance::logoUrl($settings));
        $this->assertSame('Loja Exemplo', Filament::getPanel('admin')->getBrandName());
        $this->assertSame($url, Filament::getPanel('admin')->getBrandLogo());

        $this->get('/contato')->assertOk()->assertSee('Fale com Loja Exemplo');
        $this->get('/quem-somos')->assertOk()->assertSee('Fale com Loja Exemplo');
    }

    public function test_without_uploaded_logo_brand_is_rendered_as_name_instead_of_another_company_logo(): void
    {
        app(UpdateStoreSettingsAction::class)->execute(['store_name' => 'Outra Loja']);

        $this->assertNull(Appearance::logoUrl(StoreSetting::query()->first()));
        $this->assertNull(Filament::getPanel('admin')->getBrandLogo());
        $this->assertSame('Outra Loja', Filament::getPanel('admin')->getBrandName());

        $this->get('/')->assertOk()->assertSee('Outra Loja')->assertDontSee('saba-logo.jpeg');
    }
}

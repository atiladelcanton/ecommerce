<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Domain\Catalog\Models\Banner;
use App\Domain\Settings\Models\StoreSetting;
use App\Filament\Pages\StoreSettings;
use App\Filament\Resources\Banners\Pages\CreateBanner;
use App\Filament\Resources\Banners\Pages\EditBanner;
use App\Filament\Resources\Banners\Pages\ListBanners;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

final class BannersAndSettingsAdminTest extends TestCase
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

    public function test_banner_creation_with_both_images_and_edit(): void
    {
        Livewire::test(CreateBanner::class)->fillForm([
            'title' => 'Campanha SABA',
            'desktop_image' => UploadedFile::fake()->image('desktop.jpg'),
            'mobile_image' => UploadedFile::fake()->image('mobile.png'),
            'link' => 'https://example.com/produtos', 'alt_text' => 'Máquina SABA', 'sort_order' => 3,
        ])->call('create')->assertHasNoFormErrors();
        $banner = Banner::query()->sole();
        Storage::disk('public')->assertExists($banner->desktop_image);
        Storage::disk('public')->assertExists($banner->mobile_image);
        Livewire::test(EditBanner::class, ['record' => $banner->id])
            ->assertFormSet(['title' => 'Campanha SABA', 'sort_order' => 3])
            ->fillForm(['is_active' => false, 'mobile_image' => null])
            ->call('save')->assertHasNoFormErrors()
            ->call('save')->assertHasNoFormErrors();
        $this->assertFalse($banner->fresh()->is_active);
        $this->assertNull($banner->fresh()->mobile_image);
        $this->assertSame($banner->desktop_image, $banner->fresh()->desktop_image);
    }

    public function test_banner_requires_desktop_image_and_rejects_arbitrary_paths(): void
    {
        Livewire::test(CreateBanner::class)->fillForm(['title' => 'Sem imagem'])
            ->call('create')->assertHasFormErrors(['desktop_image' => 'required']);
        Storage::disk('public')->put('banners/unrelated.jpg', 'file');
        Livewire::test(CreateBanner::class)->fillForm(['title' => 'Inválido', 'desktop_image' => ['banners/unrelated.jpg']])
            ->call('create')->assertHasFormErrors(['desktop_image']);
        $this->assertDatabaseCount('banners', 0);
    }

    public function test_banner_invalid_period_can_be_corrected_without_losing_uploads(): void
    {
        Livewire::test(CreateBanner::class)->fillForm([
            'title' => 'Campanha', 'desktop_image' => UploadedFile::fake()->image('desktop.jpg'),
            'starts_at' => '2026-10-10 12:00:00', 'ends_at' => '2026-10-09 12:00:00',
        ])->call('create')->assertHasFormErrors(['ends_at'])
            ->assertSee('O fim da publicação deve ser igual ou posterior ao início.')
            ->fillForm(['ends_at' => '2026-10-12 12:00:00'])
            ->call('create')->assertHasNoFormErrors();
        Storage::disk('public')->assertExists(Banner::query()->sole()->desktop_image);
    }

    public function test_banner_list_filters_order_and_deletion(): void
    {
        $visible = Banner::factory()->create(['sort_order' => 2]);
        $future = Banner::factory()->create(['sort_order' => 1, 'starts_at' => now()->addDay()]);
        $inactive = Banner::factory()->create(['sort_order' => 3, 'is_active' => false]);
        Livewire::test(ListBanners::class)->assertCanSeeTableRecords([$future, $visible, $inactive], inOrder: true)
            ->filterTable('currently_visible')->assertCanSeeTableRecords([$visible])->assertCanNotSeeTableRecords([$future, $inactive])
            ->removeTableFilters()->filterTable('is_active', false)->assertCanSeeTableRecords([$inactive])->assertCanNotSeeTableRecords([$visible, $future])
            ->callAction(TestAction::make('delete')->table($inactive));
        $this->assertModelMissing($inactive);
    }

    public function test_settings_page_creates_then_updates_a_single_record(): void
    {
        $page = Livewire::test(StoreSettings::class)->assertOk();
        $this->assertDatabaseCount('store_settings', 0);
        $page->fillForm([
            'store_name' => 'SABA', 'whatsapp' => '+55 (11) 99999-0000', 'email' => 'saba@example.com',
            'city' => 'São Paulo', 'state' => 'sp', 'shipping_origin_zip_code' => '01001-000',
        ])->call('save')->assertHasNoFormErrors()->assertNotified('Configurações salvas');
        $settings = StoreSetting::query()->sole();
        $this->assertSame('SP', $settings->state);
        $this->assertSame('+55 (11) 99999-0000', $settings->whatsapp);
        Livewire::test(StoreSettings::class)->assertFormSet(['store_name' => 'SABA', 'state' => 'SP', 'whatsapp' => $settings->whatsapp])
            ->fillForm(['store_name' => 'Saba Equipamentos'])->call('save')->assertHasNoFormErrors();
        $this->assertDatabaseCount('store_settings', 1);
        $this->assertSame('Saba Equipamentos', $settings->fresh()->store_name);
    }

    public function test_settings_validation_errors_are_bound_to_form_fields(): void
    {
        Livewire::test(StoreSettings::class)->fillForm(['store_name' => ''])
            ->call('save')->assertHasFormErrors(['store_name' => 'required']);
        Livewire::test(StoreSettings::class)->fillForm(['store_name' => 'SABA', 'shipping_origin_zip_code' => '123'])
            ->call('save')->assertHasFormErrors(['shipping_origin_zip_code']);
        $this->assertDatabaseCount('store_settings', 0);
    }

    public function test_local_admin_routes_render_and_guests_are_redirected(): void
    {
        config(['app.env' => 'local']);
        $this->get('/admin/banners')->assertOk()->assertSee('Banners');
        $this->get('/admin/banners/create')->assertOk()->assertSee('Publicação');
        $this->get('/admin/store-settings')->assertOk()->assertSee('Configurações da loja');
        auth()->logout();
        $this->get('/admin/banners')->assertRedirect('/admin/login');
        $this->get('/admin/store-settings')->assertRedirect('/admin/login');
    }
}

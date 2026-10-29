<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Domain\Access\Models\AdminGroup;
use App\Domain\Settings\Models\StoreSetting;
use App\Filament\Pages\StoreTemplates;
use App\Models\User;
use App\Support\Storefront\Template;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class StoreTemplatesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::bootCurrentPanel();
    }

    public function test_administrator_can_apply_any_theme_without_overwriting_store_data(): void
    {
        $this->actingAs(User::factory()->create());
        $settings = StoreSetting::query()->create(['store_name' => 'Minha Loja', 'color_primary' => '#112233']);

        $page = Livewire::test(StoreTemplates::class)->assertOk();
        $this->assertSame(1, Template::active());
        $page->set('selectedTemplate', 4)->call('apply')->assertHasNoErrors();

        $this->assertSame(4, Template::active());
        $this->assertSame(4, $settings->fresh()->template_id);
        $this->assertSame('Minha Loja', $settings->fresh()->store_name);
        $this->assertSame('#112233', $settings->fresh()->color_primary);

        $page->set('selectedTemplate', 1)->call('apply')->assertHasNoErrors();
        $this->assertSame(1, Template::active());
    }

    public function test_operational_group_cannot_open_or_apply_themes(): void
    {
        $user = User::factory()->create(['admin_group_id' => AdminGroup::query()->where('slug', 'operacional')->value('id')]);
        $this->actingAs($user);
        $this->assertFalse(StoreTemplates::canAccess());
        $this->get(StoreTemplates::getUrl())->assertForbidden();
    }

    public function test_choosing_theme_before_store_settings_exist_creates_valid_settings(): void
    {
        $this->actingAs(User::factory()->create());
        $this->assertDatabaseCount('store_settings', 0);
        Livewire::test(StoreTemplates::class)->set('selectedTemplate', 3)->call('apply')->assertHasNoErrors();
        $this->assertDatabaseCount('store_settings', 1);
        $this->assertSame(3, StoreSetting::query()->sole()->template_id);
    }

    public function test_storefront_renders_three_distinct_visuals(): void
    {
        $this->assertCount(5, Template::available());
        foreach ([3 => 'ct-horizonte-home', 4 => 'ct-essencia-home', 5 => 'ct-pulso-home'] as $id => $marker) {
            StoreSetting::query()->updateOrCreate(['id' => 1], ['template_id' => $id]);
            Template::forgetCurrentRequest();
            $this->get('/')->assertOk()->assertSee($marker, false)
                ->assertSee('css/storefront/templates/creative.css', false);
            $this->get('/produtos')->assertOk();
        }
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Domain\Access\AdminPermissions;
use App\Domain\Access\Models\AdminGroup;
use App\Filament\Pages\AiSettings;
use App\Filament\Pages\OrdersKanban;
use App\Filament\Pages\PaymentMethods;
use App\Filament\Pages\ShippingMethods;
use App\Filament\Pages\StoreSettings;
use App\Filament\Resources\AdminGroups\AdminGroupResource;
use App\Filament\Resources\AdminUsers\AdminUserResource;
use App\Filament\Resources\Banners\BannerResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Products\ProductResource;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminGroupsAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::bootCurrentPanel();
    }

    public function test_built_in_groups_are_available_and_operational_never_gets_settings(): void
    {
        $admin = AdminGroup::query()->where('slug', 'administrador')->sole();
        $operational = AdminGroup::query()->where('slug', 'operacional')->sole();
        $this->assertSame('Administrador', $admin->name);
        $this->assertSame('Operacional', $operational->name);

        $operator = User::factory()->create(['admin_group_id' => $operational->id]);
        foreach (AdminPermissions::operational() as $permission) {
            $this->assertTrue($operator->hasAdminPermission($permission), $permission);
        }
        foreach ([AdminPermissions::SETTINGS_STORE, AdminPermissions::SETTINGS_PAYMENTS, AdminPermissions::SETTINGS_SHIPPING, AdminPermissions::SETTINGS_AI, AdminPermissions::ACCESS] as $permission) {
            $this->assertFalse($operator->hasAdminPermission($permission), $permission);
        }
    }

    public function test_operator_can_operate_orders_and_labels_but_not_settings_or_access_management(): void
    {
        $operator = User::factory()->create(['admin_group_id' => AdminGroup::query()->where('slug', 'operacional')->value('id')]);
        $this->actingAs($operator);
        $this->assertTrue(OrdersKanban::canAccess());
        $this->assertTrue(OrderResource::canViewAny());
        $this->assertTrue(CategoryResource::canViewAny());
        $this->assertTrue(ProductResource::canViewAny());
        $this->assertTrue(BannerResource::canViewAny());
        $this->assertFalse(StoreSettings::canAccess());
        $this->assertFalse(PaymentMethods::canAccess());
        $this->assertFalse(ShippingMethods::canAccess());
        $this->assertFalse(AiSettings::canAccess());
        $this->assertFalse(AdminUserResource::canViewAny());
        $this->assertFalse(AdminGroupResource::canViewAny());

        $this->get(OrderResource::getUrl('index'))->assertOk();
        $this->get(StoreSettings::getUrl())->assertForbidden();
        $this->get(PaymentMethods::getUrl())->assertForbidden();
        $this->get(ShippingMethods::getUrl())->assertForbidden();
        $this->get(AiSettings::getUrl())->assertForbidden();
        $this->get(AdminUserResource::getUrl('index'))->assertForbidden();
        $this->get(AdminGroupResource::getUrl('index'))->assertForbidden();
    }

    public function test_administrator_can_manage_all_modules_and_groups(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);
        foreach (AdminPermissions::all() as $permission) {
            $this->assertTrue($admin->hasAdminPermission($permission), $permission);
        }
        $this->assertTrue(StoreSettings::canAccess());
        $this->assertTrue(ShippingMethods::canAccess());
        $this->assertTrue(AdminUserResource::canViewAny());
        $this->get(AdminUserResource::getUrl('index'))->assertOk();
        $this->get(AdminGroupResource::getUrl('index'))->assertOk();
        $this->assertFalse(AdminGroupResource::canEdit($admin->adminGroup));
    }

    public function test_custom_group_cannot_see_modules_not_checked(): void
    {
        $group = AdminGroup::query()->create(['name' => 'Marketing', 'slug' => 'marketing', 'permissions' => [AdminPermissions::CONTENT_BLOG, AdminPermissions::CONTENT_BANNERS]]);
        $user = User::factory()->create(['admin_group_id' => $group->id]);
        $this->actingAs($user);
        $this->assertTrue(BannerResource::canViewAny());
        $this->assertFalse(ProductResource::canViewAny());
        $this->assertFalse(OrderResource::canViewAny());
        $this->get(ProductResource::getUrl('index'))->assertForbidden();
    }

    public function test_account_without_group_cannot_access_panel(): void
    {
        $user = User::factory()->create(['admin_group_id' => null]);
        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));
        $this->actingAs($user);
        $this->assertFalse(AdminPermissions::check(AdminPermissions::SALES_ORDERS));
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Access\AdminGroupAccessSummary;
use App\Domain\Access\AdminPermissions;
use App\Domain\Access\Models\AdminGroup;
use PHPUnit\Framework\TestCase;

final class AdminGroupAccessSummaryTest extends TestCase
{
    public function test_administrator_shows_all_modules_once(): void
    {
        $group = new AdminGroup(['name' => 'Administrador', 'slug' => 'administrador', 'permissions' => ['invalid']]);
        self::assertSame(count(AdminPermissions::all()), AdminGroupAccessSummary::count($group));
        self::assertSame('Acesso completo', AdminGroupAccessSummary::headline($group));
        self::assertSame('Padrão', AdminGroupAccessSummary::type($group));
    }

    public function test_operational_is_not_allowed_to_change_settings_or_access(): void
    {
        $group = new AdminGroup(['name' => 'Operacional', 'slug' => 'operacional', 'permissions' => AdminPermissions::all()]);
        self::assertSame(count(AdminPermissions::operational()), AdminGroupAccessSummary::count($group));
        self::assertSame('Acesso operacional', AdminGroupAccessSummary::headline($group));

        $sections = AdminGroupAccessSummary::groupedPermissions($group);
        foreach ($sections['Configurações'] as $permission) {
            self::assertFalse($permission['granted'], $permission['label']);
        }
        foreach ($sections['Administração'] as $permission) {
            self::assertFalse($permission['granted'], $permission['label']);
        }
    }

    public function test_custom_group_shows_valid_unique_permissions(): void
    {
        $group = new AdminGroup([
            'name' => 'Marketing',
            'slug' => 'marketing',
            'permissions' => [AdminPermissions::CONTENT_BANNERS, AdminPermissions::CONTENT_BANNERS, AdminPermissions::CONTENT_BLOG, 'wrong_key'],
        ]);

        self::assertSame(2, AdminGroupAccessSummary::count($group));
        self::assertSame('2 de 13 módulos liberados', AdminGroupAccessSummary::detail($group));
        self::assertSame('Personalizado', AdminGroupAccessSummary::type($group));

        $sections = AdminGroupAccessSummary::groupedPermissions($group);
        self::assertTrue($sections['Conteúdo'][0]['granted']);
        self::assertTrue($sections['Conteúdo'][1]['granted']);
        self::assertFalse($sections['Vendas'][0]['granted']);
    }
}

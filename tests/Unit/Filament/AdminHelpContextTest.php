<?php

declare(strict_types=1);

namespace Tests\Unit\Filament;

use App\Filament\Support\AdminHelpContext;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AdminHelpContextTest extends TestCase
{
    public static function routes(): array
    {
        return [
            ['filament.admin.resources.products.create', 'produtos'],
            ['filament.admin.resources.products.edit', 'produtos'],
            ['filament.admin.resources.categories.index', 'categorias'],
            ['filament.admin.resources.banners.index', 'banners'],
            ['filament.admin.resources.blog-posts.create', 'blog'],
            ['filament.admin.resources.coupons.edit', 'cupons'],
            ['filament.admin.resources.customers.view', 'clientes'],
            ['filament.admin.resources.orders.view', 'pedidos'],
            ['filament.admin.pages.orders-kanban', 'pedidos'],
            ['filament.admin.pages.shipping-methods', 'frete'],
            ['filament.admin.pages.payment-methods', 'pagamentos'],
            ['filament.admin.pages.store-settings', 'configurar-loja'],
            ['filament.admin.pages.ai-settings', 'inteligencia-artificial'],
            ['filament.admin.pages.dashboard', 'painel'],
            [null, 'primeiros-passos'],
            ['filament.admin.pages.qualquer-outra', 'primeiros-passos'],
        ];
    }

    #[DataProvider('routes')]
    public function test_the_shortcut_opens_the_right_guide(?string $route, string $expected): void
    {
        $this->assertSame($expected, AdminHelpContext::topicForRoute($route));
    }
}

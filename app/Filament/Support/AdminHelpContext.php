<?php

declare(strict_types=1);

namespace App\Filament\Support;

/** Match the current Filament page to an existing user guide. */
final class AdminHelpContext
{
    public static function topicForRoute(?string $routeName): string
    {
        $routeName ??= '';

        if (str_contains($routeName, '.resources.products.')) {
            return 'produtos';
        }
        if (str_contains($routeName, '.resources.categories.')) {
            return 'categorias';
        }
        if (str_contains($routeName, '.resources.banners.')) {
            return 'banners';
        }
        if (str_contains($routeName, '.resources.blog-posts.')) {
            return 'blog';
        }
        if (str_contains($routeName, '.resources.coupons.')) {
            return 'cupons';
        }
        if (str_contains($routeName, '.resources.customers.')) {
            return 'clientes';
        }
        if (str_contains($routeName, '.resources.orders.')) {
            return 'pedidos';
        }
        if (str_contains($routeName, '.pages.orders-kanban')) {
            return 'pedidos';
        }
        if (str_contains($routeName, '.pages.store-settings')) {
            return 'configurar-loja';
        }
        if (str_contains($routeName, '.pages.ai-settings')) {
            return 'inteligencia-artificial';
        }
        if (str_contains($routeName, '.pages.payment-methods')) {
            return 'pagamentos';
        }
        if (str_contains($routeName, '.pages.shipping-methods')) {
            return 'frete';
        }
        if (str_contains($routeName, '.resources.admin-users.') || str_contains($routeName, '.resources.admin-groups.')) {
            return 'acessos-seguranca';
        }
        if (str_contains($routeName, '.pages.dashboard')) {
            return 'painel';
        }

        return 'primeiros-passos';
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Access;

use App\Models\User;

/** Permissões administrativas por módulo; nunca aplicadas ao guard de clientes. */
final class AdminPermissions
{
    public const CATALOG_CATEGORIES = 'categories';
    public const CATALOG_PRODUCTS = 'products';
    public const CONTENT_BANNERS = 'banners';
    public const CONTENT_BLOG = 'blog';
    public const SALES_COUPONS = 'coupons';
    public const SALES_CUSTOMERS = 'customers';
    public const SALES_ORDERS = 'orders';
    public const SALES_FINANCE = 'finance';
    public const SETTINGS_STORE = 'settings_store';
    public const SETTINGS_PAYMENTS = 'settings_payments';
    public const SETTINGS_SHIPPING = 'settings_shipping';
    public const SETTINGS_AI = 'settings_ai';
    public const ACCESS = 'access_management';

    public static function labels(): array
    {
        return [
            self::CATALOG_CATEGORIES => 'Catálogo — Categorias',
            self::CATALOG_PRODUCTS => 'Catálogo — Produtos',
            self::CONTENT_BANNERS => 'Conteúdo — Banners',
            self::CONTENT_BLOG => 'Conteúdo — Blog',
            self::SALES_COUPONS => 'Vendas — Cupons',
            self::SALES_CUSTOMERS => 'Vendas — Clientes',
            self::SALES_ORDERS => 'Vendas — Pedidos, Kanban e etiquetas',
            self::SALES_FINANCE => 'Financeiro — Fluxo de caixa',
            self::SETTINGS_STORE => 'Configurações — Loja e identidade visual',
            self::SETTINGS_PAYMENTS => 'Configurações — Meios de pagamento (Asaas)',
            self::SETTINGS_SHIPPING => 'Configurações — Meios de entrega (Melhor Envio)',
            self::SETTINGS_AI => 'Configurações — IA (Groq)',
            self::ACCESS => 'Administração — Usuários e grupos',
        ];
    }

    public static function operational(): array
    {
        return [
            self::CATALOG_CATEGORIES, self::CATALOG_PRODUCTS,
            self::CONTENT_BANNERS, self::CONTENT_BLOG,
            self::SALES_COUPONS, self::SALES_CUSTOMERS,
            self::SALES_ORDERS, self::SALES_FINANCE,
        ];
    }

    public static function all(): array
    {
        return array_keys(self::labels());
    }

    public static function allowed(?User $user, string $permission): bool
    {
        if (! $user) {
            return false;
        }

        $group = $user->adminGroup;

        if (! $group) {
            return false;
        }

        // Grupo de sistema imutável: não depende de valores de checkbox no banco.
        if ($group->slug === 'administrador') {
            return true;
        }

        // Grupo Operacional é fixo: nenhuma configuração ou gestão de acessos.
        if ($group->slug === 'operacional') {
            return in_array($permission, self::operational(), true);
        }

        return in_array($permission, (array) $group->permissions, true)
            && array_key_exists($permission, self::labels());
    }

    public static function check(string $permission): bool
    {
        $user = auth('web')->user();

        return self::allowed($user instanceof User ? $user : null, $permission);
    }
}

<?php

declare(strict_types=1);

namespace App\Support\Storefront;

final class Template
{
    public static function active(): int
    {
        $id = (int) config('storefront_templates.active', 1);

        // Se alguém informar um ID removido no .env, a loja continua funcionando.
        return array_key_exists($id, config('storefront_templates.available', [])) ? $id : 1;
    }

    public static function is(int $id): bool
    {
        return self::active() === $id;
    }
}

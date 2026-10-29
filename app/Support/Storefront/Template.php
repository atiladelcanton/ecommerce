<?php

declare(strict_types=1);

namespace App\Support\Storefront;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Storefront appearance is independent of catalog, orders and payments. */
final class Template
{
    private const REQUEST_KEY = 'storefront.active_template_id';

    public static function available(): array
    {
        return config('storefront_templates.available', []);
    }

    public static function active(): int
    {
        $request = app()->bound('request') ? request() : null;
        if ($request?->attributes->has(self::REQUEST_KEY)) {
            return (int) $request->attributes->get(self::REQUEST_KEY);
        }

        $configured = (int) config('storefront_templates.active', 1);
        $active = array_key_exists($configured, self::available()) ? $configured : 1;

        // Existing installations without this migration still work with the .env value.
        try {
            if (Schema::hasTable('store_settings') && Schema::hasColumn('store_settings', 'template_id')) {
                $saved = DB::table('store_settings')->where('id', 1)->value('template_id');
                if ($saved !== null && array_key_exists((int) $saved, self::available())) {
                    $active = (int) $saved;
                }
            }
        } catch (QueryException) {
            // Before migrations, e.g. during deployment or fresh install.
        }

        $request?->attributes->set(self::REQUEST_KEY, $active);

        return $active;
    }

    public static function is(int $id): bool
    {
        return self::active() === $id;
    }

    public static function forgetCurrentRequest(): void
    {
        if (app()->bound('request')) {
            request()->attributes->remove(self::REQUEST_KEY);
        }
    }
}

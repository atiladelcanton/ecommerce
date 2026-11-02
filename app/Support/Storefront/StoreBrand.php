<?php

declare(strict_types=1);

namespace App\Support\Storefront;

use App\Domain\Settings\Models\StoreSetting;
use Illuminate\Database\QueryException;

/** Identity shared by the storefront and the Filament panel. */
final class StoreBrand
{
    /**
     * The panel is also bootstrapped by Artisan before migrations run.
     * Resolve settings only when a request actually needs the brand.
     */
    public static function settings(): ?StoreSetting
    {
        try {
            return StoreSetting::query()->find(StoreSetting::MAIN_ID);
        } catch (QueryException) {
            return null;
        }
    }

    public static function name(?StoreSetting $settings): string
    {
        $name = trim((string) ($settings?->store_name ?? ''));

        return $name !== '' ? $name : (trim((string) config('app.name', 'Minha loja')) ?: 'Minha loja');
    }

    public static function currentName(): string
    {
        return self::name(self::settings());
    }
}

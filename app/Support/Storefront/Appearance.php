<?php

declare(strict_types=1);

namespace App\Support\Storefront;

use App\Domain\Settings\Models\StoreSetting;
use Illuminate\Support\Facades\Storage;

/** Visual configuration scoped to the public storefront, never to Filament. */
final class Appearance
{
    public static function logoUrl(?StoreSetting $settings): string
    {
        $path = $settings?->logo_path;

        if (is_string($path) && preg_match('~^store/branding/[a-zA-Z0-9_-]+\.(?:jpg|jpeg|png|webp)$~i', $path)) {
            return Storage::disk('public')->url($path);
        }

        return asset('images/saba-logo.jpeg');
    }

    /** @return array{primary: string, secondary: string, button: string, button_text: string, css: string} */
    public static function forTemplate(?StoreSetting $settings, int $template): array
    {
        // Preserve each template's original visual identity if the merchant has not customized it.
        $defaults = $template === 2
            ? ['primary' => '#156583', 'secondary' => '#29353E', 'button' => '#156583']
            : ['primary' => '#DC2626', 'secondary' => '#DC2626', 'button' => '#DC2626'];

        $primary = self::validHex($settings?->color_primary) ?? $defaults['primary'];
        $secondary = self::validHex($settings?->color_secondary) ?? $defaults['secondary'];
        $button = self::validHex($settings?->color_button) ?? $defaults['button'];
        $contrastButton = self::contrast($button);
        $primaryDark = self::validHex($settings?->color_primary) === null
            ? ($template === 2 ? '#104D67' : '#B91C1C')
            : self::darken($primary);

        // Strictly validated colors only: safe for inline CSS, no untrusted CSS values.
        $css = implode(';', [
            '--color-store-red:'.$primary,
            '--color-store-red-dark:'.$primaryDark,
            '--color-store-primary-contrast:'.self::contrast($primary),
            '--color-store-secondary:'.$secondary,
            '--color-store-secondary-contrast:'.self::contrast($secondary),
            '--color-store-button:'.$button,
            '--color-store-button-hover:'.self::darken($button),
            '--color-store-button-text:'.$contrastButton,
            '--color-store-button-hover-text:'.self::contrast(self::darken($button)),
        ]);

        return compact('primary', 'secondary', 'button') + ['button_text' => $contrastButton, 'css' => $css];
    }

    private static function validHex(?string $hex): ?string
    {
        return is_string($hex) && preg_match('/^#[0-9a-fA-F]{6}$/D', $hex)
            ? strtoupper($hex)
            : null;
    }

    private static function darken(string $hex): string
    {
        $channels = [];
        foreach ([1, 3, 5] as $offset) {
            $channels[] = (int) round(hexdec(substr($hex, $offset, 2)) * 0.80);
        }

        return sprintf('#%02X%02X%02X', ...$channels);
    }

    private static function contrast(string $hex): string
    {
        $channels = [];
        foreach ([1, 3, 5] as $offset) {
            $channel = hexdec(substr($hex, $offset, 2)) / 255;
            $channels[] = $channel <= 0.04045 ? $channel / 12.92 : (($channel + 0.055) / 1.055) ** 2.4;
        }
        $luminance = 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];

        return ($luminance + 0.05) / 0.05 >= 1.05 / ($luminance + 0.05) ? '#000000' : '#FFFFFF';
    }
}

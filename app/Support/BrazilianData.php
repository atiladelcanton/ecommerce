<?php

declare(strict_types=1);

namespace App\Support;

final class BrazilianData
{
    public static function digits(?string $value): string
    {
        return preg_replace('/\D+/', '', (string) $value) ?? '';
    }

    public static function phone(?string $value): string
    {
        $digits = substr(self::digits($value), 0, 11);
        if (strlen($digits) <= 10) {
            return preg_replace('/^(\d{0,2})(\d{0,4})(\d{0,4}).*/', '($1) $2-$3', $digits) ?? $digits;
        }

        return preg_replace('/^(\d{2})(\d{5})(\d{4}).*/', '($1) $2-$3', $digits) ?? $digits;
    }

    public static function document(?string $value): string
    {
        $digits = substr(self::digits($value), 0, 14);
        if (strlen($digits) <= 11) {
            return preg_replace('/^(\d{0,3})(\d{0,3})(\d{0,3})(\d{0,2}).*/', '$1.$2.$3-$4', $digits) ?? $digits;
        }

        return preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2}).*/', '$1.$2.$3/$4-$5', $digits) ?? $digits;
    }

    public static function postalCode(?string $value): string
    {
        $digits = substr(self::digits($value), 0, 8);
        return strlen($digits) > 5 ? substr($digits, 0, 5).'-'.substr($digits, 5) : $digits;
    }
}

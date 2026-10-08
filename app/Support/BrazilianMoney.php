<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

final class BrazilianMoney
{
    public static function format(?int $cents): ?string
    {
        if ($cents === null) {
            return null;
        }
        $whole = (string) intdiv($cents, 100);

        return preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $whole).','.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function display(int $cents): string
    {
        return 'R$ '.self::format($cents);
    }

    public static function parse(?string $value): ?int
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        $value = trim($value);
        if (! preg_match('/^(-?)(\d+|\d{1,3}(?:\.\d{3})+)(?:,(\d{1,2}))?$/D', $value, $matches)) {
            throw new InvalidArgumentException('Informe um valor em reais, como 12.900,00.');
        }
        $digits = ltrim(str_replace('.', '', $matches[2]).str_pad($matches[3] ?? '', 2, '0'), '0');
        $maximum = (string) PHP_INT_MAX;
        if (strlen($digits) > strlen($maximum) || (strlen($digits) === strlen($maximum) && strcmp($digits, $maximum) > 0)) {
            throw new InvalidArgumentException('O valor informado é muito alto.');
        }

        return ($matches[1] === '-' ? -1 : 1) * (int) $digits;
    }
}

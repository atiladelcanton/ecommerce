<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Data;

use InvalidArgumentException;

/** Exact decimal JSON number at the integration boundary; never a PHP float. */
final readonly class ApiMoney
{
    public function __construct(public int $cents)
    {
        if ($cents < 0) {
            throw new InvalidArgumentException('Valor monetário inválido.');
        }
    }

    public function decimal(): string
    {
        return intdiv($this->cents, 100).'.'.str_pad((string) ($this->cents % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function cents(mixed $value): int
    {
        if (! is_string($value) && ! is_int($value)) {
            throw new InvalidArgumentException('Valor monetário inválido na resposta de frete.');
        }
        if (! preg_match('/^(\d+)(?:\.(\d{1,2}))?$/D', (string) $value, $parts)) {
            throw new InvalidArgumentException('Valor monetário inválido na resposta de frete.');
        }
        $digits = ltrim($parts[1].str_pad($parts[2] ?? '', 2, '0'), '0') ?: '0';
        if (strlen($digits) > strlen((string) PHP_INT_MAX) || (strlen($digits) === strlen((string) PHP_INT_MAX) && strcmp($digits, (string) PHP_INT_MAX) > 0)) {
            throw new InvalidArgumentException('Valor monetário muito alto.');
        }

        return (int) $digits;
    }

    public static function json(mixed $value): string
    {
        if ($value instanceof self) {
            return $value->decimal();
        }
        if (! is_array($value)) {
            return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        if (array_is_list($value)) {
            return '['.implode(',', array_map(self::json(...), $value)).']';
        }
        $parts = [];
        foreach ($value as $key => $item) {
            $parts[] = json_encode((string) $key, JSON_THROW_ON_ERROR).':'.self::json($item);
        }

        return '{'.implode(',', $parts).'}';
    }
}

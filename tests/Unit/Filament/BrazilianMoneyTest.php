<?php

declare(strict_types=1);

namespace Tests\Unit\Filament;

use App\Support\BrazilianMoney;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BrazilianMoneyTest extends TestCase
{
    public static function amounts(): array
    {
        return [
            ['12.900,00', 1290000], ['12900,00', 1290000], ['0,01', 1], ['0', 0],
            ['1,5', 150], ['12', 1200], [null, null], ['', null], ['-0,01', -1],
            ['92.233.720.368.547.758,07', PHP_INT_MAX],
        ];
    }

    #[DataProvider('amounts')]
    public function test_conversion_is_exact(?string $input, ?int $expected): void
    {
        $this->assertSame($expected, BrazilianMoney::parse($input));
    }

    public function test_format_and_parse_round_trip_without_float(): void
    {
        foreach ([0, 1, 1290000, PHP_INT_MAX] as $cents) {
            $this->assertSame($cents, BrazilianMoney::parse(BrazilianMoney::format($cents)));
        }
    }
}

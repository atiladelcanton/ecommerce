<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\BrazilianData;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BrazilianDataTest extends TestCase
{
    public static function formats(): array
    {
        return [
            ['phone', '41999991234', '(41) 99999-1234'],
            ['phone', '4133334444', '(41) 3333-4444'],
            ['document', '52998224725', '529.982.247-25'],
            ['document', '12345678000199', '12.345.678/0001-99'],
            ['postalCode', '80000000', '80000-000'],
        ];
    }

    #[DataProvider('formats')]
    public function test_formats_brazilian_values(string $method, string $input, string $expected): void
    {
        $this->assertSame($expected, BrazilianData::{$method}($input));
    }

    public function test_digits_removes_masks(): void
    {
        $this->assertSame('52998224725', BrazilianData::digits('529.982.247-25'));
    }
}

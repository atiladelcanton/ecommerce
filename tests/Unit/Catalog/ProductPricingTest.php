<?php

declare(strict_types=1);

namespace Tests\Unit\Catalog;

use App\Domain\Catalog\ValueObjects\ProductPricing;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ProductPricingTest extends TestCase
{
    public function test_fractional_discount_is_rounded_down(): void
    {
        $this->assertSame(33, (new ProductPricing(300, 200))->discountPercentage());
    }

    public function test_bigint_prices_do_not_overflow_or_convert_to_float(): void
    {
        $pricing = new ProductPricing(PHP_INT_MAX, 1);
        $this->assertSame(1, $pricing->finalPrice());
        $this->assertSame(99, $pricing->discountPercentage());
    }

    public function test_zero_regular_price_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('O preço de venda deve ser maior que zero.');
        new ProductPricing(0);
    }

    public function test_domain_object_rejects_invalid_promotion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ProductPricing(100, 100);
    }
}

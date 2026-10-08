<?php

declare(strict_types=1);

namespace App\Domain\Catalog\ValueObjects;

use App\Domain\Catalog\Exceptions\InvalidProductPrice;

final readonly class ProductPricing
{
    public function __construct(public int $regularPrice, public ?int $promotionalPrice = null)
    {
        if ($regularPrice <= 0) {
            throw new InvalidProductPrice('price', 'O preço de venda deve ser maior que zero.');
        }
        if ($promotionalPrice !== null && $promotionalPrice <= 0) {
            throw new InvalidProductPrice('promotional_price', 'O preço promocional deve ser maior que zero.');
        }
        if ($promotionalPrice !== null && $promotionalPrice >= $regularPrice) {
            throw new InvalidProductPrice('promotional_price', 'O preço promocional deve ser menor que o preço de venda.');
        }
    }

    public function finalPrice(): int
    {
        return $this->promotionalPrice ?? $this->regularPrice;
    }

    public function hasPromotion(): bool
    {
        return $this->promotionalPrice !== null;
    }

    /** Whole percentage, rounded down; money remains integer cents. */
    public function discountPercentage(): int
    {
        if (! $this->hasPromotion()) {
            return 0;
        }

        // Avoid multiplication overflow even for BIGINT prices.
        $saving = $this->regularPrice - $this->finalPrice();
        $percentage = 0;
        $remainder = 0;
        for ($i = 0; $i < 100; $i++) {
            if ($remainder >= $this->regularPrice - $saving) {
                $remainder -= $this->regularPrice - $saving;
                $percentage++;
            } else {
                $remainder += $saving;
            }
        }

        return $percentage;
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Catalog\Enums\ProductAvailability;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
final class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => 'SABA '.fake()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'sku' => fake()->unique()->bothify('SABA-#####??'),
            'availability' => ProductAvailability::IMMEDIATE,
            'cost_price' => 50000,
            'price' => 129990,
            'promotional_price' => null,
            'stock' => 10,
            'weight' => '15.500',
            'height' => '80.00',
            'width' => '60.00',
            'length' => '90.00',
            'is_active' => true,
            'is_featured' => false,
            'whatsapp_quote_enabled' => true,
        ];
    }

    public function onPromotion(): static
    {
        return $this->state(fn (array $attributes): array => ['promotional_price' => max(1, intdiv($attributes['price'], 2))]);
    }
}

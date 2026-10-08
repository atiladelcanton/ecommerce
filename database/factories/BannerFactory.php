<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Catalog\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Banner> */
final class BannerFactory extends Factory
{
    protected $model = Banner::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'desktop_image' => 'banners/'.fake()->uuid().'.jpg',
            'mobile_image' => null,
            'link' => null,
            'alt_text' => 'Banner de demonstração',
            'sort_order' => 0,
            'is_active' => true,
            'starts_at' => null,
            'ends_at' => null,
        ];
    }
}

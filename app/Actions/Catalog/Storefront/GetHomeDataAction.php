<?php

declare(strict_types=1);

namespace App\Actions\Catalog\Storefront;
use App\Domain\Catalog\Models\Banner;
use App\Domain\Catalog\Models\Category;
final class GetHomeDataAction
{
    public function __construct(private readonly PublicProducts $products) {}
    public function execute(): array
    {
        return [
            'banners' => Banner::currentlyVisible()->ordered()->get(),
            'categories' => Category::active()->ordered()->get(['id', 'name', 'slug']),
            'featured' => $this->products->query()->featured()->latest()->limit(8)->get(),
            'promotions' => $this->products->query()->onPromotion()->latest()->limit(8)->get(),
            'recent' => $this->products->query()->latest()->limit(8)->get(),
        ];
    }
}

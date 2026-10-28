<?php

declare(strict_types=1);

namespace App\Actions\Catalog\Storefront;

use App\Actions\Blog\GetPublicBlogAction;
use App\Domain\Catalog\Models\Banner;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Settings\Models\StoreSetting;

final class GetHomeDataAction
{
    public function __construct(private readonly PublicProducts $products, private readonly GetPublicBlogAction $blog) {}

    public function execute(): array
    {
        $promotions = $this->products->query()->onPromotion()->latest()->limit(8)->get();
        // Eight displayed promotions can overlap with at most eight of these candidates.
        $featured = $this->products->query()->featured()->latest()->limit(16)->get()
            ->sortBy(fn (Product $product): int => $promotions->contains('id', $product->id) ? 1 : 0)
            ->take(8)->values();

        $settings = StoreSetting::query()->find(StoreSetting::MAIN_ID);
        $latestPosts = $settings?->blog_enabled && $settings?->blog_show_on_home
            ? $this->blog->latest(3)
            : collect();

        return [
            'storeName' => $settings?->store_name ?: config('app.name', 'Saba Equipamentos'),
            'latestBlogPosts' => $latestPosts,
            'banners' => Banner::currentlyVisible()->ordered()->get(),
            'categories' => Category::active()->ordered()->get(['id', 'name', 'slug', 'image_path']),
            'featured' => $featured,
            'promotions' => $promotions,
        ];
    }
}

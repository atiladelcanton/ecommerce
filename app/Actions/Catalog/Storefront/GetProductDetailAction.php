<?php

declare(strict_types=1);

namespace App\Actions\Catalog\Storefront;
use App\Domain\Settings\Models\StoreSetting;
use App\Support\Storefront\Links;
use App\Support\Storefront\ProductMetadata;
final class GetProductDetailAction
{
    public function __construct(private readonly PublicProducts $products) {}
    public function execute(string $slug): array
    {
        $product = $this->products->query(gallery: true)->where('slug', $slug)->firstOrFail();
        $settings = StoreSetting::query()->find(StoreSetting::MAIN_ID);
        $url = route('store.product', $product->slug);
        return [
            'product' => $product,
            'related' => $this->products->query()->where('category_id', $product->category_id)->whereKeyNot($product->id)->latest()->limit(4)->get(),
            'youtubeEmbed' => Links::youtube($product->youtube_url),
            'quoteUrl' => $product->whatsapp_quote_enabled ? Links::whatsapp($settings?->whatsapp, "Olá! Tenho interesse neste produto:\n\n{$product->name}\n\n{$url}\n\nGostaria de mais informações e de uma cotação.") : null,
            'structuredData' => ProductMetadata::make($product, $url),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Catalog\Storefront;

use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Settings\Models\StoreSetting;
use App\Support\Storefront\Links;
use App\Support\Storefront\ProductMetadata;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class GetProductDetailAction
{
    public function __construct(private readonly PublicProducts $products) {}

    public function execute(string $slug): array
    {
        $product = $this->products->query(gallery: true)->with([
            'variants' => fn (HasMany $query) => $query->where('name', 'Cor')->where('is_active', true),
        ])->where('slug', $slug)->firstOrFail();
        $settings = StoreSetting::query()->find(StoreSetting::MAIN_ID);
        $url = route('store.product', $product->slug);
        $quoteMessage = fn (?ProductVariant $variant = null): string => "Olá! Tenho interesse neste produto:\n\n{$product->name}"
            .($variant ? "\nCor: {$variant->value}" : '')
            ."\n\n{$url}\n\nGostaria de mais informações e de uma cotação.";
        $variantQuoteUrls = $product->whatsapp_quote_enabled
            ? $product->variants->mapWithKeys(fn (ProductVariant $variant): array => [
                $variant->id => Links::whatsapp($settings?->whatsapp, $quoteMessage($variant)),
            ])->filter()->all()
            : [];

        return [
            'product' => $product,
            'related' => $this->products->query()->where('category_id', $product->category_id)->whereKeyNot($product->id)->latest()->limit(4)->get(),
            'youtubeEmbed' => Links::youtube($product->youtube_url),
            'quoteUrl' => $product->whatsapp_quote_enabled && $product->variants->isEmpty()
                ? Links::whatsapp($settings?->whatsapp, $quoteMessage()) : null,
            'variantQuoteUrls' => $variantQuoteUrls,
            'structuredData' => ProductMetadata::make($product, $url),
        ];
    }
}

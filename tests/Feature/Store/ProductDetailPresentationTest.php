<?php

declare(strict_types=1);

namespace Tests\Feature\Store;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImage;
use App\Support\BrazilianMoney;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductDetailPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_promotion_uses_existing_domain_values_and_visible_price_labels(): void
    {
        $product = Product::factory()->create(['price' => 1290000, 'promotional_price' => 1150000]);

        $this->get(route('store.product', $product->slug))->assertOk()
            ->assertSee('store-product-detail', false)
            ->assertSeeInOrder(['De:', BrazilianMoney::display($product->regular_price), 'Por:', BrazilianMoney::display($product->final_price)])
            ->assertSee('Economize '.$product->discount_percentage.'%')
            ->assertSee('<del>'.BrazilianMoney::display($product->regular_price).'</del>', false);
    }

    public function test_regular_product_has_no_promotion_selector_or_empty_content_sections(): void
    {
        $product = Product::factory()->create(['sku' => null]);

        $this->get(route('store.product', $product->slug))->assertOk()
            ->assertSee(BrazilianMoney::display($product->final_price))
            ->assertDontSee('product-detail-original', false)->assertDontSee('Economize')
            ->assertDontSee('data-color-selector', false)->assertDontSee('SKU:')
            ->assertDontSee('product-description-title', false)
            ->assertDontSee('product-video-title', false)
            ->assertDontSee('product-related-title', false);
    }

    public function test_gallery_preserves_images_and_only_shows_thumbnails_for_multiple_photos(): void
    {
        $product = Product::factory()->create();
        ProductImage::factory()->for($product)->create(['is_primary' => true, 'alt_text' => 'Equipamento completo']);
        $url = route('store.product', $product->slug);

        $this->get($url)->assertOk()->assertSee('Equipamento completo')
            ->assertSee('object-contain', false)->assertDontSee('product-detail-thumbnails', false);

        ProductImage::factory()->for($product)->create(['alt_text' => 'Vista lateral']);
        $this->get($url)->assertOk()->assertSee('product-detail-thumbnails', false)
            ->assertSee('Ver foto 2 de '.$product->name)
            ->assertSee('@click="selected = 1"', false)
            ->assertSee('Vista lateral');
    }

    public function test_description_video_and_related_cards_use_existing_data(): void
    {
        $product = Product::factory()->create([
            'description' => 'Detalhes técnicos do equipamento.',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
        Product::factory()->count(5)->create(['category_id' => $product->category_id]);

        $response = $this->get(route('store.product', $product->slug))->assertOk()
            ->assertSee('Descrição')->assertSee($product->description)
            ->assertSee('Veja o equipamento em funcionamento')
            ->assertSee('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', false)
            ->assertSee('loading="lazy"', false)->assertSee('Você também pode gostar');

        $this->assertCount(4, $response->viewData('related'));
        $this->assertFalse($response->viewData('related')->contains('id', $product->id));
    }
}

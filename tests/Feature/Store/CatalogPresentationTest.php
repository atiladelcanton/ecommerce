<?php

declare(strict_types=1);

namespace Tests\Feature\Store;

use App\Actions\Catalog\Storefront\GetHomeDataAction;
use App\Domain\Catalog\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CatalogPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_limits_sections_and_prioritizes_distinct_featured_products(): void
    {
        $regular = Product::factory()->count(9)->create(['is_featured' => true, 'created_at' => now()->subDay()]);
        Product::factory()->count(9)->onPromotion()->create(['is_featured' => true, 'created_at' => now()]);
        Product::factory()->onPromotion()->create(['is_active' => false, 'is_featured' => true]);

        $data = app(GetHomeDataAction::class)->execute();
        $this->assertCount(8, $data['featured']);
        $this->assertCount(8, $data['promotions']);
        $this->assertEmpty(array_intersect($data['featured']->modelKeys(), $data['promotions']->modelKeys()));
        // One promotion outside the displayed eight is also a valid distinct candidate.
        $this->assertGreaterThanOrEqual(7, count(array_intersect($regular->modelKeys(), $data['featured']->modelKeys())));
        $this->get('/')->assertOk()->assertSee('Produtos em destaque')->assertSee('Promoções')
            ->assertDontSee('Conheça nossos equipamentos');
    }

    public function test_featured_can_overlap_when_there_are_not_enough_distinct_products(): void
    {
        Product::factory()->count(2)->onPromotion()->create(['is_featured' => true]);
        $data = app(GetHomeDataAction::class)->execute();
        $this->assertCount(2, $data['featured']);
        $this->assertCount(2, $data['promotions']);
        $this->assertEqualsCanonicalizing($data['featured']->modelKeys(), $data['promotions']->modelKeys());
    }

    public function test_no_promotions_means_no_empty_promotions_section(): void
    {
        Product::factory()->create(['is_featured' => true]);
        $this->get('/')->assertOk()->assertDontSee('data-section="promotions"', false);
    }

    public function test_product_card_markup_is_identical_on_home_listing_and_related_products(): void
    {
        $product = Product::factory()->onPromotion()->create(['is_featured' => true]);
        $other = Product::factory()->create(['category_id' => $product->category_id]);
        $cards = [];
        foreach (['/', '/produtos', route('store.product', $other->slug)] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            preg_match('/<article[^>]*data-product-id="'.$product->id.'"[^>]*>.*?<\/article>/s', $html, $match);
            $this->assertNotEmpty($match);
            // Livewire adds morph markers to otherwise identical card markup.
            $markup = preg_replace('/<!--.*?-->/s', '', $match[0]);
            $cards[] = preg_replace('/>\s+</', '><', trim($markup));
        }
        $this->assertSame($cards[0], $cards[1]);
        $this->assertSame($cards[0], $cards[2]);
    }

    public function test_product_detail_renders_formatted_description_without_active_html(): void
    {
        $product = Product::factory()->create([
            'description' => '<h2>Benefícios</h2><p><strong>Robusto</strong> ✅</p><ul><li>Fácil operação</li></ul><script>alert(123456)</script>',
        ]);
        $this->get(route('store.product', $product->slug))->assertOk()
            ->assertSee('<h2>Benefícios</h2>', false)->assertSee('<strong>Robusto</strong>', false)
            ->assertSee('<ul><li>Fácil operação</li></ul>', false)
            ->assertDontSee('<script>alert(123456)</script>', false);
    }
}

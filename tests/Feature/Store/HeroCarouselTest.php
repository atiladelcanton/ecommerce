<?php

declare(strict_types=1);

namespace Tests\Feature\Store;

use App\Domain\Catalog\Models\Banner;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class HeroCarouselTest extends TestCase
{
    public function test_multiple_banners_preserve_images_links_and_independent_controls(): void
    {
        $banners = collect([
            Banner::factory()->make(['desktop_image' => 'banners/desktop.jpg', 'mobile_image' => 'banners/mobile.jpg', 'link' => 'https://example.com/offer']),
            Banner::factory()->make(['link' => null]),
        ]);
        $html = Blade::render('<x-store.hero-carousel :banners="$banners" />', ['banners' => $banners]);
        $this->assertStringContainsString('storeCarousel(2)', $html);
        $this->assertStringContainsString('banners/desktop.jpg', $html);
        $this->assertStringContainsString('banners/mobile.jpg', $html);
        $this->assertStringContainsString('object-contain', $html);
        $this->assertStringContainsString('aria-label="Banner anterior"', $html);
        $this->assertStringContainsString('aria-label="Próximo banner"', $html);
        $this->assertStringContainsString('aria-label="Ver banner 2"', $html);
        $this->assertStringContainsString('@touchend.passive="touchEnd($event)"', $html);

        $document = new DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
        $xpath = new DOMXPath($document);
        $this->assertSame(1, $xpath->query('//a')->length);
        $this->assertSame('https://example.com/offer', $xpath->query('//a')->item(0)->getAttribute('href'));
        $this->assertSame(0, $xpath->query('//button/ancestor::a')->length);
        $this->assertSame(1, $xpath->query('//*[@data-initial-active]')->length);
        $this->assertSame(1, $xpath->query('//*[@inert and @aria-hidden="true"]')->length);
    }

    public function test_single_unlinked_banner_has_no_unnecessary_controls(): void
    {
        $html = Blade::render('<x-store.hero-carousel :banners="$banners" />', [
            'banners' => collect([Banner::factory()->make(['link' => null])]),
        ]);
        $this->assertStringContainsString('storeCarousel(1)', $html);
        $this->assertStringNotContainsString('<button', $html);
        $this->assertStringNotContainsString('<a ', $html);
        $this->assertStringContainsString('data-initial-active', $html);
    }

    public function test_hero_uses_real_dimensions_of_uploaded_banner_when_available(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('banners/one-pixel.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/Px0AAAAASUVORK5CYII='
        ));
        $html = Blade::render('<x-store.hero-carousel :banners="$banners" />', [
            'banners' => collect([Banner::factory()->make(['desktop_image' => 'banners/one-pixel.png', 'mobile_image' => null])]),
        ]);
        $this->assertStringContainsString('--saba-hero-ratio: 1 / 1', $html);
        $this->assertStringContainsString('object-contain', $html);
    }

    public function test_empty_banner_collection_does_not_render_a_carousel(): void
    {
        $html = Blade::render('<x-store.hero-carousel :banners="$banners" />', ['banners' => collect()]);
        $this->assertStringNotContainsString('<section', $html);
    }
}

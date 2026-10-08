<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Actions\Catalog\CreateBannerAction;
use App\Actions\Catalog\DeleteBannerAction;
use App\Actions\Catalog\UpdateBannerAction;
use App\Domain\Catalog\Models\Banner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class BannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_update_and_delete_banner(): void
    {
        $banner = app(CreateBannerAction::class)->execute(['title' => 'SABA', 'desktop_image' => 'banners/a.jpg']);
        $this->assertTrue($banner->is_active);
        $this->assertSame(0, $banner->sort_order);
        $banner = app(UpdateBannerAction::class)->execute($banner, ['title' => 'Nova campanha', 'is_active' => false, 'mobile_image' => 'banners/mobile.jpg']);
        $this->assertSame('Nova campanha', $banner->title);
        $this->assertFalse($banner->is_active);
        $this->assertSame('banners/mobile.jpg', $banner->mobile_image);
        app(DeleteBannerAction::class)->execute($banner);
        $this->assertModelMissing($banner);
    }

    public function test_desktop_image_is_required(): void
    {
        $this->expectException(ValidationException::class);
        app(CreateBannerAction::class)->execute(['title' => 'Sem imagem']);
    }

    public function test_order_and_active_scope(): void
    {
        $last = Banner::factory()->create(['sort_order' => 5]);
        $first = Banner::factory()->create(['sort_order' => 1]);
        $tie = Banner::factory()->create(['sort_order' => 1, 'is_active' => false]);
        $this->assertSame([$first->id, $tie->id, $last->id], Banner::ordered()->pluck('id')->all());
        $this->assertSame([$first->id, $last->id], Banner::active()->ordered()->pluck('id')->all());
    }

    public function test_visibility_handles_open_intervals_boundaries_and_inactive_records(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 6)->setTime(12, 0));
        $always = Banner::factory()->create();
        $running = Banner::factory()->create(['starts_at' => now()->subHour(), 'ends_at' => now()->addHour()]);
        $startsNow = Banner::factory()->create(['starts_at' => now()]);
        $endsNow = Banner::factory()->create(['ends_at' => now()]);
        $instant = Banner::factory()->create(['starts_at' => now(), 'ends_at' => now()]);
        Banner::factory()->create(['starts_at' => now()->addSecond()]);
        Banner::factory()->create(['ends_at' => now()->subSecond()]);
        Banner::factory()->create(['is_active' => false]);
        Banner::factory()->create(['is_active' => false, 'starts_at' => now()->subHour()]);
        $this->assertEqualsCanonicalizing([$always->id, $running->id, $startsNow->id, $endsNow->id, $instant->id], Banner::currentlyVisible()->pluck('id')->all());
    }

    public function test_invalid_period_is_rejected_on_creation(): void
    {
        $this->expectException(ValidationException::class);
        app(CreateBannerAction::class)->execute(['title' => 'SABA', 'desktop_image' => 'banners/a.jpg', 'starts_at' => '2026-10-10 12:00:00', 'ends_at' => '2026-10-09 12:00:00']);
    }

    public function test_partial_updates_validate_existing_period_and_leave_record_unchanged(): void
    {
        $banner = Banner::factory()->create(['starts_at' => '2026-10-10 12:00:00', 'ends_at' => '2026-10-12 12:00:00']);
        foreach ([['ends_at' => '2026-10-09 12:00:00'], ['starts_at' => '2026-10-13 12:00:00']] as $change) {
            try {
                app(UpdateBannerAction::class)->execute($banner, $change);
                $this->fail('Expected invalid interval to be rejected.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('ends_at', $exception->errors());
                $this->assertSame('2026-10-12 12:00:00', $banner->fresh()->ends_at->format('Y-m-d H:i:s'));
            }
        }
        $updated = app(UpdateBannerAction::class)->execute($banner, ['ends_at' => null]);
        $this->assertNull($updated->ends_at);
        $equal = app(UpdateBannerAction::class)->execute($updated, ['ends_at' => $updated->starts_at]);
        $this->assertTrue($equal->starts_at->equalTo($equal->ends_at));
    }
}

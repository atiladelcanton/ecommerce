<?php

declare(strict_types=1);

namespace Tests\Feature\Influencer;

use App\Actions\Coupon\SaveCouponAction;
use App\Domain\Coupon\Models\Coupon;
use App\Domain\Coupon\Models\CouponUsage;
use App\Domain\Influencer\Models\Influencer;
use App\Domain\Influencer\Models\InfluencerCommission;
use App\Domain\Influencer\Repositories\InfluencerReportRepository;
use App\Domain\Influencer\Services\SyncInfluencerCommission;
use App\Domain\Order\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class InfluencerProgramTest extends TestCase
{
    use RefreshDatabase;

    private function influencer(string $email, int $bps = 750): Influencer
    {
        return Influencer::query()->create([
            'name' => 'Pessoa Parceira', 'email' => $email, 'password' => 'senha-segura-123456',
            'commission_bps' => $bps, 'is_active' => true,
        ]);
    }

    private function coupon(Influencer $influencer, string $code): Coupon
    {
        return Coupon::query()->create([
            'code' => $code, 'description' => 'Campanha de teste', 'discount_type' => 'percentage',
            'value' => 10, 'scope' => 'all', 'is_active' => true, 'influencer_id' => $influencer->id,
        ]);
    }

    private function order(Coupon $coupon, string $number, int $subtotal = 30000, int $discount = 3000): Order
    {
        return Order::query()->create([
            'number' => $number, 'checkout_token' => (string) Str::uuid(),
            'checkout_session_hash' => str_repeat('a', 64), 'customer_name' => 'Comprador',
            'customer_email' => 'comprador@example.test', 'customer_phone' => '11999999999',
            'customer_document' => '52998224725', 'status' => 'pending', 'payment_status' => 'pending',
            'subtotal_cents' => $subtotal, 'discount_cents' => $discount,
            'shipping_cents' => 1000, 'total_cents' => $subtotal - $discount + 1000,
            'shipping_option_id' => 'local_pickup:pickup', 'shipping_name' => 'Retirada',
            'shipping_description' => 'Retirada local', 'coupon_id' => $coupon->id, 'coupon_code' => $coupon->code,
        ]);
    }

    public function test_commission_uses_net_product_value_and_freezes_rate(): void
    {
        $person = $this->influencer('pessoa@example.test');
        $order = $this->order($this->coupon($person, 'PESSOA10'), 'TEST-1');

        app(SyncInfluencerCommission::class)->execute($order, 'reserved');
        $commission = InfluencerCommission::query()->sole();
        $this->assertSame('pending', $commission->status);
        $this->assertSame(27000, $commission->base_cents);
        $this->assertSame(2025, $commission->amount_cents);
        $person->update(['commission_bps' => 1000]);

        app(SyncInfluencerCommission::class)->execute($order, 'consumed');
        app(SyncInfluencerCommission::class)->execute($order, 'consumed');
        $commission->refresh();
        $this->assertSame('forecast', $commission->status);
        $this->assertSame(750, $commission->commission_bps);
        $this->assertSame(2025, $commission->amount_cents);
        $this->assertSame(1, InfluencerCommission::query()->count());
    }

    public function test_cannot_change_influencer_after_coupon_has_been_reserved(): void
    {
        $one = $this->influencer('um@example.test');
        $two = $this->influencer('dois@example.test');
        $coupon = $this->coupon($one, 'PERSONAL10');
        $order = $this->order($coupon, 'TEST-2');
        CouponUsage::query()->create([
            'coupon_id' => $coupon->id, 'order_id' => $order->id,
            'document_hash' => str_repeat('b', 64), 'status' => 'reserved', 'discount_cents' => 3000,
        ]);

        $this->expectException(ValidationException::class);
        app(SaveCouponAction::class)->execute([
            'code' => 'PERSONAL10', 'description' => 'Campanha de teste', 'discount_type' => 'percentage',
            'value' => 10, 'scope' => 'all', 'influencer_id' => $two->id, 'is_active' => true,
        ], $coupon);
    }

    public function test_an_influencer_can_only_see_their_attributed_orders(): void
    {
        $one = $this->influencer('one@example.test');
        $two = $this->influencer('two@example.test');
        $this->order($this->coupon($one, 'ONE10'), 'ORDER-ONE');
        $this->order($this->coupon($two, 'TWO10'), 'ORDER-TWO');

        $this->actingAs($one, 'influencer')->get('/influencers/orders')
            ->assertOk()->assertSee('ORDER-ONE')->assertDontSee('ORDER-TWO')
            ->assertDontSee('comprador@example.test')->assertDontSee('52998224725');
        $this->assertSame(1, app(InfluencerReportRepository::class)->orders($one)->total());
    }

    public function test_inactive_influencer_cannot_sign_in(): void
    {
        $person = $this->influencer('inativo@example.test');
        $person->update(['is_active' => false]);
        $this->post('/influencers/login', ['email' => 'inativo@example.test', 'password' => 'senha-segura-123456'])
            ->assertSessionHasErrors('email');
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Filament\Widgets\DeliveredVsCancelledChart;
use App\Filament\Widgets\OrderOutcomesSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use ReflectionMethod;
use Tests\TestCase;

final class DashboardOrderOutcomesTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_dashboard_compares_delivered_and_cancelled_by_outcome_date(): void
    {
        Carbon::setTestNow('2026-10-19 12:00:00');

        $this->order(OrderStatus::DELIVERED, deliveredAt: '2026-10-05 10:00:00', totalCents: 12000);
        $this->order(OrderStatus::CANCELLED, cancelledAt: '2026-10-08 10:00:00', totalCents: 18000);
        $this->order(OrderStatus::DELIVERED, deliveredAt: '2026-09-12 10:00:00', totalCents: 15000);
        $this->order(OrderStatus::DELIVERED, deliveredAt: '2026-08-12 10:00:00', totalCents: 15000);

        $chart = $this->protectedResult(new DeliveredVsCancelledChart(), 'getData');

        self::assertCount(12, $chart['labels']);
        self::assertSame(1, $chart['datasets'][0]['data'][11]);
        self::assertSame(1, $chart['datasets'][1]['data'][11]);
        self::assertSame(1, $chart['datasets'][0]['data'][10]);
        self::assertSame(0, $chart['datasets'][1]['data'][10]);

        $summary = $this->protectedResult(new OrderOutcomesSummary(), 'getViewData');

        self::assertSame(1, $summary['delivered']);
        self::assertSame(1, $summary['cancelled']);
        self::assertSame(50.0, $summary['cancellationRate']);
        self::assertSame(50.0, $summary['cancellationRateDelta']);
        self::assertSame('R$ 180,00', $summary['cancelledValue']);
    }

    /** @return array<string, mixed> */
    private function protectedResult(object $object, string $method): array
    {
        $reflection = new ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        /** @var array<string, mixed> $result */
        $result = $reflection->invoke($object);

        return $result;
    }

    private function order(
        OrderStatus $status,
        ?string $deliveredAt = null,
        ?string $cancelledAt = null,
        int $totalCents = 10000,
    ): Order {
        return Order::query()->create([
            'number' => 'SABA-'.Str::upper(Str::random(8)),
            'checkout_token' => (string) Str::uuid(),
            'checkout_session_hash' => hash('sha256', Str::random(40)),
            'customer_name' => 'Cliente Teste',
            'customer_email' => Str::lower(Str::random(8)).'@saba.test',
            'customer_phone' => '11999999999',
            'customer_document' => '52998224725',
            'status' => $status,
            'payment_status' => PaymentStatus::APPROVED,
            'subtotal_cents' => $totalCents,
            'shipping_cents' => 0,
            'discount_cents' => 0,
            'total_cents' => $totalCents,
            'shipping_option_id' => 'pickup',
            'shipping_name' => 'Retirada',
            'shipping_description' => 'Teste',
            'delivered_at' => $deliveredAt,
            'cancelled_at' => $cancelledAt,
        ]);
    }
}

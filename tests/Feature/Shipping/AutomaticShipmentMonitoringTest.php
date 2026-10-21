<?php

declare(strict_types=1);

namespace Tests\Feature\Shipping;

use App\Actions\Notification\NotifyOrderMilestoneAction;
use App\Actions\Order\TransitionOrderStatusAction;
use App\Actions\Shipping\DispatchShipmentMonitoringAction;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Domain\Shipping\Models\Shipment;
use App\Jobs\Shipping\CheckShipmentStatusJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\Support\ShippingFixtures;
use Tests\TestCase;

final class AutomaticShipmentMonitoringTest extends TestCase
{
    use RefreshDatabase;
    use ShippingFixtures;

    public function test_payment_approval_creates_a_persistent_fiscal_alert_only_once_per_admin(): void
    {
        $admin = User::factory()->create();
        $order = $this->order();
        $order->update(['status' => OrderStatus::PENDING]);

        app(TransitionOrderStatusAction::class)->execute($order, OrderStatus::PAID);
        app(TransitionOrderStatusAction::class)->execute($order, OrderStatus::PAID);
        app(NotifyOrderMilestoneAction::class)->execute($order->refresh(), OrderStatus::PAID);

        $this->assertDatabaseCount('notifications', 1);
        $row = DB::table('notifications')->where('notifiable_id', $admin->id)->first();
        $this->assertNotNull($row);
        $data = json_decode($row->data, true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('filament', $data['format']);
        $this->assertStringContainsString('NF-e a emitir', $data['title']);
        $this->assertNull($row->read_at);
    }

    public function test_monitor_dispatches_only_eligible_shipments_without_http_calls(): void
    {
        $order = $this->order();
        $due = $this->shipment($order, ShipmentStatus::PURCHASED);
        $this->shipment($order, ShipmentStatus::DELIVERED);
        $this->shipment($order, ShipmentStatus::PURCHASED)->update(['monitor_checked_at' => now()]);

        Queue::fake();
        $this->assertSame(1, app(DispatchShipmentMonitoringAction::class)->execute());
        Queue::assertPushed(CheckShipmentStatusJob::class, fn (CheckShipmentStatusJob $job): bool => $job->shipmentId === $due->id);
        Http::assertNothingSent();
    }

    public function test_scheduler_reads_paid_label_without_buying_it_again_and_notifies_admin(): void
    {
        User::factory()->create();
        $order = $this->order();
        $shipment = $this->shipment($order, ShipmentStatus::PURCHASED);
        $when = now()->toIso8601String();
        Http::fake([
            '*/api/v2/me/orders/*' => Http::response([
                'id' => $shipment->external_id,
                'status' => 'released',
                'generated_at' => $when,
                'service_id' => $shipment->service_external_id,
                'tags' => ['saba-operation:'.$shipment->operation_key],
            ]),
        ]);

        app(CheckShipmentStatusJob::class, ['shipmentId' => $shipment->id])->handle(
            app(\App\Infrastructure\Shipping\ShippingProviderRegistry::class),
            app(\App\Actions\Shipping\ReconcileShipmentAction::class),
        );

        $this->assertSame(ShipmentStatus::LABEL_GENERATED, $shipment->refresh()->status);
        $this->assertSame(OrderStatus::READY_TO_SHIP, $order->refresh()->status);
        $this->assertDatabaseCount('notifications', 1);
        Http::assertSentCount(1); // GET /orders only. No new cart, purchase or label.

        app(CheckShipmentStatusJob::class, ['shipmentId' => $shipment->id])->handle(
            app(\App\Infrastructure\Shipping\ShippingProviderRegistry::class),
            app(\App\Actions\Shipping\ReconcileShipmentAction::class),
        );
        $this->assertDatabaseCount('notifications', 1);
        $this->assertSame(ShipmentStatus::LABEL_GENERATED, $shipment->refresh()->status);
    }

    public function test_a_foreign_shipment_tag_cannot_change_the_order(): void
    {
        $order = $this->order();
        $shipment = $this->shipment($order, ShipmentStatus::PURCHASED);
        Http::fake(['*/api/v2/me/orders/*' => Http::response([
            'status' => 'generated', 'service_id' => $shipment->service_external_id,
            'tags' => ['saba-operation:different-order'],
        ])]);
        app(CheckShipmentStatusJob::class, ['shipmentId' => $shipment->id])->handle(
            app(\App\Infrastructure\Shipping\ShippingProviderRegistry::class),
            app(\App\Actions\Shipping\ReconcileShipmentAction::class),
        );
        $this->assertSame(ShipmentStatus::PURCHASED, $shipment->refresh()->status);
        $this->assertSame(OrderStatus::PROCESSING, $order->refresh()->status);
    }

    private function shipment(\App\Domain\Order\Models\Order $order, ShipmentStatus $status): Shipment
    {
        return $order->shipments()->create([
            'provider' => 'melhor_envio',
            'shipping_integration_id' => $order->shipping_integration_id,
            'environment' => 'sandbox',
            'operation_key' => (string) Str::uuid(),
            'fulfillment_key' => (string) Str::uuid(),
            'external_id' => 'shipment-'.Str::lower(Str::random(10)),
            'service_external_id' => '902',
            'status' => $status,
            'customer_price_cents' => 4890,
        ]);
    }
}

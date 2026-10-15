<?php

declare(strict_types=1);

namespace Tests\Feature\Shipping;

use App\Domain\Shipping\Models\ShippingIntegration;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ShippingMigrationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_agency_membership_migration_backfills_shared_defaults_without_changing_ids(): void
    {
        $migration = require database_path('migrations/2026_10_15_000001_create_shipping_agency_carrier.php');
        $migration->down();
        $integration = ShippingIntegration::forProvider('melhor_envio');
        $first = DB::table('shipping_carriers')->insertGetId(['shipping_integration_id' => $integration->id, 'environment' => 'sandbox', 'external_id' => '901', 'name' => 'Primeira']);
        $second = DB::table('shipping_carriers')->insertGetId(['shipping_integration_id' => $integration->id, 'environment' => 'sandbox', 'external_id' => '902', 'name' => 'Segunda']);
        $otherEnvironment = DB::table('shipping_carriers')->insertGetId(['shipping_integration_id' => $integration->id, 'environment' => 'production', 'external_id' => '902', 'name' => 'Produção']);
        $agency = DB::table('shipping_agencies')->insertGetId(['shipping_integration_id' => $integration->id, 'environment' => 'sandbox', 'external_id' => 'shared', 'name' => 'Histórica', 'shipping_carrier_id' => $first, 'metadata' => json_encode(['companies' => [['id' => 902]]])]);
        DB::table('shipping_carriers')->where('id', $second)->update(['default_shipping_agency_id' => $agency]);
        $migration->up();
        $this->assertDatabaseHas('shipping_agency_carrier', ['shipping_agency_id' => $agency, 'shipping_carrier_id' => $first]);
        $this->assertDatabaseHas('shipping_agency_carrier', ['shipping_agency_id' => $agency, 'shipping_carrier_id' => $second]);
        $this->assertDatabaseMissing('shipping_agency_carrier', ['shipping_agency_id' => $agency, 'shipping_carrier_id' => $otherEnvironment]);
        $migration->down();
        $this->assertDatabaseHas('shipping_agencies', ['id' => $agency, 'shipping_carrier_id' => $first]);
        $this->assertDatabaseHas('shipping_carriers', ['id' => $second, 'default_shipping_agency_id' => $agency]);
        $migration->up();
        $this->assertDatabaseCount('shipping_agency_carrier', 2);
    }

    public function test_incremental_migrations_round_trip_preserves_historical_order_and_shipment(): void
    {
        ShippingIntegration::forProvider('melhor_envio')->update(['configuration' => ['state_register' => 'IE legada']]);
        $orderId = DB::table('orders')->insertGetId([
            'number' => 'LEGACY-1', 'checkout_token' => (string) Str::uuid(), 'checkout_session_hash' => hash('sha256', 'legacy'),
            'customer_name' => 'Cliente histórico', 'customer_email' => 'cliente@example.test', 'customer_phone' => '11999999999', 'customer_document' => '52998224725',
            'status' => 'ready_for_shipping', 'payment_status' => 'approved', 'subtotal_cents' => 10000, 'shipping_cents' => 4890, 'discount_cents' => 0, 'total_cents' => 14890,
            'shipping_option_id' => 'legacy-service', 'shipping_name' => 'Serviço histórico', 'shipping_description' => 'Prazo histórico',
            'shipping_quote_snapshot' => json_encode(['carrier_name' => 'Transportadora histórica', 'price' => 4890]),
        ]);
        DB::table('shipments')->insert(['order_id' => $orderId, 'provider' => 'melhor_envio', 'external_id' => 'historic-external', 'carrier_name' => 'Transportadora histórica', 'status' => 'label_generated', 'customer_price_cents' => 4890]);
        $migrations = array_map(fn ($file) => require $file, array_merge(glob(database_path('migrations/2026_10_14_*.php')), glob(database_path('migrations/2026_10_15_*.php'))));
        foreach (array_reverse($migrations) as $migration) {
            $migration->down();
        }
        $this->assertFalse(Schema::hasTable('shipping_carriers'));
        $this->assertFalse(Schema::hasColumn('shipments', 'operation_key'));
        $this->assertSame('ready_for_shipping', DB::table('orders')->where('id', $orderId)->value('status'));
        foreach ($migrations as $migration) {
            $migration->up();
        }
        $this->assertTrue(Schema::hasColumn('shipments', 'operation_key'));
        $this->assertDatabaseHas('shipments', ['external_id' => 'historic-external', 'carrier_name' => 'Transportadora histórica', 'customer_price_cents' => 4890]);
        $this->assertSame(4890, json_decode(DB::table('orders')->where('id', $orderId)->value('shipping_quote_snapshot'), true)['price']);
    }
}

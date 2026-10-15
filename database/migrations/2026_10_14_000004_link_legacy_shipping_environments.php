<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->string('shipping_environment', 16)->nullable());
        $integrations = DB::table('shipping_integrations')->get()->keyBy('provider');
        DB::table('orders')->whereNotNull('shipping_provider')->orderBy('id')->chunkById(100, function ($orders) use ($integrations): void {
            foreach ($orders as $order) {
                $integration = $integrations->get($order->shipping_provider);
                if (! $integration) {
                    continue;
                }
                $snapshot = json_decode($order->shipping_quote_snapshot ?? '{}', true) ?: [];
                DB::table('orders')->where('id', $order->id)->update(['shipping_integration_id' => $order->shipping_integration_id ?? $integration->id, 'shipping_environment' => $snapshot['environment'] ?? $integration->environment]);
            }
        });
        DB::table('shipments')->whereNull('environment')->orderBy('id')->chunkById(100, function ($shipments) use ($integrations): void {
            foreach ($shipments as $shipment) {
                $integration = $integrations->get($shipment->provider);
                if (! $integration) {
                    continue;
                }
                $snapshot = json_decode($shipment->quote_snapshot ?? '{}', true) ?: [];
                DB::table('shipments')->where('id', $shipment->id)->update(['shipping_integration_id' => $shipment->shipping_integration_id ?? $integration->id, 'environment' => $snapshot['environment'] ?? $integration->environment]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn('shipping_environment'));
    }
};

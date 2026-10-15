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
        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('shipping_integration_id')->nullable()->constrained()->restrictOnDelete();
        });
        Schema::table('shipments', function (Blueprint $table): void {
            $table->foreignId('shipping_integration_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('shipping_agency_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('environment', 16)->nullable();
            $table->uuid('operation_key')->nullable()->unique();
            $table->string('fulfillment_key', 100)->nullable();
            $table->unsignedInteger('attempt')->default(1);
            $table->json('packages')->nullable();
            $table->string('operation_phase', 32)->nullable();
            $table->timestamp('operation_started_at')->nullable();
            $table->unique(['order_id', 'fulfillment_key', 'attempt'], 'shipment_fulfillment_attempt_unique');
        });
        DB::table('shipments')->orderBy('id')->chunkById(100, function ($rows): void {
            foreach ($rows as $row) {
                DB::table('shipments')->where('id', $row->id)->update(['fulfillment_key' => 'legacy-'.$row->id]);
            }
        });
        Schema::table('shipment_events', function (Blueprint $table): void {
            $table->timestamp('processed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('shipment_events', fn (Blueprint $table) => $table->dropColumn('processed_at'));
        Schema::table('shipments', function (Blueprint $table): void {
            $table->dropUnique('shipment_fulfillment_attempt_unique');
            $table->dropConstrainedForeignId('shipping_integration_id');
            $table->dropConstrainedForeignId('shipping_agency_id');
            $table->dropUnique(['operation_key']);
            $table->dropColumn(['environment', 'operation_key', 'fulfillment_key', 'attempt', 'packages', 'operation_phase', 'operation_started_at']);
        });
        Schema::table('orders', fn (Blueprint $table) => $table->dropConstrainedForeignId('shipping_integration_id'));
    }
};

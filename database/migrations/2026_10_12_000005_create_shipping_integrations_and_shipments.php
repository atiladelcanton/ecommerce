<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_integrations', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 64)->unique();
            $table->boolean('is_enabled')->default(false)->index();
            $table->string('environment', 16)->default('sandbox');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->longText('configuration')->nullable();
            $table->longText('credentials')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->dateTime('token_expires_at')->nullable();
            $table->dateTime('connected_at')->nullable();
            $table->dateTime('last_tested_at')->nullable();
            $table->timestamps();
        });

        Schema::create('shipments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('provider', 64)->index();
            $table->string('external_id')->nullable();
            $table->string('carrier_external_id')->nullable();
            $table->string('carrier_name')->nullable();
            $table->string('service_external_id')->nullable();
            $table->string('service_name')->nullable();
            $table->string('status', 40)->index();
            $table->unsignedBigInteger('customer_price_cents');
            $table->unsignedBigInteger('provider_price_cents')->nullable();
            $table->string('tracking_code')->nullable();
            $table->string('tracking_url', 2048)->nullable();
            $table->string('label_url', 2048)->nullable();
            $table->string('label_format', 12)->nullable();
            $table->json('quote_snapshot')->nullable();
            $table->json('metadata')->nullable();
            $table->dateTime('generated_at')->nullable();
            $table->dateTime('posted_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'external_id']);
            $table->index(['order_id', 'status']);
        });

        Schema::create('shipment_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->string('provider_event_id')->nullable()->unique();
            $table->string('event_type', 80)->index();
            $table->string('status', 40)->nullable();
            $table->json('payload')->nullable();
            $table->dateTime('occurred_at')->nullable();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->string('shipping_provider', 64)->nullable()->after('shipping_option_id')->index();
            $table->string('shipping_service_id')->nullable()->after('shipping_provider');
            $table->json('shipping_quote_snapshot')->nullable()->after('shipping_description');
            $table->string('invoice_key', 80)->nullable()->after('tracking_url');
            $table->string('dce_key', 80)->nullable()->after('invoice_key');
            $table->dateTime('ready_for_shipping_at')->nullable()->after('dce_key');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['shipping_provider']);
            $table->dropColumn([
                'shipping_provider',
                'shipping_service_id',
                'shipping_quote_snapshot',
                'invoice_key',
                'dce_key',
                'ready_for_shipping_at',
            ]);
        });

        Schema::dropIfExists('shipment_events');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('shipping_integrations');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_carriers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shipping_integration_id')->constrained()->restrictOnDelete();
            $table->string('environment', 16);
            $table->string('external_id', 100);
            $table->string('name');
            $table->string('logo_url', 2048)->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_available')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['shipping_integration_id', 'environment', 'external_id'], 'carriers_external_unique');
        });
        Schema::create('shipping_services', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shipping_carrier_id')->constrained()->restrictOnDelete();
            $table->string('external_id', 100);
            $table->string('name');
            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_available')->default(true);
            $table->string('fulfillment_mode', 16)->default('manual');
            $table->json('metadata')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['shipping_carrier_id', 'external_id']);
        });
        Schema::create('shipping_agencies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shipping_integration_id')->constrained()->restrictOnDelete();
            $table->foreignId('shipping_carrier_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('environment', 16);
            $table->string('external_id', 100);
            $table->string('name');
            $table->string('company_external_id', 100)->nullable();
            foreach (['postal_code', 'street', 'number', 'district', 'city', 'state'] as $field) {
                $table->string($field)->nullable();
            }
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['shipping_integration_id', 'environment', 'external_id'], 'agencies_external_unique');
            $table->index(['shipping_carrier_id', 'is_active']);
        });
        Schema::table('shipping_carriers', function (Blueprint $table): void {
            $table->foreignId('default_shipping_agency_id')->nullable()->constrained('shipping_agencies')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shipping_carriers', fn (Blueprint $table) => $table->dropConstrainedForeignId('default_shipping_agency_id'));
        Schema::dropIfExists('shipping_agencies');
        Schema::dropIfExists('shipping_services');
        Schema::dropIfExists('shipping_carriers');
    }
};

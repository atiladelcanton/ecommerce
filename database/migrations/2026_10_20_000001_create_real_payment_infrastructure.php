<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_integrations', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 64);
            $table->string('environment', 16);
            $table->boolean('is_enabled')->default(false);
            $table->text('credentials')->nullable();
            $table->text('configuration')->nullable();
            $table->timestamp('last_tested_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'environment']);
        });
        Schema::table('payments', function (Blueprint $table): void {
            $table->foreignId('payment_integration_id')->nullable()->constrained('payment_integrations');
            $table->string('environment', 16)->nullable();
            $table->uuid('idempotency_key')->nullable()->unique();
            $table->text('checkout_url')->nullable();
            $table->string('provider_status', 64)->nullable();
            $table->string('status_detail', 128)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->boolean('needs_review')->default(false);
            $table->string('review_reason', 255)->nullable();
            $table->index(['gateway', 'environment', 'status']);
        });
        Schema::create('payment_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('gateway', 64);
            $table->string('environment', 16);
            $table->string('event_key', 191);
            $table->string('external_id', 255);
            $table->string('action', 100)->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['gateway', 'environment', 'event_key'], 'payment_events_identity_unique');
        });
        Schema::table('inventory_reservations', function (Blueprint $table): void {
            $table->boolean('payment_hold')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('inventory_reservations', fn (Blueprint $table) => $table->dropColumn('payment_hold'));
        Schema::dropIfExists('payment_events');
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropForeign(['payment_integration_id']);
            $table->dropIndex(['gateway', 'environment', 'status']);
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn(['payment_integration_id', 'environment', 'idempotency_key', 'checkout_url', 'provider_status', 'status_detail', 'expires_at', 'last_checked_at', 'needs_review', 'review_reason']);
        });
        Schema::dropIfExists('payment_integrations');
    }
};

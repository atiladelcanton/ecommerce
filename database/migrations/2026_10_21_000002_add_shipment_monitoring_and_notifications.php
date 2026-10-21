<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table): void {
            $table->timestamp('monitor_checked_at')->nullable();
            $table->index(['provider', 'status', 'monitor_checked_at'], 'shipments_monitor_due_index');
        });

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->json('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                $table->index('read_at');
            });
        }
    }

    public function down(): void
    {
        // Existing shared notifications are never deleted by this feature's rollback.
        Schema::table('shipments', function (Blueprint $table): void {
            $table->dropIndex('shipments_monitor_due_index');
            $table->dropColumn('monitor_checked_at');
        });
    }
};

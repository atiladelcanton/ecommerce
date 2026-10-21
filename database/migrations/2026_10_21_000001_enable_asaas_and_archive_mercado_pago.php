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
        // Historical Mercado Pago charges/webhooks remain in the database, but no new
        // checkout may select the adapter. Never delete historical financial records.
        DB::table('payment_integrations')->where('provider', 'mercado_pago')->update(['is_enabled' => false]);
        Schema::table('payment_events', function (Blueprint $table): void {
            $table->json('payload')->nullable(); // Minimised, authenticated Asaas event data.
        });
    }

    public function down(): void
    {
        Schema::table('payment_events', fn (Blueprint $table) => $table->dropColumn('payload'));
        // Intentionally do not reactivate a previous production payment provider.
    }
};

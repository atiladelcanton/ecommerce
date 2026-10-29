<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('store_settings', 'template_id')) {
            Schema::table('store_settings', function (Blueprint $table): void {
                // Null means: keep previous .env fallback until a theme is explicitly chosen.
                $table->unsignedTinyInteger('template_id')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('store_settings', 'template_id')) {
            Schema::table('store_settings', fn (Blueprint $table) => $table->dropColumn('template_id'));
        }
    }
};

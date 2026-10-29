<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table): void {
            $table->string('logo_path')->nullable();
            $table->char('color_primary', 7)->nullable();
            $table->char('color_secondary', 7)->nullable();
            $table->char('color_button', 7)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table): void {
            $table->dropColumn(['logo_path', 'color_primary', 'color_secondary', 'color_button']);
        });
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table): void {
            $table->string('state_register', 40)->nullable();
            $table->string('economic_activity_code', 20)->nullable();
        });
        $encrypted = DB::table('shipping_integrations')->where('provider', 'melhor_envio')->value('configuration');
        if (is_string($encrypted) && $encrypted !== '') {
            $config = json_decode(Crypt::decryptString($encrypted), true, flags: JSON_THROW_ON_ERROR);
            foreach (['state_register', 'economic_activity_code'] as $field) {
                if (filled($config[$field] ?? null)) {
                    DB::table('store_settings')->whereNull($field)->update([$field => $config[$field]]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('store_settings', fn (Blueprint $table) => $table->dropColumn(['state_register', 'economic_activity_code']));
    }
};

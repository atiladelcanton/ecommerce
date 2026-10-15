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
        Schema::create('shipping_agency_carrier', function (Blueprint $table): void {
            $table->foreignId('shipping_agency_id')->constrained('shipping_agencies')->cascadeOnDelete();
            $table->foreignId('shipping_carrier_id')->constrained('shipping_carriers')->cascadeOnDelete();
            $table->primary(['shipping_agency_id', 'shipping_carrier_id'], 'shipping_agency_carrier_primary');
        });

        DB::table('shipping_agencies')->orderBy('id')->chunkById(200, function ($agencies): void {
            foreach ($agencies as $agency) {
                $metadata = json_decode($agency->metadata ?? '{}', true) ?? [];
                $externalIds = collect($metadata['companies'] ?? [])->pluck('id')->map(fn ($id) => (string) $id)->all();
                $carriers = DB::table('shipping_carriers')
                    ->where('shipping_integration_id', $agency->shipping_integration_id)
                    ->where('environment', $agency->environment)
                    ->where(function ($query) use ($agency, $externalIds): void {
                        $query->where('id', $agency->shipping_carrier_id)
                            ->orWhere('default_shipping_agency_id', $agency->id)
                            ->orWhereIn('external_id', $externalIds);
                    })->pluck('id');
                foreach ($carriers as $carrierId) {
                    DB::table('shipping_agency_carrier')->insertOrIgnore(['shipping_agency_id' => $agency->id, 'shipping_carrier_id' => $carrierId]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_agency_carrier');
    }
};

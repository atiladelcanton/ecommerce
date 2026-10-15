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
        Schema::table('product_variants', function (Blueprint $table): void {
            $table->json('attributes')->nullable();
            $table->char('combination_key', 64)->nullable();
            $table->unsignedBigInteger('price')->nullable();
            $table->unsignedBigInteger('promotional_price')->nullable();
            $table->decimal('weight', 12, 3)->nullable();
            foreach (['height', 'width', 'length'] as $field) {
                $table->decimal($field, 12, 2)->nullable();
            }
            $table->unique(['product_id', 'combination_key'], 'product_variant_combination_unique');
        });
        DB::table('product_variants')->orderBy('id')->chunkById(200, function ($rows): void {
            foreach ($rows as $row) {
                $attributes = [$row->name => $row->value];
                $canonical = [mb_strtolower(trim($row->name)) => mb_strtolower(trim($row->value))];
                DB::table('product_variants')->where('id', $row->id)->update([
                    'attributes' => json_encode($attributes, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    'combination_key' => hash('sha256', json_encode($canonical, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)),
                ]);
            }
        });
        Schema::table('order_items', function (Blueprint $table): void {
            $table->json('variant_attributes_snapshot')->nullable();
            $table->json('shipping_measurements_snapshot')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', fn (Blueprint $table) => $table->dropColumn(['variant_attributes_snapshot', 'shipping_measurements_snapshot']));
        Schema::table('product_variants', function (Blueprint $table): void {
            $table->dropUnique('product_variant_combination_unique');
            $table->dropColumn(['attributes', 'combination_key', 'price', 'promotional_price', 'weight', 'height', 'width', 'length']);
        });
    }
};

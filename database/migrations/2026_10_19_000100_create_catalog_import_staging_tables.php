<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_import_sources', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->char('source_key', 64)->unique();
            $table->string('source_url', 2048);
            $table->string('source_domain', 255);
            $table->string('price_status', 80)->nullable();
            $table->string('variant_details_status', 100)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['product_id', 'source_domain']);
        });

        Schema::create('catalog_import_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('catalog_import_source_id')
                ->constrained('catalog_import_sources')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('value', 255);
            $table->string('status', 100)->default('unverified');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['catalog_import_source_id', 'name', 'value'], 'catalog_import_option_identity_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_import_options');
        Schema::dropIfExists('catalog_import_sources');
    }
};

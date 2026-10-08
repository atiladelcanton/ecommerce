<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->nullable()->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('youtube_url', 2048)->nullable();
            $table->string('availability', 20)->index();
            $table->unsignedBigInteger('cost_price');
            $table->unsignedBigInteger('price')->index();
            $table->unsignedBigInteger('promotional_price')->nullable()->index();
            $table->unsignedInteger('stock')->nullable();
            $table->decimal('weight', 12, 3)->nullable();
            foreach (['height', 'width', 'length'] as $dimension) {
                $table->decimal($dimension, 12, 2)->nullable();
            }
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('whatsapp_quote_enabled')->default(true);
            $table->index(['is_active', 'availability']);
            $table->index(['is_featured', 'is_active']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

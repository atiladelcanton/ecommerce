<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 50)->default('groq');
            $table->boolean('is_enabled')->default(false);
            $table->string('model')->default('openai/gpt-oss-120b');
            $table->text('api_key')->nullable();
            $table->text('store_instructions')->nullable();
            $table->unsignedInteger('monthly_request_limit')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_usage_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 50)->index();
            $table->string('model')->index();
            $table->string('action', 80)->index();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->unsignedInteger('total_tokens')->default(0);
            $table->unsignedInteger('duration_ms')->nullable();
            $table->boolean('successful')->default(true)->index();
            $table->string('error_code', 100)->nullable();
            $table->timestamps();
            $table->index(['created_at', 'provider']);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->string('seo_title')->nullable()->after('description');
            $table->text('seo_description')->nullable()->after('seo_title');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['seo_title', 'seo_description']);
        });

        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('ai_settings');
    }
};

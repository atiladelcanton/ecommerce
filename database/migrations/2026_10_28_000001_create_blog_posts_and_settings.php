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
            $table->boolean('blog_enabled')->default(false);
            $table->boolean('blog_show_on_home')->default(false);
        });

        Schema::create('blog_posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('cover_alt')->nullable();
            $table->string('category', 100)->nullable();
            $table->json('tags')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->string('status', 16)->default('draft');
            $table->dateTime('published_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'published_at', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
        Schema::table('store_settings', function (Blueprint $table): void {
            $table->dropColumn(['blog_enabled', 'blog_show_on_home']);
        });
    }
};

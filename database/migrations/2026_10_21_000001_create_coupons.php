<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('description')->nullable();
            $table->string('discount_type', 16); // percentage | fixed
            $table->unsignedBigInteger('value'); // percent 1..100 or amount in integer cents
            $table->string('scope', 16)->default('all'); // all | categories | products
            $table->unsignedBigInteger('minimum_subtotal_cents')->nullable();
            $table->unsignedBigInteger('maximum_discount_cents')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->boolean('allow_sale_items')->default(false);
            $table->boolean('is_active')->default(false);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->timestamps();
            $table->index(['is_active', 'starts_at', 'ends_at']);
        });
        Schema::create('coupon_products', function (Blueprint $table): void {
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->primary(['coupon_id', 'product_id']);
        });
        Schema::create('coupon_categories', function (Blueprint $table): void {
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->primary(['coupon_id', 'category_id']);
        });
        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->string('coupon_code', 64)->nullable();
        });
        Schema::create('coupon_usages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->restrictOnDelete();
            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->char('document_hash', 64);
            $table->string('status', 16); // reserved | consumed | released
            $table->unsignedBigInteger('discount_cents');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            $table->index(['coupon_id', 'status']);
        });
        // Separate live claims allow retention of released historical usages while
        // enforcing a single active/purchased use per CPF/CNPJ at database level.
        Schema::create('coupon_claims', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->restrictOnDelete();
            $table->foreignId('coupon_usage_id')->unique()->constrained('coupon_usages')->cascadeOnDelete();
            $table->char('document_hash', 64);
            $table->timestamps();
            $table->unique(['coupon_id', 'document_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_claims');
        Schema::dropIfExists('coupon_usages');
        Schema::table('orders', fn (Blueprint $table) => $table->dropConstrainedForeignId('coupon_id'));
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn('coupon_code'));
        Schema::dropIfExists('coupon_categories');
        Schema::dropIfExists('coupon_products');
        Schema::dropIfExists('coupons');
    }
};

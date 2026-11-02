<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('influencers', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 160);
            $table->string('email', 255)->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->string('instagram')->nullable();
            $table->string('tiktok')->nullable();
            $table->text('pix_key')->nullable();
            $table->unsignedInteger('commission_bps')->default(500); // 500 = 5.00%.
            $table->text('notes')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::table('coupons', function (Blueprint $table): void {
            $table->foreignId('influencer_id')->nullable()->after('id')->constrained('influencers')->nullOnDelete();
            $table->index('influencer_id');
        });

        Schema::create('influencer_commissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('influencer_id')->constrained('influencers')->restrictOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->unsignedBigInteger('base_cents');
            $table->unsignedInteger('commission_bps');
            $table->unsignedBigInteger('amount_cents');
            $table->string('status', 20)->default('pending'); // pending, forecast, approved, paid, cancelled, review, reversed
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['influencer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('influencer_commissions');
        Schema::table('coupons', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('influencer_id');
        });
        Schema::dropIfExists('influencers');
    }
};

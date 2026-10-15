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
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('shipping_pricing_status', 32)->nullable()->index();
            $table->unsignedBigInteger('shipping_cents')->nullable()->change();
            $table->unsignedBigInteger('total_cents')->nullable()->change();
        });
        Schema::create('order_shipping_quotes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('revision');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('carrier_name');
            $table->string('service_name');
            $table->unsignedBigInteger('price_cents');
            $table->unsignedInteger('estimated_days')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('superseded_at')->nullable();
            $table->timestamps();
            $table->unique(['order_id', 'revision']);
        });
    }

    public function down(): void
    {
        if (DB::table('orders')->whereNull('shipping_cents')->orWhereNull('total_cents')->exists()) {
            throw new RuntimeException('Conclua ou cancele e defina os totais dos pedidos com frete pendente antes de reverter esta migration.');
        }
        Schema::dropIfExists('order_shipping_quotes');
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['shipping_pricing_status']);
            $table->dropColumn('shipping_pricing_status');
            $table->unsignedBigInteger('shipping_cents')->nullable(false)->change();
            $table->unsignedBigInteger('total_cents')->nullable(false)->change();
        });
    }
};

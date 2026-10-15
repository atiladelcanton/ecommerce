<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cash flow was intentionally removed from the e-commerce MVP. Sales and
        // payment reporting remain available through Orders and Payments.
        Schema::dropIfExists('cash_flow_entries');
    }

    public function down(): void
    {
        Schema::create('cash_flow_entries', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 16)->index();
            $table->string('category', 80)->index();
            $table->string('description');
            $table->unsignedBigInteger('amount_cents');
            $table->dateTime('occurred_at')->index();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->unique()->constrained('payments')->nullOnDelete();
            $table->string('source', 32)->default('manual')->index();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
};

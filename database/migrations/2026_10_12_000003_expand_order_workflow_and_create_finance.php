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
            $table->string('carrier')->nullable()->after('shipping_description');
        });

        Schema::table('order_status_histories', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('order_id')->constrained('users')->nullOnDelete();
        });

        // The old "confirmed" state meant payment approval. Keep historical data
        // semantically aligned with the explicit paid state introduced here.
        DB::table('orders')->where('status', 'confirmed')->update(['status' => 'paid']);
        DB::table('order_status_histories')->where('from_status', 'confirmed')->update(['from_status' => 'paid']);
        DB::table('order_status_histories')->where('to_status', 'confirmed')->update(['to_status' => 'paid']);

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

        // Existing approved payments should immediately appear in the finance view.
        $payments = DB::table('payments')
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->where('payments.status', 'approved')
            ->select([
                'payments.id as payment_id',
                'payments.order_id',
                'payments.amount_cents',
                'payments.updated_at',
                'orders.number',
            ])
            ->get();

        foreach ($payments as $payment) {
            DB::table('cash_flow_entries')->insertOrIgnore([
                'type' => 'income',
                'category' => 'Venda',
                'description' => 'Pedido '.$payment->number,
                'amount_cents' => $payment->amount_cents,
                'occurred_at' => $payment->updated_at ?? now(),
                'order_id' => $payment->order_id,
                'payment_id' => $payment->payment_id,
                'source' => 'payment',
                'notes' => null,
                'user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_flow_entries');

        DB::table('orders')->where('status', 'paid')->update(['status' => 'confirmed']);
        DB::table('order_status_histories')->where('from_status', 'paid')->update(['from_status' => 'confirmed']);
        DB::table('order_status_histories')->where('to_status', 'paid')->update(['to_status' => 'confirmed']);

        Schema::table('order_status_histories', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('carrier');
        });
    }
};

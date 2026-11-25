<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('invoice_code', 50)->unique();
            $table->json('service_detail');
            $table->decimal('subtotal', 12, 2);
            $table->integer('points_used')->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('total_paid', 12, 2);
            $table->integer('points_earned')->default(0);
            $table->timestamp('receipt_sent_at')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'created_at']);
            $table->index(['store_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('name');
            $table->string('whatsapp_number', 20);
            $table->string('email')->nullable();
            $table->date('birth_date')->nullable();
            $table->integer('total_points')->default(0);
            $table->timestamp('last_visit_at')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'name']);
            $table->index(['store_id', 'whatsapp_number']);
            $table->index(['store_id', 'last_visit_at']);
            $table->index(['store_id', 'total_points']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

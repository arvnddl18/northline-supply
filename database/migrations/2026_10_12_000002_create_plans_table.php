<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->string('name');
            $table->string('type', 30)->default('deposit'); // 'deposit', 'installment', 'subscription'
            $table->string('deposit_type', 20)->nullable(); // 'percentage', 'fixed'
            $table->integer('deposit_value')->nullable(); // Percentage (e.g. 30) or fixed cents (e.g. 5000 = $50.00)
            $table->integer('installments_count')->default(1);
            $table->string('interval', 20)->nullable(); // 'day', 'week', 'month', 'year'
            $table->integer('interval_count')->default(1);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['shop_id', 'active']);
            $table->index(['shop_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};

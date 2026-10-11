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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->string('shopify_order_id')->nullable();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->string('currency', 3)->default('AUD');
            $table->unsignedBigInteger('total_amount'); // Integer cents
            $table->unsignedBigInteger('paid_amount')->default(0); // Integer cents
            $table->string('payment_status', 30)->default('pending_deposit');
            $table->timestamps();

            $table->index(['shop_id', 'payment_status']);
            $table->index(['shop_id', 'shopify_order_id']);
            $table->index('shopify_order_id');
            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

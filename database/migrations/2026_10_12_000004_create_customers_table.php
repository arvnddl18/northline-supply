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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->string('shopify_customer_id')->nullable();
            $table->string('email');
            $table->string('stripe_customer_id');
            $table->string('default_payment_method')->nullable();
            $table->timestamps();

            $table->index(['shop_id', 'email']);
            $table->index(['shop_id', 'stripe_customer_id']);
            $table->index('shopify_customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

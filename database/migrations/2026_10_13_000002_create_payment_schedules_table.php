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
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->unsignedSmallInteger('sequence')->default(1);
            $table->timestamp('due_at');
            $table->unsignedBigInteger('amount'); // Integer cents
            $table->string('status', 30)->default('pending'); // pending, processing, succeeded, failed, cancelled
            $table->unsignedSmallInteger('attempt_count')->default(0);
            $table->timestamp('next_retry_at')->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'sequence']);
            $table->index(['due_at', 'status']);
            $table->index(['next_retry_at', 'status']);
            $table->index('stripe_payment_intent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_schedules');
    }
};

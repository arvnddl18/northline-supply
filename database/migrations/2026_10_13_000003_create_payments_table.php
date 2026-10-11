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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained('payment_schedules')->nullOnDelete();
            $table->string('stripe_payment_intent_id');
            $table->unsignedBigInteger('amount'); // Integer cents
            $table->string('status', 30); // succeeded, failed, requires_action
            $table->string('failure_code')->nullable();
            $table->timestamps();

            $table->index('stripe_payment_intent_id');
            $table->index('schedule_id');
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

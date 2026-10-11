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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('template_key', 100);
            $table->string('to_email');
            $table->string('status', 20)->default('sent'); // sent, failed, queued
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();

            $table->index(['order_id', 'template_key']);
            $table->index('to_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};

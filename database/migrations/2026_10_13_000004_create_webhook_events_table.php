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
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('source', 20); // 'shopify', 'stripe'
            $table->string('external_id')->unique(); // Idempotency key (evt_..., webhook ID)
            $table->string('topic');
            $table->jsonb('payload');
            $table->string('status', 20)->default('pending'); // pending, processed, failed
            $table->timestamp('received_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'received_at']);
            $table->index('topic');
            $table->index('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
    }
};

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
        Schema::table('shops', function (Blueprint $table) {
            $table->text('stripe_publishable_key')->nullable()->after('stripe_account_id');
            $table->text('stripe_secret_key')->nullable()->after('stripe_publishable_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['stripe_publishable_key', 'stripe_secret_key']);
        });
    }
};

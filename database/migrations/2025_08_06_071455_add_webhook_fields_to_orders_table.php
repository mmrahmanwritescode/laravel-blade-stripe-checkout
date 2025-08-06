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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_intent_id')->nullable()->after('transaction_id');
            $table->string('payment_status')->nullable()->after('payment_intent_id');
            $table->string('webhook_event_id')->nullable()->after('payment_status');
            $table->json('webhook_data')->nullable()->after('webhook_event_id');
            $table->timestamp('payment_completed_at')->nullable()->after('webhook_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_intent_id',
                'payment_status',
                'webhook_event_id',
                'webhook_data',
                'payment_completed_at'
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_status')->default('unpaid')->after('total_harga');
            $table->string('payment_order_id')->nullable()->unique()->after('payment_status');
            $table->text('payment_token')->nullable()->after('payment_order_id');
            $table->string('payment_transaction_id')->nullable()->index()->after('payment_token');
            $table->string('payment_type')->nullable()->after('payment_transaction_id');
            $table->timestamp('payment_paid_at')->nullable()->after('payment_type');
            $table->timestamp('payment_expired_at')->nullable()->after('payment_paid_at');
            $table->json('payment_payload')->nullable()->after('payment_expired_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique(['payment_order_id']);
            $table->dropIndex(['payment_transaction_id']);
            $table->dropColumn([
                'payment_status',
                'payment_order_id',
                'payment_token',
                'payment_transaction_id',
                'payment_type',
                'payment_paid_at',
                'payment_expired_at',
                'payment_payload',
            ]);
        });
    }
};

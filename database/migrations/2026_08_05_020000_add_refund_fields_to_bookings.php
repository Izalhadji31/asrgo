<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('refund_status')->default('none')->after('payment_payload');
            $table->string('refund_id')->nullable()->index()->after('refund_status');
            $table->unsignedInteger('refund_amount')->nullable()->after('refund_id');
            $table->timestamp('refunded_at')->nullable()->after('refund_amount');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['refund_id']);
            $table->dropColumn(['refund_status', 'refund_id', 'refund_amount', 'refunded_at']);
        });
    }
};

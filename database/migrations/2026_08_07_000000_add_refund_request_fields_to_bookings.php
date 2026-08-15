<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->text('refund_reason')->nullable()->after('refund_status');
            $table->text('refund_rejection_reason')->nullable()->after('refund_reason');
            $table->timestamp('refund_requested_at')->nullable()->after('refund_rejection_reason');
            $table->timestamp('refund_reviewed_at')->nullable()->after('refund_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'refund_reason',
                'refund_rejection_reason',
                'refund_requested_at',
                'refund_reviewed_at',
            ]);
        });
    }
};

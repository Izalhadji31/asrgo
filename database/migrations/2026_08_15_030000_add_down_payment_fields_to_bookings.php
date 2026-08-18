<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Scheme pembayaran: 'dp' = DP 30% di awal (rental), 'full' = lunas di awal
            $table->string('payment_scheme')->nullable()->after('payment_type');
            // Nominal yang dibayarkan via Midtrans (DP 30% atau lunas penuh)
            $table->unsignedBigInteger('payment_amount')->nullable()->after('payment_scheme');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_scheme', 'payment_amount']);
        });
    }
};

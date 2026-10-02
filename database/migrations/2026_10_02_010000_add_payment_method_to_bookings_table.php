<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Metode pembayaran: 'midtrans' (online) atau 'cash' (tunai, dikonfirmasi admin).
 * Skema DP 30% dihapus, jadi payment_scheme selalu 'full' (kolom lama tetap ada untuk data historis).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_method', 20)->nullable()->after('payment_scheme');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
};

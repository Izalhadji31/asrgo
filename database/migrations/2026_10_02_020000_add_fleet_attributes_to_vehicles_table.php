<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Atribut armada sesuai data riil CV. IzalhadjiTravel:
 * - warna        : warna unit (mis. Hitam, Putih, Silver)
 * - kota_operasi : basis operasi unit travel (Ende / Mbay)
 * - layanan      : unit dipakai untuk 'rental', 'travel', atau 'keduanya'
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('warna', 30)->nullable()->after('jenis');
            $table->string('kota_operasi', 30)->nullable()->after('warna');
            $table->string('layanan', 20)->default('keduanya')->after('kota_operasi');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['warna', 'kota_operasi', 'layanan']);
        });
    }
};

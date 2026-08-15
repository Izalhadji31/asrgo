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
        // Migration ini sudah no-op: kolom harga_sewa_tanpa_sopir_per_hari, harga_sewa_dengan_sopir_per_hari,
        // dan tarif_sopir_harian sudah langsung dibuat di 2026_08_02_000001_create_vehicles_table.
        // Rename harga_sewa_per_hari -> harga_sewa_tanpa_sopir_per_hari tidak relevan karena
        // kolom sumber (harga_sewa_per_hari) tidak pernah dibuat oleh migration manapun.
        // Guard Schema::hasColumn sebagai safety net bila dijalankan ulang di environment lawas.
        if (! Schema::hasColumn('vehicles', 'harga_sewa_tanpa_sopir_per_hari')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->integer('harga_sewa_tanpa_sopir_per_hari')->default(0);
            });
        }
        if (! Schema::hasColumn('vehicles', 'harga_sewa_dengan_sopir_per_hari')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->integer('harga_sewa_dengan_sopir_per_hari')->default(0);
            });
        }
        if (! Schema::hasColumn('vehicles', 'tarif_sopir_harian')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->integer('tarif_sopir_harian')->default(150000);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: kolom harga_sewa_tanpa_sopir_per_hari, harga_sewa_dengan_sopir_per_hari,
        // dan tarif_sopir_harian dibuat langsung oleh migration create_vehicles_table.
        // Tidak ada harga_sewa_per_hari untuk di-restore.
    }
};

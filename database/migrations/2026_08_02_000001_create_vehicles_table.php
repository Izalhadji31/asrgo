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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mitra_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama');
            $table->string('plat_nomor')->unique();
            $table->string('jenis');
            $table->enum('status', ['tersedia', 'disewa', 'maintenance'])->default('tersedia');
            $table->integer('harga_sewa_tanpa_sopir_per_hari');
            $table->integer('harga_sewa_dengan_sopir_per_hari');
            $table->integer('tarif_sopir_harian')->default(150000);
            $table->string('foto')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->string('transmission')->default('manual');
            $table->integer('capacity')->default(7);
            $table->integer('year')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('fuel_type')->default('bensin');
            $table->json('features')->nullable();
            $table->string('brand')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};

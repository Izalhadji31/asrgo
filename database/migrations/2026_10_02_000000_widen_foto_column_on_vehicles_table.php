<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * URL foto dari Wikimedia Commons (thumbnail 1280px) bisa mencapai ~272 karakter,
 * sementara kolom foto sebelumnya varchar(255) -> SQLSTATE 22001 Data too long.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('foto', 500)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('foto', 255)->nullable()->change();
        });
    }
};

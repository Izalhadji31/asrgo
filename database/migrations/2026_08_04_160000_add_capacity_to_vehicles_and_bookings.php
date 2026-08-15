<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->unsignedSmallInteger('kapasitas_penumpang')->default(4)->after('jenis');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedSmallInteger('jumlah_penumpang')->default(1)->after('session');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('jumlah_penumpang');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('kapasitas_penumpang');
        });
    }
};

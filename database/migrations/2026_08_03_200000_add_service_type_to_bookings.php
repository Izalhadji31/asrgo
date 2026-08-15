<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('service_type')->default('rental')->after('id');
            $table->string('origin')->nullable()->after('tanggal_selesai');
            $table->string('destination')->nullable()->after('origin');
            $table->string('flight_number')->nullable()->after('destination');
            $table->text('notes')->nullable()->after('flight_number');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['service_type', 'origin', 'destination', 'flight_number', 'notes']);
        });
    }
};

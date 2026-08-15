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
        Schema::table('routes', function (Blueprint $table) {
            $table->foreignId('origin_city_id')->nullable()->after('origin')->constrained('cities')->nullOnDelete();
            $table->foreignId('destination_city_id')->nullable()->after('destination')->constrained('cities')->nullOnDelete();
            $table->text('description')->nullable();
            $table->integer('duration_minutes')->nullable(); // Estimasi waktu perjalanan
            $table->integer('distance_km')->nullable(); // Jarak dalam km
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropForeign(['origin_city_id']);
            $table->dropForeign(['destination_city_id']);
            $table->dropColumn(['origin_city_id', 'destination_city_id', 'description', 'duration_minutes', 'distance_km']);
        });
    }
};

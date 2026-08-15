<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->string('session')->nullable()->after('service_type');
            $table->foreignId('vehicle_id')->nullable()->after('price')->constrained('vehicles')->nullOnDelete();
            $table->foreignId('sopir_id')->nullable()->after('vehicle_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sopir_id');
            $table->dropConstrainedForeignId('vehicle_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('routes')->cascadeOnDelete();
            $table->string('session');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->timestamps();

            $table->unique(['route_id', 'session']);
        });

        Schema::table('routes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sopir_id');
            $table->dropConstrainedForeignId('vehicle_id');
            $table->dropColumn('session');
        });
    }

    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->string('session')->nullable();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignId('sopir_id')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::dropIfExists('route_assignments');
    }
};

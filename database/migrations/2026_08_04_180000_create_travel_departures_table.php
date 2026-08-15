<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_departures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_assignment_id')->constrained('route_assignments')->cascadeOnDelete();
            $table->foreignId('route_id')->constrained('routes')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->date('departure_date');
            $table->string('session');
            $table->timestamp('departed_at');
            $table->timestamps();

            $table->unique(['route_assignment_id', 'departure_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_departures');
    }
};

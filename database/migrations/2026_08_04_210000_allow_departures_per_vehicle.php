<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('travel_departures', function (Blueprint $table) {
            $table->unique(['route_assignment_id', 'vehicle_id', 'departure_date'], 'travel_departures_assignment_vehicle_date_unique');
            $table->dropUnique(['route_assignment_id', 'departure_date']);
        });
    }

    public function down(): void
    {
        Schema::table('travel_departures', function (Blueprint $table) {
            $table->dropUnique('travel_departures_assignment_vehicle_date_unique');
            $table->unique(['route_assignment_id', 'departure_date']);
        });
    }
};

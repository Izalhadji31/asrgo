<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('ticket_status', ['not_created', 'created'])
                ->default('not_created')
                ->after('ticket_number');
        });

        // The ticket number is the reliable source for legacy ticket state.
        DB::table('bookings')
            ->whereNotNull('ticket_number')
            ->update(['ticket_status' => 'created']);
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('ticket_status');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('route_assignments', function (Blueprint $table) {
            $table->foreignId('mitra_id')->nullable()->after('route_id')->constrained('users')->nullOnDelete();
        });

        foreach (DB::table('route_assignments')->get() as $assignment) {
            if (!$assignment->vehicle_id) {
                continue;
            }

            $mitraId = DB::table('vehicles')->where('id', $assignment->vehicle_id)->value('mitra_id');
            if ($mitraId) {
                DB::table('route_assignments')->where('id', $assignment->id)->update(['mitra_id' => $mitraId]);
            }
        }

        $duplicateGroups = DB::table('route_assignments')
            ->select('route_id', 'session')
            ->groupBy('route_id', 'session')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateGroups as $group) {
            $rows = DB::table('route_assignments')
                ->where('route_id', $group->route_id)
                ->where('session', $group->session)
                ->orderByRaw('CASE WHEN mitra_id IS NULL THEN 1 ELSE 0 END')
                ->orderBy('priority')
                ->orderBy('id')
                ->get();
            $keepId = $rows->first()->id;

            DB::table('route_assignments')
                ->where('route_id', $group->route_id)
                ->where('session', $group->session)
                ->where('id', '!=', $keepId)
                ->delete();
        }

        Schema::table('route_assignments', function (Blueprint $table) {
            $table->unique(['route_id', 'session']);
        });
    }

    public function down(): void
    {
        Schema::table('route_assignments', function (Blueprint $table) {
            $table->dropUnique(['route_id', 'session']);
            $table->dropConstrainedForeignId('mitra_id');
        });
    }
};

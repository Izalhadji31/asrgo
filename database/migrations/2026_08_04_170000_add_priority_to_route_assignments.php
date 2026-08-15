<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('route_assignments', 'priority')) {
            Schema::table('route_assignments', function (Blueprint $table) {
                $table->unsignedSmallInteger('priority')->default(1)->after('session');
            });
        }

        $indexName = 'route_assignments_route_session_priority_index';
        $indexExists = DB::getDriverName() === 'mysql'
            ? DB::selectOne('SHOW INDEX FROM route_assignments WHERE Key_name = ?', [$indexName]) !== null
            : false;

        if (!$indexExists) {
            Schema::table('route_assignments', function (Blueprint $table) {
                $table->index(['route_id', 'session', 'priority']);
            });
        }

        Schema::table('route_assignments', function (Blueprint $table) {
            $table->dropUnique(['route_id', 'session']);
        });
    }

    public function down(): void
    {
        Schema::table('route_assignments', function (Blueprint $table) {
            $table->dropIndex(['route_id', 'session', 'priority']);
            $table->dropColumn('priority');
            $table->unique(['route_id', 'session']);
        });
    }
};

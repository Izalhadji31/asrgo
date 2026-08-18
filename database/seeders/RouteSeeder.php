<?php

namespace Database\Seeders;

use App\Models\Route;
use App\Models\RouteAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder idempotent untuk rute travel Flores.
 * Dihapus dulu semua data lama (travel_departures, route_assignments, routes)
 * lalu insert ulang dari skripsi hardcoded array.
 *
 * Jalankan: php artisan db:seed --class=RouteSeeder
 */
class RouteSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus berurutan karena ada foreign key
        DB::table('travel_departures')->delete();
        DB::table('route_assignments')->delete();
        DB::table('routes')->where('service_type', 'travel')->delete();

        $mitraId = (int) DB::table('users')
            ->where('role', 'mitra')
            ->value('id');

        if (! $mitraId) {
            $this->command->error('Tidak ada user dengan role mitra. Jalankan UserSeeder dulu.');

            return;
        }

        // 8 rute travel — semua lewat Ende (hub): armada mitra hanya melayani rute dari/ke Ende
        // Format: [origin, destination, price, duration_minutes, distance_km, description]
        $rutes = [
            ['Ende', 'Mbay',        200000, 360,  300, 'Rute pesisir selatan Flores menuju Mbay, Nagekeo.'],
            ['Mbay', 'Ende',        200000, 360,  300, 'Balik dari Mbay ke Kota Pancasila Ende.'],
            ['Maumere', 'Ende',      100000, 180,  130, 'Rute pesisir utara, surga bawah laut menuju kota sejarah.'],
            ['Ende', 'Maumere',      100000, 180,  130, 'Dari Kota Pancasila ke Teluk Maumere.'],
            ['Ruteng', 'Ende',       130000, 240,  180, 'Dari Kota Dingin ke Kota Pancasila lewat pesisir selatan.'],
            ['Ende', 'Ruteng',       130000, 240,  180, 'Balik dari Ende ke Kota Dingin.'],
            ['Bajawa', 'Ende',       150000, 240,  170, 'Dari Kota Adat Bajawa ke Kota Pancasila Ende.'],
            ['Ende', 'Bajawa',       150000, 240,  170, 'Dari Ende ke Bajawa — kota adat Ngada.'],
        ];

        $sesiList = ['pagi', 'siang']; // 'pagi' = 08:00, 'siang' = 12:00 (per Alpine js blade)

        $routeCount = 0;
        $assignmentCount = 0;

        foreach ($rutes as $rute) {
            [$origin, $destination, $price, $durMin, $distKm, $desc] = $rute;

            $route = Route::create([
                'origin'           => $origin,
                'destination'      => $destination,
                'service_type'     => 'travel',
                'price'            => $price,
                'description'      => $desc,
                'duration_minutes' => $durMin,
                'distance_km'      => $distKm,
            ]);
            $routeCount++;

            foreach ($sesiList as $i => $sesi) {
                RouteAssignment::create([
                    'route_id'   => $route->id,
                    'mitra_id'   => $mitraId,
                    'session'    => $sesi,
                    'priority'   => $i + 1,
                    'vehicle_id' => null, // Mitra punya banyak kendaraan, sistem akan pilih sendiri
                ]);
                $assignmentCount++;
            }
        }

        $this->command->info("Seeding rute travel selesai.");
        $this->command->info("  Rute: {$routeCount}");
        $this->command->info("  Assignment (rute x sesi): {$assignmentCount}");
        $this->command->info("  Mitra pemilik: {$mitraId}");

        // Pastikan minimal 3 kendaraan mitra punya sopir untuk travel
        $kendaraanBersopir = DB::table('vehicles')
            ->where('mitra_id', $mitraId)
            ->where('is_approved', true)
            ->where('status', 'tersedia')
            ->whereNotNull('sopir_id')
            ->count();

        if ($kendaraanBersopir < 3) {
            $this->command->warn("Kendaraan mitra yang punya sopir cuma {$kendaraanBersopir}. Sebaiknya assign sopir ke minimal 3 kendaraan.");
        }
    }
}
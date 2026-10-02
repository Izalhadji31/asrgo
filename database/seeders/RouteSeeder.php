<?php

namespace Database\Seeders;

use App\Models\Route;
use App\Models\RouteAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder idempotent untuk rute travel CV. IzalhadjiTravel.
 * Dihapus dulu semua data lama (travel_departures, route_assignments, routes)
 * lalu insert ulang dari skripsi hardcoded array.
 * 2 Okt 2026: rute disesuaikan data riil — HANYA Ende <-> Mbay (2 arah), 2 sesi.
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

        // 2 rute travel (data riil): hanya Ende <-> Mbay, dua arah
        // Format: [origin, destination, price, duration_minutes, distance_km, description]
        $rutes = [
            ['Ende', 'Mbay',        200000, 360,  300, 'Rute pesisir selatan Flores menuju Mbay, Nagekeo.'],
            ['Mbay', 'Ende',        200000, 360,  300, 'Balik dari Mbay ke Kota Pancasila Ende.'],
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

        // Pastikan ada unit khusus travel yang bersopir (auto-assign butuh ini)
        $kendaraanTravel = DB::table('vehicles')
            ->where('mitra_id', $mitraId)
            ->where('is_approved', true)
            ->where('status', 'tersedia')
            ->whereIn('layanan', ['travel', 'keduanya'])
            ->whereNotNull('sopir_id')
            ->count();

        if ($kendaraanTravel < 2) {
            $this->command->warn("Unit travel bersopir cuma {$kendaraanTravel}. Jalankan Fleet2026Seeder atau assign sopir ke unit travel.");
        }
    }
}
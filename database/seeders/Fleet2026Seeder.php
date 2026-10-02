<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Route;
use App\Models\RouteAssignment;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Armada riil CV. IzalhadjiTravel (2 Okt 2026):
 *  - Rental 5 unit
 *  - Travel 4 unit, rute HANYA Ende <-> Mbay (2 basis Ende, 2 basis Mbay)
 *  - Sopir 1 unit = 1 sopir
 * Jalankan manual: php artisan db:seed --class=Fleet2026Seeder
 */
class Fleet2026Seeder extends Seeder
{
    private const FOTO = [
        'avanza' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3d/2019_Toyota_Avanza_1.3_E_%28front%29%2C_Batu_City.jpg/1280px-2019_Toyota_Avanza_1.3_E_%28front%29%2C_Batu_City.jpg',
        'ertiga' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c9/2024_Suzuki_Ertiga_1.5_GLX_Hybrid_in_Snow_White_Pearl%2C_front_right%2C_06-16-2024.jpg/1280px-2024_Suzuki_Ertiga_1.5_GLX_Hybrid_in_Snow_White_Pearl%2C_front_right%2C_06-16-2024.jpg',
        'innova_reborn' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e2/Toyota_Innova_Reborn_2022.jpg/1280px-Toyota_Innova_Reborn_2022.jpg',
        'hiace' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/91/2019_Toyota_HiAce_Premio_2.8_GDH322R_%2820190722%29.jpg/1280px-2019_Toyota_HiAce_Premio_2.8_GDH322R_%2820190722%29.jpg',
        'xl7' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/06/2022_Suzuki_XL7_Alpha_FF_1.5_NC22S_%2820220405%29.jpg/1280px-2022_Suzuki_XL7_Alpha_FF_1.5_NC22S_%2820220405%29.jpg',
        'zenix' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/15/2022_Toyota_Kijang_Innova_Zenix_V_%28Indonesia%29_front_view.jpg/1280px-2022_Toyota_Kijang_Innova_Zenix_V_%28Indonesia%29_front_view.jpg',
    ];

    /** nama, plat, warna, jenis, kapasitas, layanan, kota_operasi, sopir email, tarif harian tanpa sopir, dengan sopir, foto, brand, tahun, transmisi, bahan bakar */
    private const ARMADA = [
        ['Toyota Innova Reborn', 'EB 2112 AP', 'Hitam', 'mpv_premium', 7, 'rental', null, 'yohanes@asrgo.test', 700000, 950000, 'innova_reborn', 'Toyota', 2022, 'Matic', 'Bensin'],
        ['Toyota Innova Reborn', 'EB 1304 AJ', 'Putih', 'mpv_premium', 7, 'rental', null, 'driver@asrgo.test', 700000, 950000, 'innova_reborn', 'Toyota', 2022, 'Matic', 'Bensin'],
        ['Toyota Hiace', 'EB 1707 AS', 'Putih', 'minibus', 14, 'rental', null, 'petrus@asrgo.test', 1300000, 1650000, 'hiace', 'Toyota', 2019, 'Manual', 'Solar'],
        ['Suzuki XL7', 'EB 1180 AH', 'Silver', 'mpv', 7, 'rental', null, 'stefanus@asrgo.test', 450000, 600000, 'xl7', 'Suzuki', 2022, 'Matic', 'Bensin'],
        ['Toyota Innova Zenix', 'EB 1105 AH', 'Hitam', 'mpv_premium', 7, 'rental', null, 'karolus@asrgo.test', 850000, 1100000, 'zenix', 'Toyota', 2023, 'Matic', 'Bensin'],
        ['Toyota Avanza', 'EB 1403 AB', 'Putih', 'mpv', 7, 'travel', 'Ende', 'andreas@asrgo.test', 500000, 650000, 'avanza', 'Toyota', 2019, 'Manual', 'Bensin'],
        ['Suzuki Ertiga', 'EB 1146 AB', 'Silver', 'mpv', 7, 'travel', 'Ende', 'benediktus@asrgo.test', 450000, 600000, 'ertiga', 'Suzuki', 2024, 'Matic', 'Bensin'],
        ['Toyota Avanza', 'EB 1401 AB', 'Merah', 'mpv', 7, 'travel', 'Mbay', 'fransiskus@asrgo.test', 500000, 650000, 'avanza', 'Toyota', 2019, 'Manual', 'Bensin'],
        ['Toyota Avanza', 'EB 3113 AJ', 'Hitam', 'mpv', 7, 'travel', 'Mbay', 'yulius@asrgo.test', 500000, 650000, 'avanza', 'Toyota', 2019, 'Manual', 'Bensin'],
    ];

    private const SOPIR_BARU = [
        ['Andreas Lako', 'andreas@asrgo.test'],
        ['Benediktus Nggai', 'benediktus@asrgo.test'],
        ['Fransiskus Duli', 'fransiskus@asrgo.test'],
        ['Yulius Rada', 'yulius@asrgo.test'],
    ];

    public function run(): void
    {
        // ==== 1. Bersihkan data armada lama ====
        DB::table('notification_logs')->where('related_model', Booking::class)->delete();
        DB::table('reviews')->delete();
        DB::table('payouts')->delete();
        DB::table('booking_passengers')->delete();
        DB::table('travel_departures')->delete();
        DB::table('route_assignments')->delete();
        Booking::query()->delete();

        // Sisakan HANYA rute Ende <-> Mbay (dua arah), hapus rute kota lain bila ada.
        Route::where('service_type', 'travel')
            ->where(function ($query) {
                $query->whereNotIn('origin', ['Ende', 'Mbay'])
                    ->orWhereNotIn('destination', ['Ende', 'Mbay']);
            })
            ->delete();
        Vehicle::query()->delete();
        $this->command->info('Data armada & rute lama dibersihkan.');

        // ==== 2. Sopir tambahan (1 unit 1 sopir) ====
        foreach (self::SOPIR_BARU as [$nama, $email]) {
            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $nama,
                    'role' => 'driver',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }

        $mitra = User::where('email', 'mitra@asrgo.test')->firstOrFail();

        // ==== 3. Armada baru ====
        $prioritas = 1;
        foreach (self::ARMADA as [$nama, $plat, $warna, $jenis, $kapasitas, $layanan, $kota, $emailSopir, $tanpaSopir, $denganSopir, $fotoKey, $brand, $tahun, $transmisi, $bahanBakar]) {
            $sopir = User::where('email', $emailSopir)->firstOrFail();

            Vehicle::create([
                'mitra_id' => $mitra->id,
                'sopir_id' => $sopir->id,
                'nama' => $nama,
                'plat_nomor' => $plat,
                'jenis' => $jenis,
                'warna' => $warna,
                'kota_operasi' => $kota,
                'layanan' => $layanan,
                'kapasitas_penumpang' => $kapasitas,
                'prioritas_travel' => $layanan === 'travel' ? $prioritas++ : 0,
                'status' => 'tersedia',
                'harga_sewa_tanpa_sopir_per_hari' => $tanpaSopir,
                'harga_sewa_dengan_sopir_per_hari' => $denganSopir,
                'tarif_sopir_harian' => 150000,
                'foto' => self::FOTO[$fotoKey],
                'is_approved' => true,
                'transmission' => $transmisi,
                'capacity' => $kapasitas,
                'year' => $tahun,
                'vehicle_type' => $jenis,
                'fuel_type' => $bahanBakar,
                'features' => ['AC', 'Audio', 'Airbag'],
                'brand' => $brand,
            ]);
        }
        $this->command->info('Armada baru: '.Vehicle::count().' unit (rental '.Vehicle::where('layanan', 'rental')->count().', travel '.Vehicle::where('layanan', 'travel')->count().').');

        // ==== 4. Assignment rute travel: Ende <-> Mbay, 2 sesi ====
        $ruteIds = Route::where('service_type', 'travel')
            ->whereIn('origin', ['Ende', 'Mbay'])
            ->whereIn('destination', ['Ende', 'Mbay'])
            ->pluck('id');

        foreach ($ruteIds as $routeId) {
            foreach (['pagi', 'siang'] as $sesi) {
                RouteAssignment::create([
                    'route_id' => $routeId,
                    'mitra_id' => $mitra->id,
                    'session' => $sesi,
                    'priority' => 1,
                ]);
            }
        }
        $this->command->info('Rute travel aktif: '.Route::where('service_type', 'travel')->count().' rute, assignment: '.RouteAssignment::count().'.');
    }
}
